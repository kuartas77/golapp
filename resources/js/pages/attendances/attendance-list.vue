<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-3 col-lg-3 col-sm-12">
                <div class="panel br-6 p-2" data-tour="attendance-search-panel">
                    <div class="panel-body">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-outline-info btn-sm" @click="tutorial.start()">
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
                                    <h5>Selecciona Día de Entrenamiento:</h5>
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
                <div class="panel br-6 p-2" data-tour="attendance-table-panel">
                    <div class="panel-body">
                        <div data-tour="attendance-session-summary">
                            <h5 v-if="modelGroup">{{ modelGroup.full_group }}</h5>
                            <h6 v-if="classDaySelected">
                                Clase: {{ `#${classDaySelected.index} | ${classDaySelected.day} ${classDaySelected.date}` }}
                            </h6>
                        </div>
                        <div class="row" data-tour="attendance-table">
                            <DataTable :options="options" :data="attendancesGroup" class="table table-bordered table-sm"
                                id="attendance_table" ref="attendance_table">

                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Asistencia</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>

                                <template #player-photo="props">
                                    <div class="media d-md-flex d-block text-sm-start text-center">
                                        <div class="media-aside align-self-start avatar avatar-sm me-1">
                                            <img :src="props.rowData.inscription.player.photo_url" alt="avatar"
                                                class="player-avatar" />
                                        </div>
                                        <div class="media-body">
                                            <div class="d-xl-flex d-block justify-content-between">
                                                <div>
                                                    <small>
                                                        {{ props.rowData.inscription.player.full_names }}
                                                    </small>
                                                    <p>
                                                        <small>
                                                            {{ props.rowData.inscription.player.unique_code }}
                                                            <span>
                                                                | {{ props.rowData.inscription.player.category }}
                                                            </span>
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template #attendance-select="props">
                                    <select
                                        class="form-select form-select-sm"
                                        :value="props.rowData[classDaySelected.column] ?? ''"
                                        :disabled="isLoading"
                                        :id="props.rowData.id"
                                        data-tour="attendance-status-select"
                                        @change="onChangeAttendance(props.rowData, $event.target.value)"
                                    >
                                        <option value="">Selecciona...</option>
                                        <option
                                            v-for="(label, value) in attendanceTypes"
                                            :key="value"
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                </template>

                                <template #observations="props">
                                    <button
                                        type="button"
                                        class="badge badge-primary btn btn-sm m-1"
                                        data-tour="attendance-observation-button"
                                        @click="onClickOpenModalObservation(props.rowData)"
                                    >
                                        Observación
                                    </button>
                                </template>
                            </DataTable>
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
                                class="form-control form-control-sm" v-model="takeAttendance.observation"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn" @click="onCancelModalObservation">
                        <i class="flaticon-cancel-12"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" @click="onSaveModalObservation">
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
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useAttendances from '@/composables/attendances/attendances'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { attendancesTutorial } from '@/tutorials/attendances'

const form = useTemplateRef('form')

const {
    attendance_table,
    isLoading,
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
    takeAttendance,
    optionsMonths,
    attendanceTypes,
    options,
    handleSearchClassdays,
    clickClassDay,
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
