<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Handles all tasks to get the Media API
 * up and running and link files correctly
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Media
{
	/**
	 * Tries to find a file by model and filename
	 * and to copy it to the media folder.
	 *
	 * @param \Kirby\Cms\Model|null $model
	 * @param string $hash
	 * @param string $filename
	 * @return \Kirby\Cms\Response|false
	 */
	public static function link(Model $model = null, string $hash, string $filename)
	{
		if ($model === null) {
			return false;
		}

		// fix issues with spaces in filenames
		$filename = urldecode($filename);

		// try to find a file by model and filename
		// this should work for all original files
		if ($file = $model->file($filename)) {
			// check if the request contained an outdated media hash
			if ($file->mediaHash() !== $hash) {
				// if at least the token was correct, redirect
				if (Str::startsWith($hash, $file->mediaToken() . '-') === true) {
					return Response::redirect($file->mediaUrl(), 307);
				}

				// don't leak the correct token, render the error page
				return false;
			}

			// send the file to the browser
			return Response::file($file->publish()->mediaRoot());
		}

		// try to generate a thumb for the file
		return static::thumb($model, $hash, $filename);
	}

	/**
	 * Copy the file to the final media folder location
	 *
	 * @param \Kirby\Cms\File $file
	 * @param string $dest
	 * @return bool
	 */
	public static function publish(File $file, string $dest): bool
	{
		// never publish risky files (e.g. HTML, PHP or Apache config files)
		FileRules::validFile($file, false);

		$src       = $file->root();
		$version   = dirname($dest);
		$directory = dirname($version);

		// unpublish all files except stuff in the version folder
		Media::unpublish($directory, $file, $version);

		// copy/overwrite the file to the dest folder
		return F::copy($src, $dest, true);
	}

	/**
	 * Tries to find a job file for the
	 * given filename and then calls the thumb
	 * component to create a thumbnail accordingly
	 *
	 * @param \Kirby\Cms\Model|string $model
	 * @param string $hash
	 * @param string $filename
	 * @return \Kirby\Cms\Response|false
	 */
	public static function thumb($model, string $hash, string $filename)
	{
		$kirby = App::instance();

		$root = match (true) {
			// assets
			is_string($model)
				=> $kirby->root('media') . '/assets/' . $model . '/' . $hash,
			// parent files for file model that already included hash
			$model instanceof File
				=> dirname($model->mediaRoot()),
			// model files
			default
			=> $model->mediaRoot() . '/' . $hash
		};

		$thumb = $root . '/' . $filename;
		$job   = $root . '/.jobs/' . $filename . '.json';

		try {
			$options = Data::read($job);
		} catch (Throwable) {
			// send a customized error message to make clearer what happened here
			throw new NotFoundException('The thumbnail configuration could not be found');
		}
		
		if (empty($options['filename']) === true) {
			throw new InvalidArgumentException('Incomplete thumbnail configuration');
		}

		try {
			// find the correct source file depending on the model
			// this adds support for custom assets
			$source = match (true) {
				is_string($model) === true
					=> $kirby->root('index') . '/' . $model . '/' . $options['filename'],
				default
					=> $model->file($options['filename'])->root()
			};

			// generate the thumbnail and save it in the media folder
			$kirby->thumb($source, $thumb, $options);

			// remove the job file once the thumbnail has been created
			F::remove($job);

			// read the file and send it to the browser
			return Response::file($thumb);
		} catch (Throwable $e) {
			// remove potentially broken thumbnails
			F::remove($thumb);
			throw $e;
		}
	}

	/**
	 * Deletes all versions of the given file
	 * within the parent directory
	 *
	 * @param string $directory
	 * @param \Kirby\Cms\File $file
	 * @param string|null $ignore
	 * @return bool
	 */
	public static function unpublish(string $directory, File $file, string $ignore = null): bool
	{
		if (is_dir($directory) === false) {
			return true;
		}

		// get both old and new versions (pre and post Kirby 3.4.0)
		$versions = array_merge(
			glob($directory . '/' . crc32($file->filename()) . '-*', GLOB_ONLYDIR),
			glob($directory . '/' . $file->mediaToken() . '-*', GLOB_ONLYDIR)
		);

		// delete all versions of the file
		foreach ($versions as $version) {
			if ($version === $ignore) {
				continue;
			}

			Dir::remove($version);
		}

		return true;
	}
}
