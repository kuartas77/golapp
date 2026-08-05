<template>
    <div
        v-if="isLoading"
        class="loading-overlay"
        :class="{ 'loading-overlay--fullscreen': fullscreen }"
        role="status"
        aria-live="polite"
    >
        <div class="spinner"></div>
        <p v-if="loadingText" class="loading-text">{{ loadingText }}</p>
    </div>
</template>
<script setup>
const props = defineProps({
    isLoading: { type: Boolean, default: true },
    loadingText: { type: String, default: '' },
    fullscreen: { type: Boolean, default: false }
})
</script>
<style lang="scss" scoped>
.loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(2px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 20;
    border-radius: 10px;
    text-align: center;
}

.loading-overlay--fullscreen {
    position: fixed;
    z-index: 2000;
    border-radius: 0;
}

.spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(15, 28, 70, 0.18);
    border-top-color: #0f1c46;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 0.5rem;
}

.loading-text {
    font-weight: 500;
    color: var(--golapp-text-body, #515365);
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

:global(body.dark) {
    .loading-overlay {
        background: rgba(6, 8, 24, 0.88);
    }

    .spinner {
        border-color: rgba(255, 255, 255, 0.2);
        border-top-color: #ffca00;
    }

    .loading-text {
        color: var(--golapp-text-body, #e0e6ed);
    }
}

@media (prefers-reduced-motion: reduce) {
    .spinner {
        animation-duration: 1.5s;
    }
}
</style>
