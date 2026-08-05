<template>
    <time v-if="dateTime" :datetime="dateTime">{{ displayValue }}</time>
    <span v-else>{{ displayValue }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { appDateTime, formatAppDate } from '@/utils/appFormatters'

const props = defineProps({
    value: {
        type: [String, Number, Date],
        default: null,
    },
    format: {
        type: String,
        default: 'DD/MM/YYYY',
    },
    fallback: {
        type: String,
        default: '—',
    },
})

const displayValue = computed(() => formatAppDate(props.value, {
    format: props.format,
    fallback: props.fallback,
}))
const dateTime = computed(() => appDateTime(props.value))
</script>
