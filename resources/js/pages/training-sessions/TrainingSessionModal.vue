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
                @submit="onSubmit"
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

                        <Wizard v-model="currentStep" :options="wizardOptions" @finish="handleSubmit(onSubmit)">
                            <template #info>
                                <h6 class="d-flex block-helper justify-content-center">
                                    Los campos con <span class="text-danger">&ensp;(*)&ensp;</span> son requeridos.
                                </h6>
                            </template>

                            <Step title="Información general">
                                <fieldset class="col-md-12 p-2">
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
                                                <inputField label="Hora" name="hour" :is-required="true" placeholder="02:00 PM" />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <inputField label="Lugar" name="training_ground" :is-required="true" />
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
                                <fieldset class="col-md-12 p-2">
                                    <Field :name="taskField(taskNumber - 1, 'task_number')" type="hidden" />

                                    <div class="row col-md-12">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'task_name')"
                                                    :label="`Ejercicio N° ${taskNumber}`"
                                                    :is-required="true"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'general_objective')"
                                                    label="Objetivo general"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'specific_goal')"
                                                    label="Objetivo específico"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_one')"
                                                    label="Contenido 1"
                                                />
                                            </div>
                                            <div class="form-group mt-2">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_two')"
                                                    label="Contenido 2"
                                                />
                                            </div>
                                            <div class="form-group mt-2">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'content_three')"
                                                    label="Contenido 3"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-md-12">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'ts')"
                                                    label="TS"
                                                    placeholder="9"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'sr')"
                                                    label="S/R"
                                                    placeholder="2 (1'D)"
                                                />
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <inputField
                                                    :name="taskField(taskNumber - 1, 'tt')"
                                                    label="TT"
                                                    placeholder="20"
                                                />
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
                                <fieldset class="col-md-12 p-2">
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
</template>

<script setup>
import { computed, getCurrentInstance, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
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

const taskSchema = yup.object({
    task_number: yup.number().required().integer().min(1).max(3),
    task_name: yup.string().required().max(10),
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
    hour: yup.string().required().max(20),
    training_ground: yup.string().required().max(100),
    material: yup.string().nullable(),
    warm_up: yup.string().nullable(),
    back_to_calm: numericString('Vuelta a la calma').max(10),
    players: numericString('N° Jugadores'),
    absences: yup.string().nullable(),
    incidents: yup.string().nullable(),
    feedback: yup.string().nullable(),
    tasks: yup.array().of(taskSchema).length(3),
})

const wizardOptions = {
    saveState: false,
    enableAllSteps: true,
    labels: {
        finish: 'Guardar',
        next: 'Siguiente',
        previous: 'Anterior',
    },
}

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
    if (!settings.groups.length) {
        await settings.getSettings()
    }
}

async function prepareModal() {
    globalError.value = null
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
                task_name: task.task_name,
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

function onCancel() {
    globalError.value = null
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
