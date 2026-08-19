<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-3 col-lg-3 col-sm-12">
                <div class="panel br-6 p-2" data-tour="attendance-search-panel">
                    <div class="panel-body">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                                <i class="fa-regular fa-circle-question me-2"></i>
                                Guia
                            </button>
                        </div>

                        <div class="row">
                            <div class="text-center">
                                <Form ref="form" :validation-schema="schema" @submit="handleSearchClassdays"
                                    :initial-values="formData" class="align-items-center justify-content-center">
                                    <div class="mb-3" data-tour="attendance-group-filter">
                                        <label for="training_group_id" class="sr-only">Grupo</label>
                                        <Field name="training_group_id" as="CustomSelect2" id="training_group_id"
                                            :options="groups" />
                                        <ErrorMessage name="training_group_id" class="custom-error" />
                                    </div>
                                    <div class="mb-3" data-tour="attendance-month-filter">
                                        <label for="month" class="sr-only">Mes</label>
                                        <Field name="month" as="CustomSelect2" id="month" :options="optionsMonths" />
                                        <ErrorMessage name="month" class="custom-error" />
                                    </div>
                                    <div class="mb-3" data-tour="attendance-search-button">
                                        <button type="submit" class="btn btn-primary w-100" :disabled="isLoading">
                                            Buscar
                                            <template v-if="isLoading">
                                                &nbsp;
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-loader spin me-2">
                                                    <line x1="12" y1="2" x2="12" y2="6"></line>
                                                    <line x1="12" y1="18" x2="12" y2="22"></line>
                                                    <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                                    <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                                    <line x1="2" y1="12" x2="6" y2="12"></line>
                                                    <line x1="18" y1="12" x2="22" y2="12"></line>
                                                    <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                                    <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                                </svg>
                                            </template>
                                        </button>
                                    </div>
                                </Form>
                            </div>

                            <div v-if="classDays.length" class="row text-center mt-3" data-tour="attendance-classdays">
                                <div class="col-12">
                                    <span>Selecciona día de entrenamiento:</span>
                                    <template v-for="classDay in classDays" :key="classDay.id">
                                        <button
                                            class="badge outline-badge-info btn btn-sm m-1"
                                            :disabled="isLoading"
                                            data-tour="attendance-classday-button"
                                            @click="clickClassDay(classDay)">
                                            {{ `#${classDay.index} | ${classDay.day} ${classDay.date}` }}
                                        </button>
                                    </template>
                                </div>
                                <div class="col-12" data-tour="attendance-exports">
                                    <div class="btn-group mt-1" role="group">
                                        <a v-if="export_pdf" :href="export_pdf" target="_blank"
                                            class="badge badge-info btn btn-sm me-1">
                                            <i class="far fa-file-pdf fa-lg"></i>PDF
                                        </a>
                                        <a v-if="export_excel" :href="export_excel" target="_blank"
                                            class="badge badge-info btn btn-sm me-1">
                                            <i class="far fa-file-excel fa-lg"></i>Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layout-spacing col-xl-9 col-lg-9 col-sm-12">
                <div class="attendance-results" data-tour="attendance-table-panel">
                    <div class="attendance-results__body">
                        <div data-tour="attendance-session-summary">
                            <h5 v-if="modelGroup">{{ modelGroup.full_group }}</h5>
                            <h6 v-if="classDaySelected">
                                Clase: {{ `#${classDaySelected.index} | ${classDaySelected.day} ${classDaySelected.date}` }}
                            </h6>
                        </div>
                        <div v-if="retiredRowsCount" class="alert alert-warning py-2" role="alert">
                            Hay {{ retiredRowsCount }} registro(s) con inscripción retirada. Se muestran solo como historial y permanecen en solo lectura.
                        </div>
                        <div
                            v-if="canBulkMarkAttendance"
                            class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"
                        >
                            <span class="text-muted">
                                {{ eligibleAttendanceRowsCount }} deportista(s) activo(s) para esta clase.
                            </span>
                            <button
                                type="button"
                                class="btn btn-success btn-sm"
                                :disabled="isLoading || isBulkUpdating"
                                @click="markAttendanceForAllLoaded"
                            >
                                <i class="fa-solid fa-check-double me-2"></i>
                                Marcar asistencia a todos
                            </button>
                        </div>
                        <ContentState
                            v-if="isLoading && attendancesGroup.length === 0"
                            type="loading"
                            title="Cargando asistencias"
                            message="Estamos consultando las clases y los deportistas del periodo seleccionado."
                            data-tour="attendance-table"
                        />
                        <ContentState
                            v-else-if="globalError"
                            type="error"
                            title="No fue posible cargar las asistencias"
                            :message="globalError"
                            action-label="Reintentar"
                            data-tour="attendance-table"
                            @action="retryLastRequest"
                        />
                        <ContentState
                            v-else-if="!hasSearched"
                            type="empty"
                            title="Selecciona un grupo"
                            message="Elige un grupo y presiona Buscar para mostrar sus días de entrenamiento en el mes seleccionado."
                            data-tour="attendance-table"
                        />
                        <ContentState
                            v-else-if="classDays.length === 0"
                            type="empty"
                            title="No hay días de entrenamiento"
                            :message="`No encontramos clases para ${modelGroup?.full_group || 'el grupo'} en ${modelMonth?.label || 'el periodo seleccionado'}.`"
                            data-tour="attendance-table"
                        />
                        <ContentState
                            v-else-if="!classDaySelected"
                            type="empty"
                            title="Selecciona un día de entrenamiento"
                            message="Elige una de las fechas disponibles para consultar y registrar la asistencia."
                            data-tour="attendance-table"
                        />
                        <ContentState
                            v-else-if="attendancesGroup.length === 0"
                            type="empty"
                            title="No hay deportistas en esta clase"
                            message="Puedes crear los registros de asistencia faltantes para el grupo y periodo seleccionados."
                            action-label="Crear asistencias"
                            data-tour="attendance-table"
                            @action="createMissingAttendances"
                        />
                        <div v-else data-tour="attendance-table">
                            <div class="attendance-search mb-3">
                                <i class="fa-solid fa-magnifying-glass attendance-search__icon" aria-hidden="true"></i>
                                <input
                                    type="search"
                                    class="form-control form-control-sm attendance-search__input"
                                    placeholder="Buscar deportista"
                                    aria-label="Buscar deportista"
                                    data-attendance-player-search="true"
                                    :value="playerSearchTerm"
                                    @input="applyPlayerSearch"
                                />
                            </div>

                            <div class="attendance-card-list" role="list">
                                <AttendancePlayerCard
                                    v-for="row in filteredAttendancesGroup"
                                    :key="`${classDaySelected.column}-${row.id}`"
                                    :row="row"
                                    :attendance-types="attendanceTypes"
                                    :attendance-value="attendanceValueFor(row)"
                                    :read-only="attendanceRowReadOnly(row)"
                                    @attendance-change="onChangeAttendance(row, $event)"
                                    @open-observation="onClickOpenModalObservation(row)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="composeModalObservation" tabindex="-1" role="dialog" aria-labelledby="observationModal"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" v-if="takeAttendance">
                <div class="modal-header">
                    <h5 class="modal-title" id="observationModal">
                        {{ takeAttendance.player_name }}
                    </h5>
                    <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                        class="btn-close" @click="onCancelModalObservation"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-1 row" v-if="classDaySelected">
                        <label for="attendance_number" class="col-sm-4 col-form-label">
                            Entrenamiento#:
                        </label>
                        <div class="col-sm-8">
                            <input type="text" readonly class="form-control-plaintext" id="attendance_number"
                                :value="classDaySelected.index" />
                        </div>
                    </div>

                    <div class="mb-1 row" v-if="classDaySelected">
                        <label for="attendance_name" class="col-sm-4 col-form-label">
                            Fecha:
                        </label>
                        <div class="col-sm-8">
                            <input type="text" readonly class="form-control-plaintext" id="attendance_name"
                                :value="`${classDaySelected.day} ${classDaySelected.date} de ${classDaySelected.month_name}`" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group" data-tour="attendance-observation-field">
                            <label for="single_observation">
                                Observación para el deportista en el entrenamiento:
                            </label>
                            <span class="bar"></span>
                            <textarea name="observations" id="single_observation" cols="30" rows="10"
                                class="form-control form-control-sm" v-model="takeAttendance.observation"
                                :disabled="takeAttendance.inscription_deleted"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" @click="onCancelModalObservation">
                        <i class="flaticon-cancel-12"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" :disabled="takeAttendance.inscription_deleted" @click="onSaveModalObservation">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <PageTutorialOverlay :tutorial="tutorial" />
    <breadcrumb :parent="'Plataforma'" :current="'Asistencias'" />

    <!-- <teleport defer to="#search_players">
        <input type="text" id="players" name="players" class="form-control control-sm form-control-custom" placeholder="Deportista">
    </teleport> -->
</template>
<script>
export default {
    name: "attendance-list",
};
</script>
<script setup>
import { useTemplateRef } from 'vue'
import { ErrorMessage, Field, Form } from "vee-validate";
import ContentState from '@/components/general/ContentState.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import AttendancePlayerCard from '@/pages/attendances/components/AttendancePlayerCard.vue'
import useAttendances from '@/composables/attendances/attendances'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { attendancesTutorial } from '@/tutorials/attendances'

const form = useTemplateRef('form')

const {
    isLoading,
    isBulkUpdating,
    groups,
    schema,
    formData,
    modelGroup,
    modelMonth,
    export_pdf,
    export_excel,
    classDays,
    classDaySelected,
    attendancesGroup,
    globalError,
    hasSearched,
    playerSearchTerm,
    filteredAttendancesGroup,
    takeAttendance,
    retiredRowsCount,
    eligibleAttendanceRowsCount,
    canBulkMarkAttendance,
    optionsMonths,
    attendanceTypes,
    attendanceRowReadOnly,
    attendanceValueFor,
    applyPlayerSearch,
    handleSearchClassdays,
    retryLastRequest,
    createMissingAttendances,
    clickClassDay,
    markAttendanceForAllLoaded,
    onChangeAttendance,
    onClickOpenModalObservation,
    onCancelModalObservation,
    onSaveModalObservation
} = useAttendances()
const tutorial = usePageTutorial(attendancesTutorial, {
    formRef: form,
    groups,
    formData,
    optionsMonths,
    classDays,
    classDaySelected,
    attendancesGroup,
    takeAttendance,
    exportPdf: export_pdf,
    exportExcel: export_excel,
    actions: {
        handleSearchClassdays,
        clickClassDay,
        openObservationModal: onClickOpenModalObservation,
        closeObservationModal: onCancelModalObservation,
    },
})
</script>

<style scoped>
.attendance-search {
    position: relative;
    max-width: 28rem;
}

.attendance-search__icon {
    position: absolute;
    top: 50%;
    left: 0.8rem;
    z-index: 1;
    color: #6b7280;
    font-size: 0.8rem;
    pointer-events: none;
    transform: translateY(-50%);
}

.attendance-search__input {
    min-height: 2.4rem;
    padding-left: 2.25rem;
}

.attendance-card-list {
    display: grid;
    gap: 0.75rem;
}

:global(body.dark .attendance-search__icon) {
    color: #bfc9d4;
}

@media (max-width: 767.98px) {
    .attendance-search {
        max-width: none;
    }
}
</style>
