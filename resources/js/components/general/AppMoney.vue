<template>
    <span>{{ displayValue }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { formatAppMoney } from '@/utils/appFormatters'

const props = defineProps({
    value: {
        type: [String, Number],
        default: null,
    },
    currency: {
        type: String,
        default: 'COP',
    },
    locale: {
        type: String,
        default: 'es-CO',
    },
    minimumFractionDigits: {
        type: Number,
        default: 0,
    },
    maximumFractionDigits: {
        type: Number,
        default: 0,
    },
    fallback: {
        type: String,
        default: '—',
    },
})

const displayValue = computed(() => formatAppMoney(props.value, {
    currency: props.currency,
    locale: props.locale,
    minimumFractionDigits: props.minimumFractionDigits,
    maximumFractionDigits: props.maximumFractionDigits,
    fallback: props.fallback,
}))
</script>
