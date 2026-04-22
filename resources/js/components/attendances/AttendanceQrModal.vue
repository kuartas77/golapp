<template>
    <teleport to="body">
        <div
            ref="modalElement"
            class="modal fade"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content attendance-qr-modal__content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title mb-1">{{ title }}</h5>
                            <p class="text-muted mb-0 small">{{ subtitle }}</p>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Cerrar"
                            @click="hideModal"
                        ></button>
                    </div>

                    <div class="modal-body pt-3">
                        <div class="attendance-qr-modal__preview">
                            <div class="attendance-qr-modal__canvas text-center">
                                <QRCodeVue3
                                    v-if="hasUniqueCode"
                                    :key="attendanceUrl"
                                    :value="attendanceUrl"
                                    :width="240"
                                    :height="240"
                                    :qr-options="{ errorCorrectionLevel: 'H' }"
                                    :image="schoolLogo"
                                    :image-options="{
                                        hideBackgroundDots: true,
                                        imageSize: 0.26,
                                        margin: 4,
                                        crossOrigin: 'anonymous',
                                    }"
                                    :dots-options="{ color: '#111827', type: 'square' }"
                                    :background-options="{ color: '#ffffff' }"
                                    :corners-square-options="{ color: '#111827', type: 'extra-rounded' }"
                                    :corners-dot-options="{ color: '#111827', type: 'dot' }"
                                    fileExt="png"
                                    :download="true"
                                    myclass="attendance-qr-modal__qr-instance"
                                    imgclass="attendance-qr-modal__qr-image"
                                    downloadButton="btn btn-primary btn-sm attendance-qr-modal__download-button"
                                    buttonName="Descargar PNG"
                                    :downloadOptions="{ name: `asistencia-qr-${normalizedUniqueCode}`, extension: 'png' }"
                                />
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="copyLink">
                            Copiar enlace
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import QRCodeVue3 from 'qrcode-vue3'

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    uniqueCode: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'QR de asistencia',
    },
    subtitle: {
        type: String,
        default: 'Escanéalo para abrir la toma rápida desde el celular.',
    },
    logoFile: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const authUser = useAuthUser()
const router = useRouter()
const modalElement = ref(null)
let modalInstance = null

const normalizedUniqueCode = computed(() => props.uniqueCode.trim())
const hasUniqueCode = computed(() => Boolean(normalizedUniqueCode.value))
const schoolLogo = computed(() => props.logoFile ? props.logoFile : authUser.user?.school_logo || undefined)

const attendanceUrl = computed(() => {
    if (!hasUniqueCode.value) {
        return ''
    }

    const resolved = router.resolve({
        name: 'attendances-qr-detail',
        params: {
            unique_code: normalizedUniqueCode.value,
        },
    })

    return new URL(resolved.href, window.location.origin).toString()
})

const notify = (message, type = 'success') => {
    if (typeof window.showMessage === 'function') {
        window.showMessage(message, type)
    }
}

const ensureModal = async () => {
    await nextTick()

    if (!modalElement.value) {
        return null
    }

    if (!modalInstance) {
        modalInstance = new window.bootstrap.Modal(modalElement.value, {
            backdrop: true,
            keyboard: true,
            focus: true,
        })
    }

    return modalInstance
}

const showModal = async () => {
    if (!hasUniqueCode.value) {
        emit('update:modelValue', false)
        return
    }

    const instance = await ensureModal()
    instance?.show()
}

const hideModal = () => {
    modalInstance?.hide()
}

const copyLink = async () => {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(attendanceUrl.value)
        } else {
            const input = document.createElement('textarea')
            input.value = attendanceUrl.value
            input.setAttribute('readonly', 'readonly')
            input.style.position = 'absolute'
            input.style.left = '-9999px'
            document.body.appendChild(input)
            input.select()
            document.execCommand('copy')
            document.body.removeChild(input)
        }

        notify('Enlace copiado correctamente.')
    } catch {
        notify('No fue posible copiar el enlace.', 'error')
    }
}

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            await showModal()
        } else {
            hideModal()
        }
    }
)

onMounted(() => {
    modalElement.value?.addEventListener('hidden.bs.modal', () => {
        emit('update:modelValue', false)
    })

    if (props.modelValue) {
        showModal()
    }
})

onBeforeUnmount(() => {
    modalInstance?.dispose()
    modalInstance = null
})
</script>

<style scoped lang="scss">
.attendance-qr-modal__content {
    background:
        radial-gradient(circle at top, rgba(13, 110, 253, 0.1), transparent 48%),
        var(--bs-body-bg, #ffffff);
    color: var(--bs-body-color, #212529);
}

.attendance-qr-modal__preview {
    display: flex;
    justify-content: center;
}

.attendance-qr-modal__canvas {
    display: flex;
    justify-content: center;
    min-width: 272px;
    min-height: 272px;
    padding: 1rem;
    border-radius: 1.5rem;
    background: #bfc9d4;
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
}

.attendance-qr-modal__canvas :deep(.attendance-qr-modal__qr-instance) {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.9rem;
}

.attendance-qr-modal__canvas :deep(.attendance-qr-modal__download-button) {
    min-width: 10rem;
    margin: 10px 50px 10px;
}

.attendance-qr-modal__meta {
    max-width: 22rem;
    margin: 0 auto;
}

</style>
