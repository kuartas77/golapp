<template>
    <div class="modal fade" id="composeModalInscription" tabindex="-1" role="dialog" aria-labelledby="modalInscription"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="initialData" v-slot="{ isSubmitting }">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInscription">{{ isEditing ? 'Modificar inscripción' : 'Inscripción' }}</h5>
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                            class="btn-close" @click="onCancel"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>
                        <div class="row col-12 ">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="unique_code">Jugador</label><span class="text-danger">(*)</span>:
                                    <Field v-if="isEditing" name="unique_code" as="input" id="unique_code" readonly
                                        class="form-control form-control-sm" />
                                    <Field v-else name="unique_code" v-slot="{ field }">
                                        <TypeAhead inputClass="form-control form-control-sm"
                                            dropdownClass="dropdown custom-dropdown-icon"
                                            dropdownMenuClass="dropdown-menu w-100" id="unique_code" :items="search"
                                            :itemProjection="item => item" :modelValue="field.value"
                                            @update:modelValue="field.onChange($event);onChangeCode($event)"/>
                                    </Field>

                                    <small v-if="isEditing" class="form-text text-muted">El jugador no se puede modificar desde este formulario.</small>
                                    <small v-else class="form-text text-muted">Buscará deportistas sin inscripción</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="player_name">Jugador:</label>
                                    <Field name="player_name" as="input" id="player_name" readonly
                                        class="form-control-plaintext">
                                    </Field>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="start_date">Fecha de inicio</label><span class="text-danger">(*)</span>:
                                    <Field v-if="isEditing" name="start_date" as="input" id="start_date" readonly
                                        class="form-control form-control-sm" />
                                    <Field v-else name="start_date" v-slot="{ field }" id="start_date">
                                        <flat-pickr v-bind="field" v-model="field.value" :config="flatpickrConfigDate"
                                            class="form-control form-control-sm flatpickr" id="start_date" />
                                    </Field>
                                    <ErrorMessage name="start_date" class="custom-error" />
                                    <small v-if="isEditing" class="form-text text-muted">La fecha de inicio se conserva para evitar desajustes con pagos y asistencias.</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <small class="form-text text-muted mt-4">Al ser becado, todas las mensualidades del año
                                    se estableceran cómo: "<span class="text-warning">Becado</span>"</small>
                                <checkbox label="¿ Becado ?" name="scholarship" />
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <small class="form-text text-muted mt-4">
                                    Usa la tarifa especial configurada en la escuela para mensualidades de hermano.
                                </small>
                                <checkbox label="¿ Mensualidad hermano ?" name="brother_payment" />
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="training_group_id">Grupo de entrenamiento:</label>
                                    <Field name="training_group_id" v-slot="{ field, handleChange }">
                                        <CustomSelect2
                                            id="training_group_id"
                                            :options="trainingGroups"
                                            :modelValue="field.value"
                                            @update:modelValue="(value) => { handleChange(value); onTrainingGroupChange(value) }"
                                        />
                                    </Field>
                                    <ErrorMessage name="training_group_id" class="custom-error" as="div" />
                                    <small class="form-text text-muted">Si no se selecciona, se agregará al grupo
                                        "Provisional"</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="competition_groups">Grupo de competencia:</label>
                                    <Field name="competition_groups" as="CustomSelect2" :options="competitionGroups" id="competition_groups"
                                        :multiple="true" />
                                    <ErrorMessage name="competition_groups" class="custom-error" />
                                </div>
                            </div>
                        </div>
                        <div class="row col-12">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <h6 class="text-center text-uppercase text-muted"><strong>Documentos</strong></h6>
                                <div class="check col">
                                    <checkbox label="Fotos" name="photos" />
                                    <checkbox label="Fotocopia Doc. Identidad" name="copy_identification_document" />
                                    <checkbox label="Certificado de Salud" name="eps_certificate" />
                                    <checkbox label="Certificado Médico" name="medic_certificate" />
                                    <checkbox label="Fotocopia Doc. Acudiente" name="study_certificate" />
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <h6 class="text-center text-uppercase text-muted">
                                    <strong>Pre-Inscripción</strong>
                                </h6>
                                <div
                                    class="rounded border p-2 mb-2"
                                    :class="preInscriptionAutoReason ? 'bg-light border-warning' : 'bg-light border-secondary'"
                                >
                                    <small class="d-block text-uppercase fw-semibold mb-1">
                                        {{ preInscriptionStatusTitle }}
                                    </small>
                                    <small class="d-block">
                                        {{ preInscriptionStatusMessage }}
                                    </small>
                                </div>
                                <Field name="pre_inscription" v-slot="{ value, handleChange }">
                                    <div class="form-group mt-2">
                                        <div class="form-check ps-0">
                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                <input
                                                    id="pre_inscription"
                                                    type="checkbox"
                                                    class="custom-control-input"
                                                    :checked="Boolean(value)"
                                                    @change="(event) => onPreInscriptionInput(event, handleChange)"
                                                />
                                                <label class="custom-control-label" for="pre_inscription">
                                                    Marcar como preinscripción
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </Field>
                                <small class="form-text text-muted">
                                    Marca esta opción solo si la inscripción aún está pendiente de documentación.
                                </small>
                                <ErrorMessage name="pre_inscription" class="custom-error" as="div" />
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" :disabled="isSubmitting" @click="onCancel">Cerrar</button>
                        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
<script>
export default {
    name: 'modal_inscription'
}
</script>
<script setup>
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import dayjs from '@/utils/dayjs'
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component'
import TypeAhead from 'vue3-bootstrap-typeahead'
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, useTemplateRef, watch } from "vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import { useSetting } from "@/store/settings-store";

const props = defineProps({
    inscription_id: {
        type: [Number, String],
        default: null,
    },
    unique_code: {
        type: String,
        default: null,
    },
})
const emit = defineEmits(["success", "cancel"]);

// settings flatpick
const flatpickrConfigDate = {
    locale: Spanish,
    minDate: dayjs().subtract(1, 'year').format('YYYY-M-D'),
    maxDate: dayjs().format('YYYY-M-D'),
}

const { proxy } = getCurrentInstance();
const globalError = ref(null);
const settings = useSetting();
const form = useTemplateRef("form");
const composeModalInscription = ref(null);
const currentTrainingGroupId = ref(null);
const currentPreInscription = ref(false);
const editIdentifier = computed(() => props.inscription_id ?? props.unique_code);
const isEditing = computed(() => editIdentifier.value !== null);
const trainingGroups = computed(() => settings.groups.map((group) => ({ value: group.id, label: group.name })));
const competitionGroups = computed(() => settings.competition_groups.map((group) => ({
    value: String(group.id),
    label: group.full_name_group ?? group.full_name ?? group.name ?? String(group.id),
})));
const provisionalTrainingGroup = computed(() => settings.all_groups?.[0] ?? null);
const hasTrainingGroupSelected = computed(() => ![null, '', undefined].includes(currentTrainingGroupId.value));
const preInscriptionAutoReason = computed(() => {
    if (!hasTrainingGroupSelected.value) {
        return 'empty'
    }

    if (provisionalTrainingGroup.value && String(currentTrainingGroupId.value) === String(provisionalTrainingGroup.value.id)) {
        return 'provisional'
    }

    return null
});
const preInscriptionStatusTitle = computed(() => {
    if (preInscriptionAutoReason.value) {
        return 'Se guardará como preinscripción'
    }

    if (currentPreInscription.value) {
        return 'Preinscripción manual'
    }

    return 'Inscripción lista para activarse'
});
const preInscriptionStatusMessage = computed(() => {
    if (preInscriptionAutoReason.value === 'empty') {
        return `Si guardas sin grupo de entrenamiento, el sistema asignará "${provisionalTrainingGroup.value?.name ?? 'Provisional'}". Mientras esté en preinscripción o en ese grupo, este deportista no aparecerá en asistencias ni pagos.`
    }

    if (preInscriptionAutoReason.value === 'provisional') {
        return `El grupo seleccionado es "${provisionalTrainingGroup.value?.name ?? 'Provisional'}". Mientras permanezca en ese grupo o en preinscripción, este deportista no aparecerá en asistencias ni pagos.`
    }

    if (currentPreInscription.value) {
        return 'Usa este estado cuando la inscripción aún esté pendiente de validación documental. En este estado, el deportista no aparecerá en asistencias ni pagos.'
    }

    return 'Con grupo definitivo y documentación validada, el deportista podrá aparecer en asistencias y pagos.'
});

const defaultValues = () => ({
    id: null,
    player_id: null,
    unique_code: null,
    player_name: null,
    start_date: null,
    scholarship: false,
    brother_payment: false,
    training_group_id: null,
    competition_groups: [],
    photos: false,
    copy_identification_document: false,
    eps_certificate: false,
    medic_certificate: false,
    study_certificate: false,
    pre_inscription: false,
});

const initialData = defaultValues();

const schema = yup.object().shape({
    player_id: yup.string().nullable(),
    unique_code: yup.string().required('Ingresa un código único'),
    player_name: yup.string().required('Ingresa un código único'),
    start_date: yup.date().required('La Fecha de inicio es requerida'),
    scholarship: yup.boolean().default(false),
    brother_payment: yup.boolean().default(false),
    training_group_id: yup.mixed().nullable(),
    competition_groups: yup.array().default([]),
    photos: yup.boolean().default(false),
    copy_identification_document: yup.boolean().default(false),
    eps_certificate: yup.boolean().default(false),
    medic_certificate: yup.boolean().default(false),
    study_certificate: yup.boolean().default(false),
    pre_inscription: yup.boolean().default(false),
})

const resetFormState = () => {
    form.value?.resetForm({ values: defaultValues() })
    currentTrainingGroupId.value = null
    currentPreInscription.value = false
    globalError.value = null
}

const closeModal = () => {
    modalHidden()
    composeModalInscription.value?.hide()
}

const onCancel = () => {
    resetFormState()
    closeModal()
    emit("cancel")
}

const search = async (query) => {
    if (!query) return [];

    const response = await api.get('/api/v2/autocomplete/list_code_unique?trashed=true', { params: { query: query } })
    return response.data.data ?? []
}

const loadInscriptionForEdit = async (inscriptionId) => {
    try {
        const resp = await api.get(`/api/v2/inscriptions/${inscriptionId}/edit`)
        const data = resp.data

        resetFormState()
        currentTrainingGroupId.value = data.training_group_id
        currentPreInscription.value = Boolean(data.pre_inscription)
        form.value.setValues({
            ...defaultValues(),
            id: data.id,
            player_id: data.player_id,
            unique_code: data.unique_code,
            player_name: data.player.full_names,
            start_date: data.start_date,
            scholarship: Boolean(data.scholarship),
            brother_payment: Boolean(data.brother_payment),
            training_group_id: data.training_group_id,
            competition_groups: data.competition_groups ?? [],
            photos: Boolean(data.photos),
            copy_identification_document: Boolean(data.copy_identification_document),
            eps_certificate: Boolean(data.eps_certificate),
            medic_certificate: Boolean(data.medic_certificate),
            study_certificate: Boolean(data.study_certificate),
            pre_inscription: Boolean(data.pre_inscription),
        })

        composeModalInscription.value?.show()
    } catch (error) {
        showMessage("No se pudo cargar la inscripción.", 'error')
        emit("cancel")
    }
}

const loadPlayerByUniqueCode = async (uniqueCode) => {
    if (!uniqueCode) {
        return
    }

    try {
        const response = await api.get('/api/v2/autocomplete/search_unique_code?unique=true', { params: { unique_code: uniqueCode } })
        const data = response.data.data

        if (!data) {
            showMessage("El Deportista ya tiene una inscripción.", 'warning')
            return
        }

        form.value.setValues({
            player_id: data.id,
            player_name: data.full_names,
            start_date: dayjs().format('YYYY-M-D')
        })
    } catch (error) {
        showMessage("El Deportista ya tiene una inscripción ó no se encontró.", 'error')
    }
}

const submit = async (values, actions) => {
    try {
        globalError.value = null
        let response = null
        const data = { ...values }

        if (isEditing.value) {
            data._method = 'PUT'
            response = await api.post(`/api/v2/inscriptions/${data.id}`, data)
        } else {
            response = await api.post(`/api/v2/inscriptions`, data)
        }

        if (response.data.success !== true) {
            showMessage('Algo salió mal', 'error')
            return
        }

        const message = isEditing.value ? 'Modificado correctamente' : 'Guardado correctamente'
        showMessage(message)
        emit("success")
        closeModal()
        resetFormState()
    } catch (error) {
        proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
    }
}

watch(
    editIdentifier,
    async (newValue) => {
        if (newValue !== null) {
            await loadInscriptionForEdit(newValue)
        }
    }
)

const onChangeCode = (uniqueCode) => {
    loadPlayerByUniqueCode(uniqueCode)
}

const onTrainingGroupChange = (value) => {
    currentTrainingGroupId.value = value
}

const onPreInscriptionChange = (value) => {
    currentPreInscription.value = Boolean(value)
}

const onPreInscriptionInput = (event, handleChange) => {
    const checked = Boolean(event?.target?.checked)

    handleChange(checked)
    onPreInscriptionChange(checked)
}

const listenerClickOutSide = (event) => {
    const modalElement = document.getElementById("composeModalInscription")
    if (event.target === modalElement) {
        showMessage("Guarda los cambios o Cancela.", 'warning')
    }
}

onMounted(async () => {
    const modalElement = document.getElementById("composeModalInscription")
    composeModalInscription.value = new window.bootstrap.Modal(modalElement,
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    )

    modalElement.addEventListener('click', listenerClickOutSide)

    await settings.getSettings()
})

onBeforeUnmount(() => {
    const modalElement = document.getElementById("composeModalInscription")
    modalElement?.removeEventListener('click', listenerClickOutSide)
})
</script>
