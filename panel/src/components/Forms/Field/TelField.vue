<template>
	<k-field v-bind="$props" :input="id" class="k-tel-field">
		<k-input
			v-bind="$props"
			ref="input"
			type="tel"
			v-on="$listeners"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TelInput } from "../Input/TelInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-tel-input>`
 * for additional information.
 * @example <k-tel-field :value="tel" @input="tel = $event" name="tel" label="Phone number" />
 */
export default {
	mixins: [Field, Input, TelInput],
	inheritAttrs: false,
	props: {
		icon: {
			type: String,
			default: "phone"
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
