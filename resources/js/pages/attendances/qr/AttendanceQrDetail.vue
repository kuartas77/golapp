<template>
    <div class="attendance-qr-detail">
        <Loader :is-loading="loading" loading-text="Cargando asistencia rápida..." />

        <section class="attendance-qr-detail__shell">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                        <div class="d-flex gap-3 align-items-start">
                            <img
                                :src="context?.player?.photo_url || '/img/user.webp'"
                                :alt="context?.player?.full_names || 'Deportista'"
                                class="attendance-qr-detail__photo"
                            >

                            <div>
                                <span class="attendance-qr-detail__eyebrow">Toma rápida</span>
                                <h1 class="h2 mb-2">{{ context?.player?.full_names || 'Asistencia QR' }}</h1>
                                <p class="text-muted mb-2">
                                    {{ context?.training_group?.full_group || 'Buscando grupo actual...' }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span v-if="context?.unique_code" class="badge text-bg-light border">Código {{ context.unique_code }}</span>
                                    <span v-if="context?.month_name" class="badge text-bg-light border">{{ context.month_name }} {{ context.year }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-self-start">
                            <router-link :to="{ name: 'attendances-qr' }" class="btn btn-outline-secondary btn-sm">
                                Cambiar código
                            </router-link>
                            <router-link :to="{ name: 'attendances' }" class="btn btn-outline-secondary btn-sm">
                                Asistencias
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger mt-4 mb-0" role="alert">
                {{ errorMessage }}
            </div>

            <div v-else-if="context" class="row g-4 mt-1">
                <div class="col-12 col-xl-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                                <div>
                                    <h2 class="h5 mb-1">Selecciona la clase</h2>
                                    <p class="text-muted mb-0 small">
                                        El instructor sólo elige el día y el sistema guarda asistencia en la columna correcta.
                                    </p>
                                </div>
                                <span class="badge text-bg-light border">{{ context.class_days.length }} clase(s) en el mes</span>
                            </div>

                            <div v-if="!context.class_days.length" class="alert alert-warning mb-0" role="alert">
                                Este grupo no tiene días de clase configurados para el mes actual.
                            </div>

                            <div v-else class="attendance-qr-detail__day-picker">
                                <p class="attendance-qr-detail__day-picker-title">Selecciona Día de Entrenamiento:</p>

                                <div class="attendance-qr-detail__day-actions">
                                    <button
                                        v-for="classDay in context.class_days"
                                        :key="classDay.column"
                                        type="button"
                                        class="btn btn-sm attendance-qr-detail__day-button"
                                        :class="dayButtonClass(classDay)"
                                        @click="selectedColumn = classDay.column"
                                    >
                                        {{ dayButtonText(classDay) }}
                                    </button>
                                </div>

                                <p v-if="selectedDay" class="attendance-qr-detail__day-helper mb-0">
                                    Estado actual: <strong>{{ selectedDay.current_label || 'Sin registro' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-3">Resumen de guardado</h2>

                            <div v-if="selectedDay" class="attendance-qr-detail__summary">
                                <div class="attendance-qr-detail__summary-row">
                                    <span>Clase seleccionada</span>
                                    <strong>{{ selectedDay.label }}</strong>
                                </div>
                                <div class="attendance-qr-detail__summary-row">
                                    <span>Fecha</span>
                                    <strong>{{ selectedDay.date }}</strong>
                                </div>
                                <div class="attendance-qr-detail__summary-row">
                                    <span>Estado actual</span>
                                    <strong>{{ selectedDay.current_label || 'Sin registro' }}</strong>
                                </div>
                                <div class="attendance-qr-detail__summary-row">
                                    <span>Nuevo valor</span>
                                    <strong>Asistencia</strong>
                                </div>
                            </div>

                            <div v-else class="text-muted">
                                Selecciona una clase para habilitar el guardado.
                            </div>

                            <div v-if="selectedDay?.current_value && selectedDay.current_value !== 1" class="alert alert-warning mt-3 mb-0" role="alert">
                                Ya existía un registro para esta clase. Al guardar se reemplazará por Asistencia.
                            </div>

                            <div v-if="successMessage" class="alert alert-success mt-3 mb-0" role="alert">
                                {{ successMessage }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div v-if="context && !errorMessage" class="attendance-qr-detail__sticky-bar">
            <div class="attendance-qr-detail__sticky-copy">
                <strong>{{ selectedDay ? selectedDay.label : 'Selecciona una clase' }}</strong>
                <span>{{ selectedDay ? 'Se guardará Asistencia.' : 'Elige el día para habilitar el botón.' }}</span>
            </div>

            <button
                type="button"
                class="btn btn-primary btn-lg attendance-qr-detail__sticky-button"
                :disabled="!selectedDay || saving || loading"
                @click="saveAttendance"
            >
                {{ saving ? 'Guardando...' : 'Guardar asistencia' }}
            </button>
        </div>
    </div>

    <breadcrumb :parent="'Plataforma'" :current="'Asistencia QR'" />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import { usePageTitle } from '@/composables/use-meta'

const route = useRoute()
const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const selectedColumn = ref(null)
const context = ref(null)

usePageTitle(computed(() => context.value?.player?.full_names ? `Asistencia QR · ${context.value.player.full_names}` : 'Asistencia QR'))

const selectedDay = computed(() => {
    return context.value?.class_days?.find((classDay) => classDay.column === selectedColumn.value) ?? null
})

const chooseDefaultDay = (classDays = []) => {
    const preferredDay = classDays.find((classDay) => classDay.is_today)
        ?? classDays.find((classDay) => !classDay.current_value)
        ?? classDays[0]
        ?? null

    selectedColumn.value = preferredDay?.column ?? null
}

const loadContext = async (uniqueCode) => {
    loading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        const response = await api.get(`/api/v2/attendance-qr/${encodeURIComponent(uniqueCode)}`)
        context.value = response.data
        chooseDefaultDay(response.data.class_days ?? [])
    } catch (error) {
        context.value = null
        selectedColumn.value = null
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar el formulario rápido de asistencia.'
    } finally {
        loading.value = false
    }
}

const saveAttendance = async () => {
    if (!context.value?.assist_id || !selectedDay.value) {
        return
    }

    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        await api.post(`/api/v2/attendance-qr/${context.value.assist_id}/take`, {
            column: selectedDay.value.column,
        })

        context.value = {
            ...context.value,
            current_values: {
                ...(context.value.current_values || {}),
                [selectedDay.value.column]: 1,
            },
            class_days: context.value.class_days.map((classDay) => (
                classDay.column === selectedDay.value.column
                    ? { ...classDay, current_value: 1, current_label: 'Asistencia' }
                    : classDay
            )),
        }

        successMessage.value = `Asistencia guardada para ${selectedDay.value.label}.`
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible guardar la asistencia.'
    } finally {
        saving.value = false
    }
}

const dayButtonClass = (classDay) => {
    if (classDay.column === selectedColumn.value) {
        return 'btn-primary'
    }

    if (classDay.current_value) {
        return 'btn-success'
    }

    if (classDay.is_today) {
        return 'btn-warning'
    }

    return 'btn-outline-info'
}

const dayButtonText = (classDay) => {
    if (classDay.is_today && !classDay.current_value) {
        return `${classDay.label} · Hoy`
    }

    if (classDay.current_value) {
        return `${classDay.label} · ${classDay.current_label || 'Registrado'}`
    }

    return classDay.label
}

watch(
    () => route.params.unique_code,
    (uniqueCode) => {
        if (typeof uniqueCode === 'string' && uniqueCode.trim()) {
            loadContext(uniqueCode.trim())
        }
    },
    { immediate: true }
)
</script>

<style scoped>
.attendance-qr-detail {
    padding: 1rem 1rem 7rem;
}

.attendance-qr-detail__shell {
    max-width: 72rem;
    margin: 0 auto;
}

.attendance-qr-detail__hero {
    background:
        linear-gradient(135deg, rgba(13, 110, 253, 0.12), rgba(25, 135, 84, 0.08)),
        var(--bs-body-bg, #ffffff);
}

.attendance-qr-detail__eyebrow {
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

.attendance-qr-detail__photo {
    width: 84px;
    height: 84px;
    border-radius: 1.25rem;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 0.75rem 2rem rgba(15, 23, 42, 0.14);
}

.attendance-qr-detail__day-picker {
    display: grid;
    gap: 0.85rem;
}

.attendance-qr-detail__day-picker-title {
    margin-bottom: 0;
    font-size: 0.96rem;
    font-weight: 600;
}

.attendance-qr-detail__day-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.attendance-qr-detail__day-button {
    border-radius: 999px;
    padding: 0.45rem 0.8rem;
    font-weight: 600;
    line-height: 1.2;
}

.attendance-qr-detail__day-helper {
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.88rem;
}

.attendance-qr-detail__summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.attendance-qr-detail__summary {
    display: grid;
    gap: 0.85rem;
}

.attendance-qr-detail__summary-row {
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--bs-secondary-rgb, 108, 117, 125), 0.12);
}

.attendance-qr-detail__sticky-bar {
    position: fixed;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1030;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    background: rgba(var(#3b3f5c, 255, 255, 255), 0.94);
    backdrop-filter: blur(14px);
    box-shadow: 0 -0.5rem 2rem rgba(15, 23, 42, 0.12);
}

.attendance-qr-detail__sticky-copy {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.attendance-qr-detail__sticky-copy span {
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.9rem;
}

.attendance-qr-detail__sticky-button {
    flex-shrink: 0;
}

@media (max-width: 767.98px) {
    .attendance-qr-detail {
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }

    .attendance-qr-detail__summary-row,
    .attendance-qr-detail__sticky-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .attendance-qr-detail__day-actions {
        gap: 0.45rem;
    }

    .attendance-qr-detail__day-button {
        width: 100%;
        text-align: left;
    }

    .attendance-qr-detail__sticky-button {
        width: 100%;
    }
}
</style>
