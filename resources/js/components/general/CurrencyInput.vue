<template>
  <input ref="inputRef" type="text" v-model="formattedValue" />
</template>

<script setup>
import { useCurrencyInput } from 'vue-currency-input';
import { watch } from 'vue';

const props = defineProps({ modelValue: Number });

const { inputRef, formattedValue, numberValue, setValue } = useCurrencyInput({
  currency: 'COP',
  hideCurrencySymbolOnFocus: false,
  hideGroupingSeparatorOnFocus: false,
  precision: 0,
  valueRange: { min: 0 },
});

watch(() => props.modelValue, (value) => { setValue(value) })
</script>