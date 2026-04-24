<template>
    <Form ref="form_matches" :validation-schema="schema" :initial-values="{ date: null, hour: null }"
        @submit="handleSubmit">

        <div class="layout-px-spacing">
            <div class="layout-top-spacing">
                <div class="match-page-shell position-relative">
                    <Loader :is-loading="isLoading" />

                    <div class="row g-3 match-layout">
                        <div class="col-12 col-lg-4 col-xl-3 no-print">
                            <div class="card match-sidebar-card match-sticky-card h-100">
                                <div class="card-body match-card-body">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3"
                                        data-tour="match-form-header">
                                        <div>
                                            <h4 class="match-sidebar-title">{{ sidebarTitle }}</h4>
                                            <p class="match-sidebar-subtitle">{{ sidebarSubtitle }}</p>
                                        </div>

                                        <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                                            <i class="fa-regular fa-circle-question me-2"></i>
                                            Guia
                                        </button>
                                    </div>

                                    <div v-if="globalError" class="alert alert-danger py-2 px-3 small mb-3">
                                        {{ globalError }}
                                    </div>

                                    <div class="match-form-block" data-tour="match-form-general">
                                        <div class="match-form-heading">
                                            <h5 class="match-form-title">Detalles generales</h5>
                                            <p class="match-form-subtitle">
                                                Datos base del encuentro y del grupo de competencia.
                                            </p>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="competition_group" class="form-label">Grupo Competencia</label>
                                                    <Field name="competition_group" as="input" id="competition_group"
                                                        readonly class="form-control-plaintext match-plaintext-field">
                                                    </Field>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="professor" class="form-label">Director Técnico</label>
                                                    <Field name="professor" as="input" id="professor" readonly
                                                        class="form-control-plaintext match-plaintext-field">
                                                    </Field>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="tournament_id" class="form-label">Torneo<span
                                                            class="text-danger">&nbsp;(*)</span></label>
                                                    <Field name="tournament_id" v-slot="{ field, errorMessage, meta }">
                                                        <select id="tournament_id" v-bind="field"
                                                            class="form-select form-select-sm"
                                                            :class="{ 'is-invalid': meta.touched && errorMessage }">
                                                            <option :value="item.value"
                                                                v-for="item in settingsGroup.tournaments" :key="item.value">
                                                                {{ item.label }}
                                                            </option>
                                                        </select>
                                                    </Field>
                                                    <ErrorMessage name="tournament_id"
                                                        class="invalid-feedback d-block" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <inputField label="Lugar" name="place" :is-required="true" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <inputField label="Encuentro #" name="num_match" :is-required="true" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="date" class="form-label">Fecha<span
                                                            class="text-danger">&nbsp;(*)</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                        <Field name="date" v-slot="{ field, errorMessage, meta }" id="date">
                                                            <flat-pickr v-bind="field" v-model="field.value"
                                                                :config="flatpickrConfigDate"
                                                                class="form-control form-control-sm flatpickr" id="date"
                                                                :class="{ 'is-invalid': meta.touched && errorMessage }" />
                                                        </Field>
                                                    </div>
                                                    <ErrorMessage name="date" class="invalid-feedback d-block" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="hour" class="form-label">Hora<span
                                                            class="text-danger">&nbsp;(*)</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                        <Field name="hour" v-slot="{ field, errorMessage, meta }" id="hour">
                                                            <flat-pickr v-bind="field" v-model="field.value"
                                                                :config="flatpickrConfigHour"
                                                                class="form-control form-control-sm flatpickr" id="hour"
                                                                :class="{ 'is-invalid': meta.touched && errorMessage }" />
                                                        </Field>
                                                    </div>
                                                    <ErrorMessage name="hour" class="invalid-feedback d-block" />
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-group">
                                                    <inputField label="Equipo Rival" name="rival_name"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="isEdition" class="match-form-block" data-tour="match-form-result">
                                        <div class="match-form-heading">
                                            <h5 class="match-form-title">Resultado final</h5>
                                            <p class="match-form-subtitle">
                                                Carga el formato y completa el balance general del partido.
                                            </p>
                                        </div>

                                        <div class="form-group">
                                            <label for="file_upload" class="form-label">Cargar formato</label>
                                            <input type="file" id="file_upload" name="details"
                                                class="form-control form-control-sm" @change="uploadFileFormat"
                                                accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                            <small class="text-muted d-block mt-2">
                                                Se debe cargar el mismo formato descargado y los datos se mostrarán en el
                                                listado de estadísticas por deportista.
                                            </small>
                                        </div>

                                        <div class="match-score-strip">
                                            <div class="match-score-side">
                                                <label for="final_score_school" class="form-label">Escuela</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="far fa-futbol"></i></span>
                                                    <inputField name="final_score_school" :is-required="true"
                                                        id="final_score_school" />
                                                </div>
                                            </div>

                                            <div class="match-score-divider">vs</div>

                                            <div class="match-score-side">
                                                <label for="final_score_rival" class="form-label">Rival</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="far fa-futbol"></i></span>
                                                    <inputField name="final_score_rival" :is-required="true"
                                                        id="final_score_rival" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="general_concept" class="form-label">Concepto General</label>
                                            <Field name="general_concept" as="textarea" id="general_concept"
                                                class="form-control form-control-sm" rows="3"
                                                placeholder="Concepto General" />
                                            <ErrorMessage name="general_concept" class="invalid-feedback d-block" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8 col-xl-9">
                            <div class="card match-players-card h-100">
                                <div class="card-body match-card-body">
                                    <template v-if="isEdition">

                                        <div class="match-table-toolbar no-print" data-tour="match-form-stats">
                                            <div>
                                                <h4 class="match-table-title">Jugadores</h4>
                                                <small class="text-muted">
                                                    Actualiza minutos, desempeño y observaciones del partido.
                                                </small>
                                            </div>
                                            <span class="match-table-count">
                                                Total <strong>{{ skills_controls.length }}</strong>
                                            </span>
                                        </div>

                                        <div class="match-table-wrapper table-responsive no-print">
                                            <table class="table table-bordered table-sm dataTable align-middle match-table">
                                        <thead>
                                            <tr>
                                                <th class="dt-head-center" style="width: 15%;">deportista</th>
                                                <th class="dt-head-center" style="width: 2%;" v-tooltip.top="'Asistio?'">A
                                                </th>
                                                <th class="dt-head-center" style="width: 2%;" v-tooltip.top="'Titular?'">T
                                                </th>
                                                <th class="dt-head-center" style="width: 6%;"
                                                    v-tooltip.top="'Tiempo Jugado'">
                                                    ⏱️ MIN
                                                </th>
                                                <th class="dt-head-center" style="width: 12%;" v-tooltip.top="''">posición
                                                </th>
                                                <th class="dt-head-center" style="width: 1%" v-tooltip.top="'Goles'">⚽ G
                                                </th>
                                                <th class="dt-head-center" style="width: 1%"
                                                    v-tooltip.top="'Asistencias de Gol'">
                                                    🎯 A.G
                                                </th>
                                                <th class="dt-head-center" style="width: 1%" v-tooltip.top="'Atajadas'">🧤 A
                                                </th>
                                                <th class="dt-head-center" style="width: 1%"
                                                    v-tooltip.top="'Tarjetas Amarillas'">
                                                    🟨 t.a
                                                </th>
                                                <th class="dt-head-center" style="width: 1%"
                                                    v-tooltip.top="'Tarjetas Rojas'">
                                                    🟥t.r
                                                </th>
                                                <th class="dt-head-center" style="width: 1%;"
                                                    v-tooltip.top="'Calificación'">
                                                    ⭐ CAL
                                                </th>
                                                <!-- <th class="dt-head-center" style="width: 15%;" v-tooltip.top="''">
                                                    observación</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <template v-if="skills_controls.length">
                                                <tr v-for="(skill_control, index) in skills_controls"
                                                    :key="skill_control.id ?? skill_control.player?.id ?? index">
                                                    <td class="match-player-cell">
                                                        <div class="match-player-meta">
                                                            <img :src="skill_control.player.photo_url" alt="avatar"
                                                                class="player-avatar match-player-avatar" />
                                                            <div>
                                                                <span class="match-player-name">
                                                                    {{ skill_control.player.full_names }}
                                                                </span>
                                                                <span class="match-player-code">
                                                                    {{ skill_control.player.unique_code }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <checkbox :name="`skill_controls[${index}].assistance`"
                                                            return-value-type="number" />
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <checkbox :name="`skill_controls[${index}].titular`"
                                                            return-value-type="number" />
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].played_approx`"
                                                            v-slot="{ field, errorMessage, meta }">
                                                            <select v-bind="field"
                                                                :id="`skill_controls[${index}].played_approx`"
                                                                class="form-select form-select-sm"
                                                                :class="{ 'is-invalid': meta.touched && errorMessage }">
                                                                <option :value="ind" v-for="(num, ind) in 91" :key="`${ind}_${index}`">{{ ind }} MIN</option>

                                                            </select>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].played_approx`"
                                                            class="invalid-feedback d-block" />
                                                    </td>
                                                    <td class="match-position-cell">

                                                        <Field :name="`skill_controls[${index}].position`"
                                                            v-slot="{ field, errorMessage, meta }">
                                                            <select
                                                                v-bind="field"
                                                                :id="`skill_controls[${index}].position`"
                                                                class="form-select form-select-sm"
                                                                :class="{ 'is-invalid': meta.touched && errorMessage }">
                                                                <option value="">Selecciona...</option>
                                                                <option value="Portero">Portero</option>
                                                                <option value="Defensa (Central)">Defensa (Central)</option>
                                                                <option value="Defensa (Derecho)(Izquierdo)">
                                                                    Defensa (Derecho)(Izquierdo)</option>
                                                                <option value="Defensa (Izquierdo)">Defensa (Izquierdo)
                                                                </option>
                                                                <option value="Defensa (Derecho)">Defensa (Derecho)</option>
                                                                <option value="Defensa">Defensa</option>
                                                                <option value="Volante (Defensivo Izquierdo)">
                                                                    Volante (Defensivo Izquierdo)</option>
                                                                <option value="Volante (Defensivo Derecho)">Volante (Defensivo
                                                                    Derecho)</option>
                                                                <option value="Volante (Defensivo Central)">Volante (Defensivo
                                                                    Central)</option>
                                                                <option value="Volante (Ofensivo Izquierdo)">Volante (Ofensivo
                                                                    Izquierdo)</option>
                                                                <option value="Volante (Ofensivo Derecho)">Volante (Ofensivo
                                                                    Derecho)</option>
                                                                <option value="Volante (Ofensivo Central)">Volante (Ofensivo
                                                                    Central)</option>
                                                                <option value="Volante (Extremo Izquierdo)">Volante (Extremo
                                                                    Izquierdo)</option>
                                                                <option value="Volante (Extremo Derecho)">Volante (Extremo
                                                                    Derecho)</option>
                                                                <option value="Volante (Primera línea)">Volante (Primera
                                                                    línea)</option>
                                                                <option value="Volante (Segunda línea)">Volante (Segunda
                                                                    línea)</option>
                                                                <option value="Volante (Primera linea)">Volante (Primera
                                                                    linea)</option>
                                                                <option value="Volante (Segunda linea)">Volante (Segunda
                                                                    linea)</option>
                                                                <option value="Volante (Extremo)">Volante (Extremo)</option>
                                                                <option value="Volante (Central)">Volante (Central)</option>
                                                                <option value="Delantero (Izquierdo)">Delantero (Izquierdo)
                                                                </option>
                                                                <option value="Delantero (Derecho)">Delantero (Derecho)
                                                                </option>
                                                                <option value="Delantero (Central)">Delantero (Central)
                                                                </option>
                                                                <option value="Delantero">Delantero</option>
                                                            </select>
                                                        </Field>


                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].goals`" as="select"
                                                            :id="`skill_controls[${index}].goals`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                            <option value="7">7</option>
                                                            <option value="8">8</option>
                                                            <option value="9">9</option>
                                                            <option value="10">10</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].goals`"
                                                            class="invalid-feedback d-block" />
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].goal_assists`" as="select"
                                                            :id="`skill_controls[${index}].goal_assists`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                            <option value="7">7</option>
                                                            <option value="8">8</option>
                                                            <option value="9">9</option>
                                                            <option value="10">10</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].goal_assists`"
                                                            class="invalid-feedback d-block" />
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].goal_saves`" as="select"
                                                            :id="`skill_controls[${index}].goal_saves`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                            <option value="7">7</option>
                                                            <option value="8">8</option>
                                                            <option value="9">9</option>
                                                            <option value="10">10</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].goal_saves`"
                                                            class="invalid-feedback d-block" />
                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].yellow_cards`" as="select"
                                                            :id="`skill_controls[${index}].yellow_cards`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].yellow_cards`"
                                                            class="invalid-feedback d-block" />

                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].red_cards`" as="select"
                                                            :id="`skill_controls[${index}].red_cards`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].red_cards`"
                                                            class="invalid-feedback d-block" />

                                                    </td>
                                                    <td class="match-metric-cell">
                                                        <Field :name="`skill_controls[${index}].qualification`" as="select"
                                                            :id="`skill_controls[${index}].qualification`"
                                                            class="form-select form-select-sm">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </Field>

                                                        <ErrorMessage :name="`skill_controls[${index}].qualification`"
                                                            class="invalid-feedback d-block" />

                                                    </td>
                                                    <!-- <td class="match-observation-cell">
                                                        <Field :name="`skill_controls[${index}].general_concept`"
                                                            :id="`skill_controls[${index}].general_concept`" as="textarea"
                                                            class="form-control form-control-sm match-observation-field"
                                                            rows="2" />
                                                        <ErrorMessage :name="`skill_controls[${index}].general_concept`"
                                                            class="invalid-feedback d-block" />
                                                    </td> -->
                                                </tr>
                                            </template>
                                            <template v-else>
                                                <tr>
                                                    <td colspan="12" class="dt-body-center">
                                                        El grupo no cuenta con integrantes
                                                    </td>
                                                </tr>
                                            </template>

                                        </tbody>
                                    </table>
                                        </div>

                                    </template>
                                    <template v-else>
                                        <!-- <div class="match-table-toolbar no-print" data-tour="match-form-board">
                                            <div>
                                                <h4 class="match-table-title">Coachboard</h4>
                                                <small class="text-muted">
                                                    Organiza titulares y posiciones en la pizarra táctica.
                                                </small>
                                            </div>
                                            <span class="match-table-count">
                                                Plantilla <strong>{{ skills_controls.length }}</strong>
                                            </span>
                                        </div> -->

                                        <div class="match-board-wrapper no-print">
                                            <CoachBoard ref="coach_board" :initialPlayers="skills_controls" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="account-settings-footer mt-2 no-print" data-tour="match-form-actions">
                        <div class="as-footer-container">
                            <template v-if="urlExportFormat && isEdition">
                                <a :href="urlExportFormat" class="btn btn-info"
                                    v-tooltip.top="'Sólo datos de los deportistas, llenalo y lo podras cargar'">
                                    Descargar formato
                                </a>
                            </template>

                            <button type="submit" class="btn btn-info"
                                :disabled="!skills_controls.length || isLoading">
                                {{ submitLabel }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Form>

    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import "@/assets/sass/users/account-setting.scss"
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import dayjs from '@/utils/dayjs'
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component'
import Loader from '@/components/general/Loader'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import CoachBoard from "./coachboard/CoachBoard.vue"

import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta"
import { usePageTutorial } from '@/composables/usePageTutorial'
import { ErrorMessage, Field, Form } from "vee-validate"
import * as yup from 'yup'
import { computed, getCurrentInstance, useTemplateRef, onMounted, ref } from "vue"
import { useRoute } from "vue-router"
import { useSettingGroups } from '@/store/settings-store'
import { matchFormTutorial } from '@/tutorials/matches'

const props = defineProps({ isEdition: { type: Boolean, default: false } })

const { proxy } = getCurrentInstance()
const globalError = ref(null)
const route = useRoute()
const settingsGroup = useSettingGroups()
const currentTitlePage = ref("")
const formMatches = useTemplateRef('form_matches')
const tutorial = usePageTutorial(matchFormTutorial, {
    isEdition: props.isEdition,
})
const coachBoard = useTemplateRef('coach_board')
const isLoading = ref(true)
const urlExportFormat = ref(null)
const skills_controls = ref([])
const sidebarTitle = computed(() => props.isEdition ? 'Información del partido' : 'Nuevo partido')
const sidebarSubtitle = computed(() => (
    props.isEdition
        ? 'Actualiza la información general y el resultado final desde este panel.'
        : 'Completa la información general y organiza la alineación desde el coachboard.'
))
const submitLabel = computed(() => props.isEdition ? 'Guardar cambios' : 'Guardar')
// settings flatpick
const flatpickrConfigDate = {
    locale: Spanish,
    // minDate: dayjs().subtract(1, 'month').format('YYYY-M-D'),
    maxDate: dayjs().add(1, 'month').format('YYYY-M-D'),
}
const flatpickrConfigHour = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K"
}

const schema = yup.object().shape({
    id: yup.number().nullable(),
    tournament_id: yup.string().required(),
    competition_group_id: yup.string(),
    place: yup.string().required(),
    num_match: yup.number().integer().required().typeError('Debe ser un número.'),
    date: yup.date().required(),
    hour: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 9:30 AM o 12:00 PM)'
    ).required(),
    rival_name: yup.string().required(),
    final_score_school: yup.number().integer().default(0),
    final_score_rival: yup.number().integer().default(0),
    general_concept: yup.string().nullable(),
    skill_controls: yup.array().of(
        yup.object({
            id: yup.number().nullable(),
            assistance: yup.string().required('Es requerido'),
            titular: yup.mixed().required('Es requerido'),
            played_approx: yup.string().required('Es requerido'),
            position: yup.string().nullable().when('titular', {
                is: (titular) => Number(titular) === 1,
                then: (schema) => schema.required('Es requerido'),
                otherwise: (schema) => schema.nullable().default('')
            }),
            goals: yup.number().integer().required('Es requerido'),
            yellow_cards: yup.number().integer().required('Es requerido'),
            red_cards: yup.number().integer().required('Es requerido'),
            qualification: yup.number().integer().required('Es requerido'),
            general_concept: yup.string().nullable(),
            game_id: yup.number().nullable(),
            goal_assists: yup.number().integer().required('Es requerido'),
            goal_saves: yup.number().integer().required('Es requerido'),
        })
    )
})

const onLoadData = async () => {
    try {
        let url = ''
        let dataParams = {}
        if (props.isEdition) {
            url = `/api/v2/matches/${route.params.id}`
        } else {
            url = '/api/v2/matches/0'
            dataParams.competition_group = route.params.grupo_competencia
        }

        isLoading.value = true
        globalError.value = null


        const response = await api.get(url, { params: dataParams })
        if (response.status === 200 && response.data) {
            const match = response.data
            skills_controls.value = match.skills_controls

            urlExportFormat.value = match.competition_group.url_format_match
            // urlExportFormat.value = match.id ? null : match.competition_group.url_format_match
            formMatches.value.setValues({
                id: match.id,
                competition_group_id: match.competition_group.id,
                competition_group: match.competition_group.name,
                tournament_id: match.competition_group.tournament.id,
                professor: match.competition_group.professor.name,
                place: match.place,
                date: match.date,
                hour: match.hour,
                num_match: match.num_match,
                rival_name: match.rival_name,
                final_score_school: match.final_score.soccer,
                final_score_rival: match.final_score.rival,
                general_concept: match.general_concept,
                skill_controls: match.skills_controls
            })
        }
    } catch (error) {
        console.log(error)
        showMessage('Algo salió mal.', 'error')
    } finally {
        isLoading.value = false
    }
}

const mergeCoachBoardPayload = (skillControls, lineupPayload) => {
    const lineupByPlayerId = new Map(lineupPayload.map((item) => [item.player.id, item]))

    return skillControls.map((skillControl) => {
        const lineupItem = lineupByPlayerId.get(skillControl.player?.id)

        if (!lineupItem) {
            return {
                ...skillControl,
                titular: 0,
                position: ''
            }
        }

        return {
            ...skillControl,
            titular: lineupItem.titular,
            position: lineupItem.position
        }
    })
}

const handleSubmit = async (values, actions) => {
    try {
        isLoading.value = true
        globalError.value = null

        const mergedSkillControls = props.isEdition
            ? (values.skill_controls ?? skills_controls.value)
            : mergeCoachBoardPayload(
                values.skill_controls ?? skills_controls.value,
                coachBoard.value?.getSkillControlsPayload?.() || []
            )

        skills_controls.value = mergedSkillControls

        let data = {}
        let url = ''
        if (props.isEdition) {
            url = `/api/v2/matches/${route.params.id}`
            data = { _method: 'PUT', ...values, skill_controls: mergedSkillControls }
        } else {
            url = `/api/v2/matches`
            data = { ...values, skill_controls: mergedSkillControls }
        }

        const response = await api.post(url, data)

        if (response.data.success) {
            showMessage('Guardado correctamente.')
        }

    } catch (error) {
        showMessage('Algo salió mal.', 'error')
        proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
    } finally {
        isLoading.value = false
    }
}

const uploadFileFormat = async (e) => {

    if (!props.isEdition) {
        return
    }
    globalError.value = null
    const file = e.target.files[0];
    const formData = new FormData();
    formData.append('file', file, file.name)
    api.post(`/import/matches/${route.params.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(resp => {
            if (resp.data.success) {
                skills_controls.value = resp.data.skills_controls
                formMatches.value?.setFieldValue?.('skill_controls', resp.data.skills_controls)
                showMessage('Se cargaron los datos correctamente.')
            } else {
                showMessage('Algo salió mal.', 'error')
            }
        })
}

onMounted(() => {
    onLoadData()
    settingsGroup.getGroupSettings()
    currentTitlePage.value = props.isEdition ? `Competencia ${route.params.id}` : 'Crear Competencia';
    usePageTitle(currentTitlePage);
})

</script>
<style lang="scss" scoped>
.match-page-shell {
    min-height: 18rem;
}

.match-layout {
    align-items: flex-start;
}

.match-sidebar-card,
.match-players-card {
    border: 1px solid rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.08);
    border-radius: 1rem;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}

.match-sticky-card {
    position: sticky;
    top: 90px;
}

.match-card-body {
    padding: 1.25rem;
}

.match-sidebar-title {
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.match-sidebar-subtitle {
    margin-bottom: 0;
    max-width: 32ch;
    line-height: 1.45;
}

.match-form-block {
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.12);
    border-radius: 0.9rem;
}

.match-form-block:last-of-type {
    margin-bottom: 0;
}

.match-form-heading {
    margin-bottom: 1rem;
}

.match-form-title {
    margin-bottom: 0.2rem;
    font-size: 0.95rem;
    font-weight: 700;
}

.match-form-subtitle {
    margin-bottom: 0;

    font-size: 0.78rem;
    line-height: 1.45;
}

.match-form-block .form-group:last-child {
    margin-bottom: 0;
}

.match-form-block .form-label {
    margin-bottom: 0.35rem;
    font-size: 0.78rem;
    font-weight: 600;
}

.match-form-block :deep(.form-control),
.match-form-block :deep(.form-select),
.match-form-block :deep(.input-group-text) {
    border-color: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.12);
}

.match-form-block :deep(.input-group-text) {
    color: inherit;
}

.match-plaintext-field {
    padding: 0.55rem 0.75rem;
    border: 1px solid rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.12);
    border-radius: 0.75rem;
    color: inherit;
    line-height: 1.3;
}

.match-score-strip {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.match-score-side {
    flex: 1 1 0;
}

.match-score-divider {
    flex: 0 0 auto;
    padding-top: 2rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--bs-secondary-color, rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.7));
}

.match-table-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.match-table-title {
    margin-bottom: 0.2rem;
    font-size: 1rem;
    font-weight: 700;
}

.match-table-count {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.08);
    font-size: 0.78rem;
    font-weight: 700;
}

.match-table-wrapper {
    border: 1px solid rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.12);
    border-radius: 0.9rem;
    overflow: auto;
}

.match-table {
    min-width: 1120px;
    margin-bottom: 0;
}

.match-table thead th {
    border-top: 0;
    white-space: nowrap;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    background: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.05);
}

.match-table td {
    padding: 0.5rem 0.4rem;
    vertical-align: middle;
}

.match-player-cell {
    min-width: 240px;
}

.match-player-meta {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    min-width: 220px;
}

.match-player-avatar {
    width: 42px;
    height: 42px;
    border-radius: 0.75rem;
    object-fit: cover;
    flex-shrink: 0;
}

.match-player-name {
    display: block;
    font-weight: 600;
    line-height: 1.3;
}

.match-player-code {
    display: inline-flex;
    align-items: center;
    margin-top: 0.35rem;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    background: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.06);
    font-size: 0.72rem;
    line-height: 1.2;
}

.match-metric-cell {
    min-width: 82px;
}

.match-position-cell {
    min-width: 150px;
}

.match-observation-cell {
    min-width: 200px;
}

.match-observation-field {
    min-width: 190px;
    resize: vertical;
}

.match-table :deep(.form-control-sm),
.match-table :deep(.form-select-sm) {
    min-width: 72px;
}

.match-board-wrapper {
    padding-top: 0.25rem;
}

@media (max-width: 1199.98px) {
    .match-sticky-card {
        position: static;
    }
}

@media (max-width: 575.98px) {
    .match-card-body {
        padding: 1rem;
    }

    .match-score-strip {
        flex-wrap: wrap;
    }

    .match-score-divider {
        width: 100%;
        padding-top: 0;
        text-align: center;
    }
}
</style>
