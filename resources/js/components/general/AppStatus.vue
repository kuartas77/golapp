<template>
    <span class="badge" :class="`badge-${status.variant}`">{{ status.label }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { resolveAppStatus } from '@/utils/appFormatters'

const props = defineProps({
    value: {
        type: [String, Number, Boolean],
        default: null,
    },
    context: {
        type: String,
        default: 'default',
    },
    label: {
        type: String,
        default: undefined,
    },
    variant: {
        type: String,
        default: undefined,
    },
    fallbackLabel: {
        type: String,
        default: 'Sin estado',
    },
})

const status = computed(() => resolveAppStatus(props.value, {
    context: props.context,
    label: props.label,
    variant: props.variant,
    fallbackLabel: props.fallbackLabel,
}))
</script>
