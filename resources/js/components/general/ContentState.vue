<template>
    <section
        class="content-state"
        :class="`content-state--${type}`"
        :role="type === 'error' ? 'alert' : 'status'"
        :aria-live="type === 'error' ? 'assertive' : 'polite'"
        aria-atomic="true"
    >
        <div class="content-state__icon" aria-hidden="true">
            <span v-if="type === 'loading'" class="spinner-border" role="presentation"></span>
            <i v-else :class="iconClass"></i>
        </div>

        <div class="content-state__content">
            <h2 class="content-state__title">{{ resolvedTitle }}</h2>
            <p v-if="resolvedMessage" class="content-state__message">{{ resolvedMessage }}</p>

            <div v-if="actionLabel || $slots.actions" class="content-state__actions">
                <button
                    v-if="actionLabel"
                    type="button"
                    class="btn btn-sm"
                    :class="type === 'error' ? 'btn-danger' : 'btn-primary'"
                    @click="$emit('action')"
                >
                    {{ actionLabel }}
                </button>
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

const defaults = {
    loading: {
        title: 'Cargando información',
        message: 'Espera un momento mientras preparamos los datos.',
    },
    empty: {
        title: 'Aún no hay información',
        message: 'Los datos aparecerán aquí cuando estén disponibles.',
    },
    error: {
        title: 'No fue posible cargar la información',
        message: 'Intenta nuevamente. Si el problema continúa, comunícate con soporte.',
    },
};

const icons = {
    empty: 'fa-regular fa-folder-open',
    error: 'fa-solid fa-triangle-exclamation',
};

const props = defineProps({
    type: {
        type: String,
        default: 'empty',
        validator: value => ['loading', 'empty', 'error'].includes(value),
    },
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
});

defineEmits(['action']);

const resolvedTitle = computed(() => props.title || defaults[props.type].title);
const resolvedMessage = computed(() => props.message || defaults[props.type].message);
const iconClass = computed(() => icons[props.type] ?? '');
</script>

<style scoped>
.content-state {
    --content-state-accent: #0f1c46;
    --content-state-background: #f7f8fc;
    --content-state-border: #d8ddea;

    display: flex;
    align-items: flex-start;
    gap: 1rem;
    width: 100%;
    padding: 1.25rem;
    color: var(--golapp-text-body, #515365);
    background: var(--content-state-background);
    border: 1px solid var(--content-state-border);
    border-radius: 0.75rem;
}

.content-state--error {
    --content-state-accent: #9f1d2b;
    --content-state-background: #fff5f6;
    --content-state-border: #efc2c7;
}

.content-state--error .content-state__icon {
    background: rgba(159, 29, 43, 0.08);
}

.content-state__icon {
    display: inline-flex;
    flex: 0 0 2.75rem;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    color: var(--content-state-accent);
    background: rgba(15, 28, 70, 0.08);
    border-radius: 50%;
    font-size: 1.15rem;
}

.content-state__icon .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
    border-width: 0.2rem;
}

.content-state__content {
    min-width: 0;
}

.content-state__title {
    margin: 0;
    color: inherit;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.4;
}

.content-state__message {
    margin: 0.35rem 0 0;
    color: var(--golapp-text-muted, #5f6475);
    line-height: 1.55;
}

.content-state__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.9rem;
}

:global(body.dark .content-state) {
    --content-state-accent: #ffca00;
    --content-state-background: #161a2d;
    --content-state-border: #343a55;
}

:global(body.dark .content-state__icon) {
    background: rgba(255, 202, 0, 0.1);
}

:global(body.dark .content-state--error) {
    --content-state-accent: #ff9da8;
    --content-state-background: #2b1720;
    --content-state-border: #713442;
}

:global(body.dark .content-state--error .content-state__icon) {
    background: rgba(255, 157, 168, 0.12);
}

@media (max-width: 575.98px) {
    .content-state {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .content-state__actions {
        justify-content: center;
    }
}

@media (prefers-reduced-motion: reduce) {
    .content-state__icon .spinner-border {
        animation-duration: 1.5s;
    }
}
</style>
