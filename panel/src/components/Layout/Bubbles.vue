<template>
	<ul class="k-bubbles">
		<li v-for="(bubble, id) in items" :key="id">
			<k-bubble v-bind="bubble" :html="html" />
		</li>
	</ul>
</template>

<script>
export const props = {
	props: {
		/**
		 * If set to `true`, the `text` is rendered as HTML code,
		 * otherwise as plain text
		 */
		html: {
			type: Boolean
		}
	}
};

/**
 * Display a list of <k-bubble>
 * @since 3.7.0
 *
 * @example <k-bubbles :bubbles="['Hello', 'World']" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	props: {
		/**
		 * Array or string of bubbles, see <k-bubble> for available props.  If string, will be split by comma.
		 */
		bubbles: [Array, String]
	},
	computed: {
		items() {
			let bubbles = this.bubbles;

			if (typeof bubbles === "string") {
				bubbles = bubbles.split(",");
			}

			return bubbles.map((bubble) =>
				bubble === "string" ? { text: bubble } : bubble
			);
		}
	}
};
</script>

<style>
.k-bubbles {
	display: flex;
	gap: 0.25rem;
}
</style>
