<template>
    <div v-if="creationBlocked" class="layout-px-spacing">
        <div class="layout-top-spacing">
            <div class="alert alert-warning d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div>
                    <strong>No se puede crear la competencia.</strong>
                    <div class="mt-1">{{ creationBlockedMessage }}</div>
                </div>
                <button type="button" class="btn btn-outline-warning btn-sm" @click="router.push({ name: 'matches' })">
                    Volver
                </button>
            </div>
        </div>
    </div>

    <Form v-else ref="form_matches" :validation-schema="schema" :initial-values="{ date: null, hour: null, status: 'scheduled' }"
        @submit="handleSubmit" @invalid-submit="handleInvalidSubmit">

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

                                            <div v-if="isEdition" class="col-12">
                                                <div class="form-group">
                                                    <label for="status" class="form-label">Estado<span class="text-danger">&nbsp;(*)</span></label>
                                                    <Field name="status" as="select" id="status" class="form-select form-select-sm">
                                                        <option value="scheduled">Programado</option>
                                                        <option value="played">Jugado</option>
                                                    </Field>
                                                    <ErrorMessage name="status" class="invalid-feedback d-block" />
                                                    <small class="text-muted d-block mt-1">Solo los partidos jugados alimentan las estadísticas.</small>
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
                                                class="form-control form-control-sm match-general-concept" rows="7"
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
                                    <MatchPlayersStatsTable v-if="isEdition"
                                        :skills-controls="skills_controls"
                                        :position-options="positionOptions" />

                                    <template v-else>
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

    <PageTutorialOverlay v-if="!creationBlocked" :tutorial="tutorial" />
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
import MatchPlayersStatsTable from './components/MatchPlayersStatsTable.vue'

import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta"
import { usePageTutorial } from '@/composables/usePageTutorial'
import { ErrorMessage, Field, Form } from "vee-validate"
import * as yup from 'yup'
import { computed, getCurrentInstance, useTemplateRef, onMounted, ref } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useSetting, useSettingGroups } from '@/store/settings-store'
import { matchFormTutorial } from '@/tutorials/matches'
import {
    buildSkillControlLookup,
    findMatchingSkillControl
} from './utils/skillControls'

const props = defineProps({ isEdition: { type: Boolean, default: false } })

const { proxy } = getCurrentInstance()
const globalError = ref(null)
const route = useRoute()
const router = useRouter()
const settings = useSetting()
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
const creationBlocked = ref(false)
const creationBlockedMessage = ref('El grupo de competencia seleccionado no tiene integrantes.')
const originalStatus = ref('scheduled')
const sidebarTitle = computed(() => props.isEdition ? 'Información del partido' : 'Nuevo partido')
const sidebarSubtitle = computed(() => (
    props.isEdition
        ? 'Actualiza la información general y el resultado final desde este panel.'
        : 'Completa la información general y organiza la alineación desde el coachboard.'
))
const submitLabel = computed(() => props.isEdition ? 'Guardar cambios' : 'Guardar')
const positionOptions = computed(() => (settings.positions ?? []).map((position) => {
    if (position && typeof position === 'object') {
        const value = position.value ?? position.id ?? position.name ?? ''
        const label = position.label ?? position.name ?? value

        return {
            value: String(value),
            label: String(label),
        }
    }

    return {
        value: String(position),
        label: String(position),
    }
}))
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
    status: yup.string().oneOf(['scheduled', 'played']).required(),
    date: yup.date().required().test('played-date', 'Un partido jugado no puede tener una fecha futura.', function (value) {
        return this.parent.status !== 'played' || !value || !dayjs(value).isAfter(dayjs(), 'day')
    }),
    hour: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 9:30 AM o 12:00 PM)'
    ).required(),
    rival_name: yup.string().required(),
    final_score_school: yup.number().transform((value, original) => original === '' || original === null ? null : value).nullable().integer().min(0).when('status', {
        is: 'played',
        then: (valueSchema) => valueSchema.required('Es requerido'),
    }),
    final_score_rival: yup.number().transform((value, original) => original === '' || original === null ? null : value).nullable().integer().min(0).when('status', {
        is: 'played',
        then: (valueSchema) => valueSchema.required('Es requerido'),
    }),
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
            qualification: yup.number()
                .transform((value, original) => original === '' || original === null ? null : value)
                .nullable()
                .integer()
                .min(1, 'Debe estar entre 1 y 5')
                .max(5, 'Debe estar entre 1 y 5')
                .required('Es requerido'),
            observation: yup.string().nullable(),
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
            const matchSkillControls = match.skills_controls ?? []
            if (!props.isEdition && !matchSkillControls.length) {
                creationBlocked.value = true
                creationBlockedMessage.value = 'No se puede crear la competencia porque el grupo seleccionado no tiene integrantes.'
                skills_controls.value = []
                showMessage(creationBlockedMessage.value, 'error')
                return
            }

            creationBlocked.value = false
            skills_controls.value = matchSkillControls

            urlExportFormat.value = match.id
                ? `/export/matches/${match.id}/format`
                : match.competition_group.url_format_match
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
                status: match.status || 'scheduled',
                final_score_school: match.final_score?.soccer ?? null,
                final_score_rival: match.final_score?.rival ?? null,
                general_concept: match.general_concept,
                skill_controls: match.skills_controls
            })
            originalStatus.value = match.status || 'scheduled'
        }
    } catch {
        showMessage('Algo salió mal.', 'error')
    } finally {
        isLoading.value = false
    }
}

const mergeCoachBoardPayload = (skillControls, lineupPayload) => {
    const lineupLookup = buildSkillControlLookup(lineupPayload)

    return skillControls.map((skillControl) => {
        const lineupItem = findMatchingSkillControl(lineupLookup, skillControl)

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
        if (props.isEdition && originalStatus.value === 'played' && values.status === 'scheduled') {
            const confirmation = await Swal.fire({
                title: '¿Volver a Programado?',
                text: 'El partido dejará de aparecer en todas las estadísticas. Los datos diligenciados se conservarán como borrador.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, volver a Programado',
                cancelButtonText: 'Cancelar',
            })

            if (!confirmation.isConfirmed) {
                return
            }
        }

        isLoading.value = true
        globalError.value = null

        const baseSkillControls = values.skill_controls ?? skills_controls.value
        const shouldMergeCoachBoard = !props.isEdition || coachBoard.value?.hasLineupInteraction?.()
        const mergedSkillControls = shouldMergeCoachBoard
            ? mergeCoachBoardPayload(
                baseSkillControls,
                coachBoard.value?.getSkillControlsPayload?.() || []
            )
            : baseSkillControls

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
            originalStatus.value = values.status

            if (!props.isEdition && response.data.match_id) {
                await router.replace({
                    name: 'matches-edit',
                    params: { id: response.data.match_id }
                })
            }
        }

    } catch (error) {
        showMessage('Algo salió mal.', 'error')
        proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
    } finally {
        isLoading.value = false
    }
}

const handleInvalidSubmit = ({ errors }) => {
    const errorCount = Object.keys(errors ?? {}).length
    const message = errorCount === 1
        ? 'Revisa el campo marcado antes de guardar.'
        : `Revisa los ${errorCount} campos marcados antes de guardar.`

    globalError.value = message
    showMessage(message, 'error')
}

const normalizeImportedSkillControls = (importedSkillControls) => {
    const currentSkillControlsLookup = buildSkillControlLookup(skills_controls.value)

    return importedSkillControls.map((skillControl) => {
        const currentSkillControl = findMatchingSkillControl(currentSkillControlsLookup, skillControl) ?? {}
        const player = skillControl.player
            ?? skillControl.inscription?.player
            ?? currentSkillControl.player
            ?? currentSkillControl.inscription?.player
            ?? null

        return {
            ...currentSkillControl,
            ...skillControl,
            player,
            inscription_id: skillControl.inscription_id ?? skillControl.inscription?.id ?? currentSkillControl.inscription_id,
            game_id: Number(route.params.id),
        }
    })
}

const uploadFileFormat = async (e) => {

    if (!props.isEdition) {
        return
    }
    globalError.value = null
    const file = e.target.files[0];
    if (!file) {
        return
    }

    const formData = new FormData();
    formData.append('file', file, file.name)
    api.post(`/import/matches/${route.params.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(resp => {
            if (resp.data.success) {
                skills_controls.value = normalizeImportedSkillControls(resp.data.skills_controls ?? [])
                formMatches.value?.setFieldValue?.('skill_controls', skills_controls.value)
                showMessage('Se cargaron los datos correctamente.')
            } else {
                showMessage(resp.data.message || 'Algo salió mal.', 'error')
            }
        })
        .catch(error => {
            const message = error.response?.data?.message || 'Algo salió mal.'
            globalError.value = message
            showMessage(message, 'error')
        })
}

onMounted(() => {
    onLoadData()
    settings.getSettings()
    settingsGroup.getGroupSettings()
    currentTitlePage.value = props.isEdition ? `Competencia ${route.params.id}` : 'Crear Competencia';
    usePageTitle(currentTitlePage);
})

</script>
<style lang="scss" scoped>
.match-page-shell {
    --match-surface: var(--bs-body-bg, #fff);
    --match-surface-soft: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.045);
    --match-surface-strong: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.08);
    --match-border: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.12);
    --match-muted: var(--bs-secondary-color, rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.7));
    min-height: 18rem;
}

:global(body.dark .match-page-shell) {
    --match-surface: #101426;
    --match-surface-soft: rgba(255, 255, 255, 0.045);
    --match-surface-strong: rgba(255, 255, 255, 0.08);
    --match-border: rgba(255, 255, 255, 0.13);
    --match-muted: rgba(255, 255, 255, 0.72);
}

.match-layout {
    align-items: flex-start;
}

.match-sidebar-card,
.match-players-card {
    border: 1px solid var(--match-border);
    border-radius: 1rem;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}

:global(body.dark .match-sidebar-card),
:global(body.dark .match-players-card) {
    background: var(--match-surface);
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
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

.match-general-concept {
    min-height: 10rem;
    line-height: 1.5;
    resize: vertical;
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
