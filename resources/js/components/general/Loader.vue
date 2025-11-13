<template>
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p class="loading-text">{{ loadingText }}</p>
    </div>
</template>
<script setup>
const props = defineProps({
    isLoading: { type: Boolean, default: true },
    loadingText: { type: String, default: '' }
})
</script>
<style lang="scss">
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

.spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #ddd;
    border-top-color: #0F1C46;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 0.5rem;
}

.loading-text {
    font-weight: 500;
    color: #333;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.dark {
    .loading-overlay {
        background: rgba(49, 49, 49, 0.85);
    }

    .spinner {
        border: 4px solid #ddd;
        border-top-color: #009688;
    }

    .loading-text {
        color: #009688;
    }
}
</style>