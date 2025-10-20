<template>
    <label v-if="label" :for="name" class="form-label">{{ label }}</label>
    <span v-if="isRequired" class="text-danger">*</span>
    <template v-if="!currency">
        <input  :type="type" autocomplete="off"
            class="form-control form-control-sm"
            :id="name"
            :placeholder="label"
            :class="{ field_error: errorMessage, valid: meta.valid }"
            v-model="value" v-bind="$attrs"/>
    </template>
    <template v-else>
        <CurrencyInput
            v-model="value"
            autocomplete="off"
            class="form-control form-control-sm"
            :id="name"
            :placeholder="label"
            :class="{ field_error: errorMessage, valid: meta.valid }"
            v-bind="$attrs"/>
    </template>
    <div :class="errorMessage ? 'custom-error' : ''">{{ errorMessage }}</div>
</template>
<script setup>
import { useField } from 'vee-validate'
import CurrencyInput from '@/components/general/CurrencyInput'
const props = defineProps({
    name: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'text'
    },
    currency: {
        type: Boolean,
        default: false
    },
    label: String,
    isRequired: {
        type: Boolean,
        default: false
    }
})

const { value, errorMessage, meta } = useField(() => props.name)
</script>