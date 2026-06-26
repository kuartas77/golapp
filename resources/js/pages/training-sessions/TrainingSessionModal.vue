<template>
    <div
        id="trainingSessionsModal"
        ref="modalRef"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-xl mw-90 w-100" role="document">
            <Form
                ref="form"
                v-slot="{ errors, handleSubmit }"
                :validation-schema="schema"
                :initial-values="initialValues"
                :keep-values="true"
                @submit="onSubmit"
                @invalid-submit="onInvalidSubmit"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ modalTitle }}
                        </h5>
                        <button type="button" class="btn-close" aria-label="Close" @click="onCancel"></button>
                    </div>

                    <div class="modal-body position-relative">
                        <Loader
                            :is-loading="isLoading || isSubmitting"
                            :loading-text="isLoading ? 'Cargando sesión...' : 'Guardando sesión...'"
                        />

                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>

                        <div v-if="formErrorSummary.length" class="alert alert-danger" role="alert">
                            <div class="fw-semibold">Hay campos por corregir antes de guardar.</div>
                            <ul class="mb-0 mt-2 ps-3">
                                <li v-for="error in formErrorSummary" :key="`${error.field}_${error.message}`">
                                    <button
                                        v-if="error.stepIndex !== null"
                                        type="button"
                                        class="btn btn-link btn-sm alert-link p-0 align-baseline"
                                        @click="goToStep(error.stepIndex)"
                                    >
                                        Paso {{ error.stepIndex + 1 }} · {{ error.stepTitle }}
                                    </button>
                                    <span v-if="error.stepIndex !== null">:</span>
                                    <span> {{ error.label }}. {{ error.message }}</span>
                                </li>
                            </ul>
                        </div>

                        <Wizard v-model="currentStep" :options="wizardOptions" @finish="handleSubmit(null, onSubmit)">
                            <template #info>
                                <h6 class="d-flex block-helper justify-content-center">
                                    Los campos con <span class="text-danger">&ensp;(*)&ensp;</span> son requeridos.
                                </h6>
                            </template>

                            <Step title="Información general">
                                <fieldset class="col-md-12 p-2 border rounded h-100">
                                    <div class="row col-md-12">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="training_group_id" class="form-label">
                                                    Grupo de entrenamiento
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="training_group_id"
                                                    name="training_group_id"
                                                    as="CustomSelect2"
                                                    :options="groupOptions"
                                                />
                                                <ErrorMessage name="training_group_id" class="invalid-feedback d-block" />
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <inputField label="Periodo" name="period" :is-required="true" />
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <inputField label="Sesión" name="session" :is-required="true" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <inputField label="Fecha" name="date" type="date" :is-required="true" />
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="hour" class="form-label">
                                                    Hora
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                    <Field name="hour" v-slot="{ field, errorMessage, meta }">
                                                        <flat-pickr
                                                            v-bind="field"
                                                            id="hour"
                                                            v-model="field.value"
                                                            :config="flatpickrConfigHour"
                                                            class="form-control form-control-sm flatpickr"
                                                            :class="{ 'is-invalid': meta.touched && errorMessage }"
                                                            placeholder="02:00 PM"
                                                        />
                                                    </Field>
                                                </div>
                                                <ErrorMessage name="hour" class="invalid-feedback d-block" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField label="Lugar" name="training_ground" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="material" class="form-label">Materiales utilizados</label>
                                                <Field
                                                    id="material"
                                                    name="material"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors.material }"
                                                />
                                                <ErrorMessage name="material" class="invalid-feedback d-block" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="warm_up" class="form-label">Calentamiento</label>
                                                <Field
                                                    id="warm_up"
                                                    name="warm_up"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors.warm_up }"
                                                />
                                                <ErrorMessage name="warm_up" class="invalid-feedback d-block" />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </Step>

                            <Step v-for="taskNumber in 3" :key="taskNumber" :title="`Ejercicio ${taskNumber}`">
                                <fieldset class="col-md-12 p-2 border rounded h-100">
                                    <Field :name="taskField(taskNumber - 1, 'task_number')" type="hidden" />

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'task_name')"
                                                    :label="`Ejercicio N° ${taskNumber}`"
                                                    :is-required="taskNumber === 1"
                                                    list="training-session-task-list"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'general_objective')"
                                                    label="Objetivo general"
                                                    list="training-session-general-objective-list"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'specific_goal')"
                                                    label="Objetivo específico"
                                                    list="training-session-specific-goal-list"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_one')"
                                                    label="Desarrollo del ejercicio 1"
                                                    list="training-session-content-list"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_two')"
                                                    label="Desarrollo del ejercicio 2"
                                                    list="training-session-content-list"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_three')"
                                                    label="Desarrollo del ejercicio 3"
                                                    list="training-session-content-list"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'ts')"
                                                    label="T/S"
                                                    placeholder="9"
                                                />
                                                <div class="form-text">Tiempo por serie</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'sr')"
                                                    label="S/R"
                                                    placeholder="2 (1'D)"
                                                />
                                                <div class="form-text">Series / Repeticiones</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'tt')"
                                                    label="T/T"
                                                    placeholder="20"
                                                />
                                                <div class="form-text">Tiempo total</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label :for="`observations_${taskNumber}`" class="form-label">Observaciones</label>
                                                <Field
                                                    :id="`observations_${taskNumber}`"
                                                    :name="taskField(taskNumber - 1, 'observations')"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors[taskField(taskNumber - 1, 'observations')] }"
                                                />
                                                <ErrorMessage
                                                    :name="taskField(taskNumber - 1, 'observations')"
                                                    class="invalid-feedback d-block"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </Step>

                            <Step title="Cierre">
                                <fieldset class="col-md-12 p-2 border rounded h-100">
                                    <div class="row col-md-12">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField label="Vuelta a la calma" name="back_to_calm" type="number" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField label="N° Jugadores" name="players" type="number" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="absences" class="form-label">Ausencias</label>
                                                <Field
                                                    id="absences"
                                                    name="absences"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors.absences }"
                                                />
                                                <ErrorMessage name="absences" class="invalid-feedback d-block" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="incidents" class="form-label">Incidencias</label>
                                                <Field
                                                    id="incidents"
                                                    name="incidents"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors.incidents }"
                                                />
                                                <ErrorMessage name="incidents" class="invalid-feedback d-block" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="feedback" class="form-label">Retroalimentación</label>
                                                <Field
                                                    id="feedback"
                                                    name="feedback"
                                                    as="textarea"
                                                    rows="4"
                                                    class="form-control form-control-sm"
                                                    :class="{ 'is-invalid': errors.feedback }"
                                                />
                                                <ErrorMessage name="feedback" class="invalid-feedback d-block" />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </Step>
                        </Wizard>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" @click="onCancel">
                            Cerrar
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>

    <datalist id="training-session-task-list">
        <option v-for="option in settings.training_session_tasks" :key="`task-${option.value}`" :value="option.label" />
    </datalist>

    <datalist id="training-session-general-objective-list">
        <option
            v-for="option in settings.training_session_general_objectives"
            :key="`general-objective-${option.value}`"
            :value="option.label"
        />
    </datalist>

    <datalist id="training-session-specific-goal-list">
        <option
            v-for="option in settings.training_session_specific_goals"
            :key="`specific-goal-${option.value}`"
            :value="option.label"
        />
    </datalist>

    <datalist id="training-session-content-list">
        <option v-for="option in settings.training_session_contents" :key="`content-${option.value}`" :value="option.label" />
    </datalist>
</template>

<script setup>
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import { computed, getCurrentInstance, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
import flatPickr from 'vue-flatpickr-component'
import * as yup from 'yup'
import Loader from '@/components/general/Loader.vue'
import Step from '@/plugins/wizard/Step.vue'
import Wizard from '@/plugins/wizard/Wizard.vue'
import api from '@/utils/axios'
import { useSetting } from '@/store/settings-store'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    sessionId: {
        type: [Number, String],
        default: null,
    },
})

const emit = defineEmits(['updated', 'cancel'])

const { proxy } = getCurrentInstance()
const form = useTemplateRef('form')
const modalRef = ref(null)
const modalInstance = ref(null)
const settings = useSetting()

const isLoading = ref(false)
const isSubmitting = ref(false)
const globalError = ref(null)
const formErrorSummary = ref([])
const currentStep = ref(0)
const initialValues = ref(createInitialValues())

const numericString = (label) =>
    yup
        .string()
        .nullable()
        .transform((value) => (value === '' ? null : value))
        .matches(/^\d+$/, {
            message: `${label} debe ser numérico.`,
            excludeEmptyString: true,
        })

const flatpickrConfigHour = {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'h:i K',
}

const taskSchema = yup.object({
    task_number: yup.number().required().integer().min(1).max(3),
    task_name: yup
        .string()
        .nullable()
        .max(100)
        .test('first-task-name-required', 'El ejercicio 1 es obligatorio.', function (value) {
            if (Number(this.parent?.task_number) !== 1) {
                return true
            }

            return Boolean(String(value ?? '').trim())
        }),
    general_objective: yup.string().nullable().max(50),
    specific_goal: yup.string().nullable().max(50),
    content_one: yup.string().nullable().max(50),
    content_two: yup.string().nullable().max(50),
    content_three: yup.string().nullable().max(50),
    ts: yup.string().nullable().max(10),
    sr: yup.string().nullable().max(10),
    tt: yup.string().nullable().max(10),
    observations: yup.string().nullable(),
})

const schema = yup.object({
    training_group_id: yup.number().typeError('Selecciona un grupo').required('Selecciona un grupo'),
    period: yup.string().required().max(100),
    session: yup.string().required().max(100),
    date: yup.string().required(),
    hour: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 9:30 AM o 12:00 PM)'
    ).required(),
    training_ground: yup.string().nullable().max(100),
    material: yup.string().nullable(),
    warm_up: yup.string().nullable(),
    back_to_calm: numericString('Vuelta a la calma').max(10),
    players: numericString('N° Jugadores'),
    absences: yup.string().nullable(),
    incidents: yup.string().nullable(),
    feedback: yup.string().nullable(),
    tasks: yup
        .array()
        .of(taskSchema)
        .length(3)
        .test('first-task-name-required', 'El ejercicio 1 es obligatorio.', function (tasks) {
            if (String(tasks?.[0]?.task_name ?? '').trim()) {
                return true
            }

            return this.createError({
                path: 'tasks[0].task_name',
                message: 'El ejercicio 1 es obligatorio.',
            })
        }),
})

const wizardOptions = computed(() => ({
    saveState: false,
    enableAllSteps: true,
    labels: {
        finish: 'Guardar',
        next: 'Siguiente',
        previous: 'Anterior',
    },
    onStepChanging: validateStepChange,
    onFinishing: async () => validateStepsUpTo(currentStep.value),
}))

const fieldMeta = {
    training_group_id: { label: 'Grupo de entrenamiento', stepIndex: 0, stepTitle: 'Información general' },
    period: { label: 'Periodo', stepIndex: 0, stepTitle: 'Información general' },
    session: { label: 'Sesión', stepIndex: 0, stepTitle: 'Información general' },
    date: { label: 'Fecha', stepIndex: 0, stepTitle: 'Información general' },
    hour: { label: 'Hora', stepIndex: 0, stepTitle: 'Información general' },
    training_ground: { label: 'Lugar', stepIndex: 0, stepTitle: 'Información general' },
    material: { label: 'Materiales utilizados', stepIndex: 0, stepTitle: 'Información general' },
    warm_up: { label: 'Calentamiento', stepIndex: 0, stepTitle: 'Información general' },
    'tasks[0].task_name': { label: 'Ejercicio 1', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].general_objective': { label: 'Objetivo general', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].specific_goal': { label: 'Objetivo específico', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].content_one': { label: 'Desarrollo del ejercicio 1', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].content_two': { label: 'Desarrollo del ejercicio 2', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].content_three': { label: 'Desarrollo del ejercicio 3', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].ts': { label: 'T/S', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].sr': { label: 'S/R', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[0].tt': { label: 'T/T', stepIndex: 1, stepTitle: 'Ejercicio 1' },
    'tasks[1].task_name': { label: 'Ejercicio 2', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].general_objective': { label: 'Objetivo general', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].specific_goal': { label: 'Objetivo específico', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].content_one': { label: 'Desarrollo del ejercicio 1', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].content_two': { label: 'Desarrollo del ejercicio 2', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].content_three': { label: 'Desarrollo del ejercicio 3', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].ts': { label: 'T/S', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].sr': { label: 'S/R', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[1].tt': { label: 'T/T', stepIndex: 2, stepTitle: 'Ejercicio 2' },
    'tasks[2].task_name': { label: 'Ejercicio 3', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].general_objective': { label: 'Objetivo general', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].specific_goal': { label: 'Objetivo específico', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].content_one': { label: 'Desarrollo del ejercicio 1', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].content_two': { label: 'Desarrollo del ejercicio 2', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].content_three': { label: 'Desarrollo del ejercicio 3', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].ts': { label: 'T/S', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].sr': { label: 'S/R', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    'tasks[2].tt': { label: 'T/T', stepIndex: 3, stepTitle: 'Ejercicio 3' },
    back_to_calm: { label: 'Vuelta a la calma', stepIndex: 4, stepTitle: 'Cierre' },
    players: { label: 'N° Jugadores', stepIndex: 4, stepTitle: 'Cierre' },
    absences: { label: 'Ausencias', stepIndex: 4, stepTitle: 'Cierre' },
    incidents: { label: 'Incidencias', stepIndex: 4, stepTitle: 'Cierre' },
    feedback: { label: 'Retroalimentación', stepIndex: 4, stepTitle: 'Cierre' },
}

const stepFields = [
    ['training_group_id', 'period', 'session', 'date', 'hour', 'training_ground', 'material', 'warm_up'],
    [
        'tasks[0].task_name',
        'tasks[0].general_objective',
        'tasks[0].specific_goal',
        'tasks[0].content_one',
        'tasks[0].content_two',
        'tasks[0].content_three',
        'tasks[0].ts',
        'tasks[0].sr',
        'tasks[0].tt',
        'tasks[0].observations',
    ],
    [
        'tasks[1].task_name',
        'tasks[1].general_objective',
        'tasks[1].specific_goal',
        'tasks[1].content_one',
        'tasks[1].content_two',
        'tasks[1].content_three',
        'tasks[1].ts',
        'tasks[1].sr',
        'tasks[1].tt',
        'tasks[1].observations',
    ],
    [
        'tasks[2].task_name',
        'tasks[2].general_objective',
        'tasks[2].specific_goal',
        'tasks[2].content_one',
        'tasks[2].content_two',
        'tasks[2].content_three',
        'tasks[2].ts',
        'tasks[2].sr',
        'tasks[2].tt',
        'tasks[2].observations',
    ],
    ['back_to_calm', 'players', 'absences', 'incidents', 'feedback'],
]

const modalTitle = computed(() =>
    props.sessionId ? `Actualizar sesión de entrenamiento #${props.sessionId}` : 'Crear sesión de entrenamiento'
)

const groupOptions = computed(() =>
    settings.groups.map((group) => ({
        value: group.id,
        label: group.full_schedule_group ?? group.full_group ?? group.name,
    }))
)

function blankTask(taskNumber) {
    return {
        task_number: taskNumber,
        task_name: '',
        general_objective: '',
        specific_goal: '',
        content_one: '',
        content_two: '',
        content_three: '',
        ts: '',
        sr: '',
        tt: '',
        observations: '',
    }
}

function createInitialValues() {
    return {
        training_group_id: null,
        period: '',
        session: '',
        date: todayDateString(),
        hour: '',
        training_ground: '',
        material: '',
        warm_up: '',
        back_to_calm: '',
        players: '',
        absences: '',
        incidents: '',
        feedback: '',
        tasks: [blankTask(1), blankTask(2), blankTask(3)],
    }
}

function taskField(index, field) {
    return `tasks[${index}].${field}`
}

function todayDateString() {
    const now = new Date()
    const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000)

    return localDate.toISOString().slice(0, 10)
}

function normalizeValue(value) {
    return value === null || value === undefined ? null : String(value).trim() || null
}

function normalizeErrorKey(key) {
    return String(key).replace(/\.(\d+)(?=\.|$)/g, '[$1]')
}

function prettifyField(field) {
    return String(field)
        .replace(/\[(\d+)\]/g, ' $1 ')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
}

function buildErrorSummary(errors = {}) {
    return Object.entries(errors)
        .filter(([, message]) => Boolean(message))
        .map(([field, message]) => {
            const normalizedField = normalizeErrorKey(field)
            const meta = fieldMeta[normalizedField]

            return {
                field: normalizedField,
                label: meta?.label || prettifyField(normalizedField),
                message,
                stepIndex: typeof meta?.stepIndex === 'number' ? meta.stepIndex : null,
                stepTitle: meta?.stepTitle || null,
            }
        })
        .sort((left, right) => {
            const leftStep = left.stepIndex ?? Number.MAX_SAFE_INTEGER
            const rightStep = right.stepIndex ?? Number.MAX_SAFE_INTEGER

            return leftStep - rightStep
        })
}

function moveToFirstErrorStep(summary = []) {
    const firstError = summary.find((item) => item.stepIndex !== null)

    if (firstError) {
        currentStep.value = firstError.stepIndex
    }
}

function registerFormErrors(errors = {}, message = 'Revisa los campos obligatorios antes de guardar.') {
    const summary = buildErrorSummary(errors)

    formErrorSummary.value = summary
    globalError.value = message
    moveToFirstErrorStep(summary)
}

async function validateStep(index) {
    const fields = stepFields[index] || []

    if (!fields.length) {
        return true
    }

    await nextTick()

    const validationResults = await Promise.all(
        fields.map(async (field) => ({
            field,
            result: await form.value?.validateField(field),
        }))
    )

    const errors = validationResults.reduce((accumulator, { field, result }) => {
        if (result?.valid === false && result.errors?.[0]) {
            accumulator[field] = result.errors[0]
        }

        return accumulator
    }, {})

    if (Object.keys(errors).length) {
        registerFormErrors(errors)
        currentStep.value = index

        return false
    }

    globalError.value = null
    formErrorSummary.value = []

    return true
}

async function validateStepsUpTo(index) {
    for (let stepIndex = 0; stepIndex <= index; stepIndex += 1) {
        const isValid = await validateStep(stepIndex)

        if (!isValid) {
            return false
        }
    }

    return true
}

async function validateStepChange(currentIndex, nextIndex) {
    if (nextIndex <= currentIndex) {
        return true
    }

    for (let stepIndex = currentIndex; stepIndex < nextIndex; stepIndex += 1) {
        const isValid = await validateStep(stepIndex)

        if (!isValid) {
            return false
        }
    }

    return true
}

function normalizeBackendErrors(errors = {}) {
    return Object.entries(errors).reduce((accumulator, [field, message]) => {
        accumulator[normalizeErrorKey(field)] = Array.isArray(message) ? message[0] : message

        return accumulator
    }, {})
}

function mapResponseToForm(data) {
    return {
        training_group_id: data.training_group_id,
        period: data.period ?? '',
        session: data.session ?? '',
        date: data.date ?? '',
        hour: data.hour ?? '',
        training_ground: data.training_ground ?? '',
        material: data.material ?? '',
        warm_up: data.warm_up ?? '',
        back_to_calm: data.back_to_calm ?? '',
        players: data.players ?? '',
        absences: data.absences ?? '',
        incidents: data.incidents ?? '',
        feedback: data.feedback ?? '',
        tasks: Array.from({ length: 3 }, (_, index) => ({
            ...blankTask(index + 1),
            ...(data.tasks?.[index] ?? {}),
        })),
    }
}

async function ensureSettingsLoaded() {
    if (!settings.groups.length || !settings.training_session_tasks.length) {
        await settings.getSettings()
    }
}

async function prepareModal() {
    globalError.value = null
    formErrorSummary.value = []
    currentStep.value = 0

    await ensureSettingsLoaded()
    await nextTick()

    const baseValues = createInitialValues()
    initialValues.value = baseValues
    form.value?.resetForm()
    form.value?.setValues(baseValues)

    if (props.sessionId) {
        isLoading.value = true

        try {
            const response = await api.get(`/api/v2/training-sessions/${props.sessionId}`)
            const values = mapResponseToForm(response.data.data)

            form.value?.resetForm()
            form.value?.setValues(values)
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar la sesión.'
        } finally {
            isLoading.value = false
        }
    }

    modalInstance.value?.show()
}

async function onSubmit(values, actions) {
    isSubmitting.value = true
    globalError.value = null
    formErrorSummary.value = []

    try {
        const payload = {
            training_group_id: Number(values.training_group_id),
            period: values.period,
            session: values.session,
            date: values.date,
            hour: values.hour,
            training_ground: values.training_ground,
            material: normalizeValue(values.material),
            warm_up: normalizeValue(values.warm_up),
            back_to_calm: normalizeValue(values.back_to_calm),
            players: normalizeValue(values.players),
            absences: normalizeValue(values.absences),
            incidents: normalizeValue(values.incidents),
            feedback: normalizeValue(values.feedback),
            tasks: values.tasks.map((task, index) => ({
                task_number: index + 1,
                task_name: normalizeValue(task.task_name),
                general_objective: normalizeValue(task.general_objective),
                specific_goal: normalizeValue(task.specific_goal),
                content_one: normalizeValue(task.content_one),
                content_two: normalizeValue(task.content_two),
                content_three: normalizeValue(task.content_three),
                ts: normalizeValue(task.ts),
                sr: normalizeValue(task.sr),
                tt: normalizeValue(task.tt),
                observations: normalizeValue(task.observations),
            })),
        }

        if (props.sessionId) {
            await api.put(`/api/v2/training-sessions/${props.sessionId}`, payload)
            showMessage('Sesión actualizada correctamente')
        } else {
            await api.post('/api/v2/training-sessions', payload)
            showMessage('Sesión creada correctamente')
        }

        modalHidden()
        modalInstance.value?.hide()
        emit('updated')
        actions.resetForm({ values: createInitialValues() })
    } catch (error) {
        if (error.response?.status === 422) {
            const formattedErrors = normalizeBackendErrors(error.response.data?.errors || {})
            actions.setErrors(formattedErrors)
            registerFormErrors(
                formattedErrors,
                error.response.data?.message || 'Encontramos errores al guardar. Revisa los campos indicados.'
            )
            return
        }

        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (message) => {
                globalError.value = message
            }
        )
    } finally {
        isSubmitting.value = false
    }
}

function onInvalidSubmit({ errors }) {
    registerFormErrors(errors)
}

function goToStep(stepIndex) {
    currentStep.value = stepIndex
}

function onCancel() {
    globalError.value = null
    formErrorSummary.value = []
    currentStep.value = 0
    modalHidden()
    modalInstance.value?.hide()
    emit('cancel')
}

watch(
    () => [props.show, props.sessionId],
    async ([show]) => {
        if (!modalInstance.value) {
            return
        }

        if (show) {
            await prepareModal()
            return
        }

        modalInstance.value.hide()
    }
)

onMounted(() => {
    modalInstance.value = new window.bootstrap.Modal(modalRef.value, {
        backdrop: 'static',
        keyboard: false,
        focus: false,
    })
})
</script>
