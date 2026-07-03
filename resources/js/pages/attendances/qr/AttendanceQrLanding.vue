<template>
    <div class="attendance-qr-page">
        <section class="attendance-qr-page__shell">
            <div class="card border-0 shadow-sm overflow-hidden" data-tour="attendance-qr-context">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="attendance-qr-page__eyebrow">Asistencia QR</span>
                            <h1 class="h2 mb-2">Toma rápida de asistencia</h1>
                            <p class="text-muted mb-0">
                                Escanea el QR del deportista o escribe el código manualmente para abrir su formulario rápido.
                            </p>
                        </div>

                        <router-link :to="{ name: 'attendances' }" class="btn btn-outline-secondary btn-sm align-self-start">
                            Volver a asistencias
                        </router-link>
                        <button type="button" class="btn btn-info btn-sm align-self-start" @click="tutorial.start()"><i class="fa-regular fa-circle-question me-2"></i>Guía</button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4" data-tour="attendance-qr-form">
                <div class="card-body p-4">
                    <form class="row g-3 align-items-end" @submit.prevent="openQrAttendance">
                        <div class="col-12">
                            <label for="unique_code" class="form-label">Código único</label>
                            <input
                                id="unique_code"
                                v-model.trim="uniqueCode"
                                type="text"
                                class="form-control form-control-lg"
                                placeholder="Ejemplo: RC-1001"
                                autocomplete="off"
                                autocapitalize="characters"
                            >
                        </div>

                        <div class="col-12">
                            <div class="attendance-qr-page__tips">
                                <!-- <span class="badge text-bg-light border">Mobile-first</span> -->
                                <span class="badge text-bg-light border">Requiere ingreso</span>
                                <span class="badge text-bg-light border">Mes actual</span>
                            </div>
                        </div>

                        <div v-if="errorMessage" class="col-12">
                            <div class="alert alert-danger mb-0" role="alert">
                                {{ errorMessage }}
                            </div>
                        </div>

                        <div class="col-12 col-lg-auto">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                Abrir formulario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />
    <breadcrumb :parent="'Plataforma'" :current="'Asistencia QR'" />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePageTitle } from '@/composables/use-meta'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { attendanceQrTutorial } from '@/tutorials/operations'

usePageTitle('Asistencia QR')
const tutorial = usePageTutorial(attendanceQrTutorial)

const router = useRouter()
const uniqueCode = ref('')
const errorMessage = ref('')

const openQrAttendance = async () => {
    const normalizedCode = uniqueCode.value.trim()

    if (!normalizedCode) {
        errorMessage.value = 'Ingresa un código único para continuar.'
        return
    }

    errorMessage.value = ''

    await router.push({
        name: 'attendances-qr-detail',
        params: {
            unique_code: normalizedCode,
        },
    })
}
</script>

<style scoped>
.attendance-qr-page {
    padding: 1rem 0 4rem;
}

.attendance-qr-page__shell {
    max-width: 56rem;
    margin: 0 auto;
    padding: 0 1rem;
}

.attendance-qr-page__eyebrow {
    display: inline-block;
    margin-bottom: 0.75rem;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.12);
    color: var(--bs-primary, #0d6efd);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.attendance-qr-page__tips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
</style>
