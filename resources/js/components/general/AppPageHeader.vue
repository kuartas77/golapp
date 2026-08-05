<template>
    <header class="app-page-header d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
        <div class="d-flex align-items-start gap-3 min-width-0">
            <span v-if="icon" class="app-page-header__icon" aria-hidden="true">
                <i :class="icon" />
            </span>
            <div class="min-width-0">
                <h1 class="app-page-header__title h4 mb-1">{{ title }}</h1>
                <p v-if="subtitle" class="app-page-header__subtitle text-muted mb-0">
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <div v-if="slots.actions" class="app-page-header__actions d-flex flex-wrap align-items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>

<script setup>
import { useSlots } from 'vue'

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: '',
    },
})

const slots = useSlots()
</script>

<style scoped>
.app-page-header {
    width: 100%;
}

.app-page-header__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 2.25rem;
    min-height: 2.25rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, currentColor 10%, transparent);
}

.app-page-header__title {
    line-height: 1.25;
}

.app-page-header__subtitle {
    max-width: 68ch;
    line-height: 1.5;
}

.min-width-0 {
    min-width: 0;
}

@media (max-width: 767.98px) {
    .app-page-header__actions {
        width: 100%;
    }
}
</style>
