<template>
	<k-field v-bind="$props" :input="id" class="k-toggle-field">
		<k-input
			v-bind="$props"
			ref="input"
			type="toggle"
			v-on="$listeners"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as ToggleInput } from "../Input/ToggleInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-toggle-input>`
 * for additional information.
 * @example <k-toggle-field :value="toggle" @input="toggle = $event" label="Toggle" name="toggle" />
 */
export default {
	mixins: [Field, Input, ToggleInput],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
