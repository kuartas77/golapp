<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-3 col-lg-3 col-sm-12">
                <div class="panel br-6 p-2">
                    <div class="panel-body">
                        <div class="row">
                            <div class="text-center">
                                <Form ref="form" :validation-schema="schema" @submit="handleSearchClassdays"
                                    :initial-values="formData" class="align-items-center justify-content-center">
                                    <div class="mb-3">
                                        <label for="training_group" class="sr-only">Grupo</label>
                                        <Field name="training_group" v-slot="{ field, handleChange, handleBlur }">
                                            <multiselect id="training_group" v-bind="field" @change="handleChange"
                                                @blur="handleBlur" v-model="modelGroup" :options="groups"
                                                :multiple="false" :searchable="true" :preselect-first="false"
                                                track-by="id" label="full_group" placeholder="Grupo"
                                                :show-labels="false" />
                                        </Field>
                                        <ErrorMessage name="training_group" class="custom-error" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="month" class="sr-only">Mes</label>
                                        <Field name="month" v-slot="{ field, handleChange, handleBlur }">
                                            <multiselect id="month" v-bind="field" @change="handleChange"
                                                @blur="handleBlur" v-model="modelMonth" :options="optionsMonths"
                                                :multiple="false" :searchable="true" :preselect-first="false"
                                                track-by="value" label="label" placeholder="Mes" :show-labels="false" />
                                        </Field>
                                        <ErrorMessage name="month" class="custom-error" />
                                    </div>
                                    <div class="mb-3">
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

                            <div v-if="classDays.length" class="row text-center mt-3">
                                <div class="col-12">
                                    <h5>Selecciona Día de Entrenamiento:</h5>
                                    <template v-for="classDay in classDays" :key="classDay.id">
                                        <button class="badge outline-badge-info btn btn-sm m-1" :disabled="isLoading"
                                            @click="clickClassDay(classDay)">
                                            {{ `#${classDay.index} | ${classDay.day} ${classDay.date}` }}
                                        </button>
                                    </template>
                                </div>
                                <div class="col-12">
                                    <div class="btn-group mt-1" role="group">
                                        <a v-if="export_pdf" :href="export_pdf" target="_blank"
                                            class="badge badge-info btn btn-sm me-1">
                                            <i class="far fa-file-pdf fa-lg"> PDF</i>
                                        </a>
                                        <a v-if="export_excel" :href="export_excel" target="_blank"
                                            class="badge badge-info btn btn-sm me-1">
                                            <i class="far fa-file-excel fa-lg"> Excel</i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layout-spacing col-xl-9 col-lg-9 col-sm-12">
                <div class="panel br-6 p-2">
                    <div class="panel-body">
                        <h5 v-if="modelGroup">
                            {{ modelGroup.full_group }}
                            <small v-if="classDaySelected"> | {{ `#${classDaySelected.index} | ${classDaySelected.day} ${classDaySelected.date}` }}</small>
                        </h5>
                        <div class="row">
                            <DataTable :options="options" :data="attendancesGroup" class="table table-bordered table-sm"
                                id="attendance_table" ref="attendance_table">

                                <template #player-photo="props">
                                    <div class="media d-md-flex d-block text-sm-start text-center">
                                        <div class="media-aside align-self-start avatar avatar-sm me-1">
                                            <img :src="props.rowData.inscription.player.photo_url" alt="avatar"
                                                class="rounded-circle" />
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
                                <template #bagClick="props">
                                    <button type="button" class="badge outline-badge-primary btn btn-sm m-1"
                                        @click="onClickOpenModalAttendance(props.rowData)" :data-id="props.rowData.id">
                                        {{ attendanceTypes[props.rowData[classDaySelected.column]] ?? 'Tomar Asistencia'}}
                                    </button>
                                </template>

                                <template #observations="props">
                                    <button type="button" class="badge outline-badge-primary btn btn-sm m-1"
                                    @click="onClickOpenModalObservations(props.rowData)">Ver</button>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="composeModalAttendance" tabindex="-1" role="dialog" aria-labelledby="attendanceModal"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" v-if="takeAttendance">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModal">{{ takeAttendance.player_name }}</h5>
                    <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                        class="btn-close" @click="onCancelModalAttendance"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3 row" v-if="classDaySelected">
                        <label for="attendance_number" class="col-sm-2 col-form-label">Entrenamiento#:</label>
                        <div class="col-sm-4">
                            <input type="text" readonly class="form-control-plaintext" id="attendance_number"
                                :value="classDaySelected.index">
                        </div>
                        <label for="attendance_name" class="col-sm-2 col-form-label">Fecha:</label>
                        <div class="col-sm-4">
                            <input type="text" readonly class="form-control-plaintext" id="attendance_name"
                                :value="`${classDaySelected.day} ${classDaySelected.date} de ${classDaySelected.month_name}`">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="select_attendance" class="col-sm-4 col-form-label">Tomar Asistencia:</label>
                        <div class="col-sm-8">
                            <select name="select_attendance" id="select_attendance"
                                class="form-control form-control-sm form-select" v-model="takeAttendance.value">
                                <option value="null">Selecciona...</option>
                                <option :value="index" v-for="(value, index) in attendanceTypes" :key="index">
                                    {{ value }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="observation">Observaciónes para el deportista en el entrenamiento:</label>
                            <span class="bar"></span>
                            <textarea name="observations" id="single_observation" cols="30" rows="10"
                                class="form-control form-control-sm" v-model="takeAttendance.observation"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" @click="onCancelModalAttendance">
                        <i class="flaticon-cancel-12"></i> Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="onSaveModalAttendance">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="composeModalObservations" tabindex="-1" role="dialog" aria-labelledby="observationModal"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-md" role="document" >
            <div class="modal-content" v-if="takeAttendance">
                <div class="modal-header">
                    <h5 class="modal-title" id="observationModal">{{ takeAttendance.player_name }}</h5>
                    <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                        class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <label for="observation">Observaciónes por fecha de entrenamiento:</label>
                            <span class="bar"></span>
                            <textarea name="observations" id="single_observation" cols="30" rows="10"
                                class="form-control form-control-sm" v-model="takeAttendance.observations" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal" data-bs-dismiss="modal"><i
                            class="flaticon-cancel-12"></i> Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</template>
<script>
export default {
    name: "attendance-list",
};
</script>
<script setup>
import { ErrorMessage, Field, Form } from "vee-validate";
import useAttendances from '@/composables/attendances/attendances'

const {
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
    onClickOpenModalAttendance,
    onCancelModalAttendance,
    onSaveModalAttendance,
    onClickOpenModalObservations
} = useAttendances()




</script>