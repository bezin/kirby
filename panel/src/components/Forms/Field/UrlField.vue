<template>
	<k-field v-bind="$props" :input="id" class="k-url-field">
		<k-input
			v-bind="$props"
			ref="input"
			type="url"
			v-on="$listeners"
		>
			<template #icon>
				<k-button
					v-if="link"
					:icon="icon"
					:link="value"
					:title="$t('open')"
					class="k-input-icon-button"
					tabindex="-1"
					target="_blank"
				/>
			</template>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as UrlInput } from "../Input/UrlInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-url-input>`
 * for additional information.
 * @example <k-url-field :value="url" @input="url = $event" name="url" label="Url" />
 */
export default {
	mixins: [Field, Input, UrlInput],
	inheritAttrs: false,
	props: {
		link: {
			type: Boolean,
			default: true
		},
		icon: {
			type: String,
			default: "url"
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
