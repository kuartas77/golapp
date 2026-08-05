<template>
    <div :class="wrapperClass">
        <button
            v-if="showCancel"
            type="button"
            class="btn btn-secondary"
            :disabled="submitting"
            @click="$emit('cancel')"
        >
            {{ cancelLabel }}
        </button>
        <button
            type="submit"
            :class="submitClass"
            :disabled="submitting || disabled"
            :aria-busy="submitting ? 'true' : 'false'"
        >
            <span
                v-if="submitting"
                class="spinner-border spinner-border-sm me-2"
                aria-hidden="true"
            ></span>
            {{ submitting ? submittingLabel : submitLabel }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    submitting: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    showCancel: { type: Boolean, default: true },
    submitLabel: { type: String, default: 'Guardar' },
    submittingLabel: { type: String, default: 'Guardando...' },
    cancelLabel: { type: String, default: 'Cerrar' },
    submitClass: { type: String, default: 'btn btn-primary' },
    wrapperClass: { type: String, default: 'modal-footer' },
})

defineEmits(['cancel'])
</script>
