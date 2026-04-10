<template>
    <label v-if="label" :for="name" class="form-label">{{ label }}<span v-if="isRequired" class="text-danger">&nbsp;(*)</span></label>

    <template v-if="!currency">
        <input
            v-bind="$attrs"
            v-model="value"
            :type="type"
            :id="name"
            :placeholder="label"
            class="form-control form-control-sm"
            :class="{ 'is-invalid': meta.touched && errorMessage }"
            autocomplete="off"/>
    </template>
    <template v-else>
        <CurrencyInput
            v-bind="$attrs"
            v-model="value"
            :id="name"
            :placeholder="label"
            class="form-control form-control-sm"
            :class="{ 'is-invalid': meta.touched && errorMessage }"
            autocomplete="off"/>
    </template>
    <div :class="errorMessage ? 'invalid-feedback d-block' : ''">{{ errorMessage }}</div>
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

const { value,  meta, errorMessage } = useField(() => props.name)
</script>