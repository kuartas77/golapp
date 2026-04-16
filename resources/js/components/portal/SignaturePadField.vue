<template>
    <div class="signature-field">
        <label class="d-block">
            {{ label }}
            <span v-if="required" class="text-danger">&nbsp;(*)</span>
        </label>

        <small v-if="help" class="form-text text-muted d-block mb-2">
            {{ help }}
        </small>

        <div class="signature-field__canvas" :class="{ 'signature-field__canvas--invalid': errorMessage }">
            <canvas ref="canvasRef" :width="width" :height="height"></canvas>
        </div>

        <button type="button" class="btn btn-danger waves-effect text-left mt-2" @click="clearSignature">
            Limpiar
        </button>

        <div :class="errorMessage ? 'invalid-feedback d-block' : ''">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useField } from 'vee-validate';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    help: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    width: {
        type: Number,
        default: 320,
    },
    height: {
        type: Number,
        default: 160,
    },
});

const canvasRef = ref(null);
const { value, errorMessage, setValue } = useField(() => props.name);

let signaturePad = null;
let isUnmounted = false;

const syncEvents = ['mouseup', 'touchend', 'pointerup'];

let signaturePadLoader = null;

const ensureSignaturePad = () => {
    if (window.SignaturePad) {
        return Promise.resolve(window.SignaturePad);
    }

    if (signaturePadLoader) {
        return signaturePadLoader;
    }

    signaturePadLoader = new Promise((resolve, reject) => {
        const existingScript = document.querySelector('script[data-signature-pad-loader="portal"]');

        const resolveWhenReady = () => {
            if (window.SignaturePad) {
                resolve(window.SignaturePad);
                return;
            }

            reject(new Error('No fue posible cargar el componente de firma.'));
        };

        if (existingScript) {
            existingScript.addEventListener('load', resolveWhenReady, { once: true });
            existingScript.addEventListener('error', () => reject(new Error('No fue posible cargar el componente de firma.')), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = '/js/signature_pad.umd.min.js';
        script.async = true;
        script.dataset.signaturePadLoader = 'portal';
        script.addEventListener('load', resolveWhenReady, { once: true });
        script.addEventListener('error', () => reject(new Error('No fue posible cargar el componente de firma.')), { once: true });
        document.head.appendChild(script);
    });

    return signaturePadLoader;
};

const syncValue = () => {
    if (!signaturePad) {
        return;
    }

    setValue(signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png'));
};

const restoreSignature = (dataUrl) => {
    if (!signaturePad || !dataUrl || typeof signaturePad.fromDataURL !== 'function') {
        return;
    }

    signaturePad.fromDataURL(dataUrl);
};

const clearSignature = () => {
    if (signaturePad) {
        signaturePad.clear();
    }

    setValue('');
};

onMounted(async () => {
    if (!canvasRef.value) {
        return;
    }

    try {
        await ensureSignaturePad();
    } catch (error) {
        return;
    }

    if (isUnmounted || !canvasRef.value || !window.SignaturePad) {
        return;
    }

    signaturePad = new window.SignaturePad(canvasRef.value);

    if (typeof value.value === 'string' && value.value.length > 0) {
        restoreSignature(value.value);
    }

    syncEvents.forEach((eventName) => {
        canvasRef.value?.addEventListener(eventName, syncValue);
    });
});

onBeforeUnmount(() => {
    isUnmounted = true;
    syncEvents.forEach((eventName) => {
        canvasRef.value?.removeEventListener(eventName, syncValue);
    });
});

watch(value, (currentValue) => {
    if (!signaturePad) {
        return;
    }

    if (!currentValue && !signaturePad.isEmpty()) {
        signaturePad.clear();
        return;
    }

    if (typeof currentValue === 'string' && currentValue.length > 0 && signaturePad.isEmpty()) {
        restoreSignature(currentValue);
    }
});
</script>

<style scoped>
.signature-field__canvas {
    width: 100%;
    max-width: 360px;
    border: 1px solid #d9d9d9;
    border-radius: 6px;
    background: #fff;
}

.signature-field__canvas canvas {
    width: 100%;
    height: 160px;
    display: block;
}

.signature-field__canvas--invalid {
    border-color: #dc3545;
}
</style>
