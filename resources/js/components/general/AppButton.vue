<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="btn d-inline-flex align-items-center justify-content-center gap-2 app-button"
        :class="buttonClass"
    >
        <template v-if="loading">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
            <span>Cargando...</span>
        </template>

        <slot v-else />
    </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    full: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
})

const variantClassMap = {
    primary: 'btn-primary',
    secondary: 'btn-outline-secondary',
    danger: 'btn-danger',
}

const sizeClassMap = {
    sm: 'btn-sm',
    md: '',
    lg: 'btn-lg',
}

const buttonClass = computed(() => {
    return [
        variantClassMap[props.variant] || variantClassMap.primary,
        sizeClassMap[props.size] ?? sizeClassMap.md,
        props.full ? 'w-100' : null,
    ]
})
</script>

<style scoped>
.app-button {
    min-height: 38px;
    font-weight: 600;
}
</style>
