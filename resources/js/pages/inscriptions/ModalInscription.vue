<template>
    <div class="modal fade" id="composeModalInscription" tabindex="-1" role="dialog" aria-labelledby="modalInscription"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="initialData">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInscription">Inscripción</h5>
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                            class="btn-close" @click="onCancel"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row col-12 ">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="unique_code">Jugador</label><span class="text-danger">(*)</span>:
                                    <Field name="unique_code" v-slot="{ field }">
                                        <TypeAhead inputClass="form-control form-control-sm"
                                            dropdownClass="dropdown custom-dropdown-icon"
                                            dropdownMenuClass="dropdown-menu w-100" id="unique_code" :items="search"
                                            :itemProjection="item => item" :modelValue="field.value"
                                            @update:modelValue="field.onChange($event);onChangeCode($event)"/>
                                    </Field>

                                    <small class="form-text text-muted">Buscará deportistas sin inscripción</small>
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
                                    <Field name="start_date" v-slot="{ field }" id="start_date">
                                        <flat-pickr v-bind="field" v-model="field.value" :config="flatpickrConfigDate"
                                            class="form-control form-control-sm flatpickr" id="start_date" />
                                    </Field>
                                    <ErrorMessage name="start_date" class="custom-error" />

                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <small class="form-text text-muted mt-4">Al ser becado, todas las mensualidades del año
                                    se estableceran cómo: "<span class="text-warning">Becado</span>"</small>
                                <checkbox label="¿ Becado ?" name="scholarship" />
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="training_group_id">Grupo de entrenamiento:</label>
                                    <Field name="training_group_id" as="CustomSelect2" :options="trainingGroups" />
                                    <ErrorMessage name="training_group_id" class="custom-error" as="div" />
                                    <small class="form-text text-muted">Si no se selecciona, se agregará al grupo
                                        "Provisional"</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="competition_groups">Grupo de competencia:</label>
                                    <Field name="competition_groups" as="CustomSelect2" :options="competitionGroups"
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
                                <small class="text-info text-uppercase text-justify">Al estar marcado cómo
                                    <span class="text-uppercase text-warning">"PRE-INSCRIPCIÓN"</span>, este
                                    <span class="text-uppercase text-danger">Deportista no aparecerá en los listados de asistencias ni
                                        pagos</span>, verifica la documentación y desmarcalo.
                                </small>
                                <checkbox label="¿ PRE-INSCRIPCIÓN ?" name="pre_inscription" />
                            <h6 class="text-center text-uppercase">
                                <strong class="text-muted">
                                    Grupo provisional (principal).
                                </strong>
                            </h6>
                                <small class="text-info text-uppercase text-justify">
                                    <span>este grupo sólo se utilizará para las inscripciones realizadas
                                    desde el enlance.</span>
                                    <span class="text-danger"> no tendrá asistencias ni pagos, si se encuentra en el
                                    provisional y/o está marcado cómo pre-inscripción.</span>
                                </small>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" @click="onCancel">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
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
import { getCurrentInstance, useTemplateRef, ref, onMounted, watch, onUnmounted, onBeforeUnmount } from "vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import { useSetting } from "@/store/settings-store";

const props = defineProps({
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
const composeModalInscription = ref('null');
const trainingGroups = ref([])
const competitionGroups = ref([])

const initialData = ref({
    player_id: null,
    unique_code: null,
    player_name: null,
    start_date: null,
    scholarship: false,
    training_group_id: null,
    competition_groups: null,
    photos: false,
    copy_identification_document: false,
    eps_certificate: false,
    medic_certificate: false,
    study_certificate: false,
    pre_inscription: false,
});

const schema = yup.object().shape({
    player_id: yup.string().nullable(),
    unique_code: yup.string().required('Ingresa un código único'),
    player_name: yup.string().required('Ingresa un código único'),
    start_date: yup.date().required('La Fecha de inicio es requerida'),
    scholarship: yup.boolean().default(false),
    training_group_id: yup.string().required(),
    competition_groups: yup.array().of(yup.string()).nullable(),
    photos: yup.boolean().default(false),
    copy_identification_document: yup.boolean().default(false),
    eps_certificate: yup.boolean().default(false),
    medic_certificate: yup.boolean().default(false),
    study_certificate: yup.boolean().default(false),
    pre_inscription: yup.boolean().default(false),
})

const onCancel = () => {
    form.value.resetForm()
    modalHidden()
    composeModalInscription.value.hide()
    emit("cancel")
}



const search = async (query) => {
    if (!query) return;
    const response = await api.get('/api/v2/autocomplete/list_code_unique?trashed=true', { params: { query: query } })
    return response.data.data
}

const onLoadData = async (uniqueCode) => {


    if(props.unique_code !== null) {
        api.get(`/api/v2/inscriptions/${uniqueCode}/edit`).then((resp) => {
            const data = resp.data

            form.value.setValues({
                id: data.id,
                player_id: data.player_id,
                player_name: data.player.full_names,
                start_date: data.start_date,
                training_group_id: data.training_group_id,
                competition_groups: data.competition_groups,
                pre_inscription: data.pre_inscription,
                photos: data.photos == 1,
                copy_identification_document: data.copy_identification_document == 1,
                eps_certificate: data.eps_certificate == 1,
                medic_certificate: data.medic_certificate == 1,
                study_certificate: data.study_certificate == 1,
                pre_inscription: data.pre_inscription == 1,
            })
        })
    }else {

        api.get('/api/v2/autocomplete/search_unique_code?unique=true', { params: { unique_code: uniqueCode } })
        .then((response) => {
            const data = response.data.data
            if(data) {
                form.value.setValues({
                    player_id: data.id,
                    player_name: data.full_names,
                    start_date: dayjs().format('YYYY-M-D')
                })
            } else {
                showMessage("El Deportista ya tiene una inscripción.", 'warning')
            }
        })
        .catch(() =>showMessage("El Deportista ya tiene una inscripción ó no se encontró.", 'error'));
    }

    composeModalInscription.value.show()
}

const submit = async (values, actions) => {
    try {

        let response = null
        let data = {...values}
        if(props.unique_code !== null) {
            data. _method = 'PUT'
            response = await api.post(`/api/v2/inscriptions/${data.id}`, data)
        } else {

            response = await api.post(`/api/v2/inscriptions`, data)
        }

        if (response.data.success === true) {
            const message = props.unique_code ? 'Modifiado correctamente' : 'Guardado correctamente';
            showMessage(message);
        }
        if (response.data.success === false) {
            showMessage('Algo salió mal', 'error');
        }

    } catch (error) {
        console.log(error)
       proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg));
    } finally {
        emit("success")
        modalHidden()
        composeModalInscription.value.hide()
        form.value.resetForm();
    }
}

watch(
    () => props.unique_code,
    (newValue) => {
        if (newValue !== null) {
            form.value.setValues({
                unique_code: props.unique_code
            })
            onChangeCode(props.unique_code)
        }
    }
)

const onChangeCode = (uniqueCode) => {
    onLoadData(uniqueCode)
}

const listenerClickOutSide = (event) => {
    const modalElement = document.getElementById("composeModalInscription")
    if (event.target === modalElement) {
        showMessage("Guarda los cambios o Cancela.", 'warning')
    }
}

onMounted(() => {
    const modalElement = document.getElementById("composeModalInscription")
    composeModalInscription.value = new window.bootstrap.Modal(modalElement,
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    )

    modalElement.addEventListener('click', listenerClickOutSide)

    settings.getSettings()

    trainingGroups.value = settings.groups.map((i) => ({ value: i.id, label: i.name }))
    competitionGroups.value = settings.competition_groups.map((i) => ({ value: i.id, label: i.name }))
})

onBeforeUnmount(() => {
    const modalElement = document.getElementById("composeModalInscription")
    modalElement.removeEventListener('click', listenerClickOutSide)
})
</script>