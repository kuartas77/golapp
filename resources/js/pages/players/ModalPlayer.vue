<template>
    <div class="modal fade" id="composeModalPlayer" tabindex="-1" role="dialog" aria-labelledby="modalPlayer"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-xl mw-80 w-100" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPlayer">Deportista</h5>
                    <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                        class="btn-close" @click="onCancel"></button>
                </div>
                <div class="modal-body">
                    <div class="col-lg-12">
                        <form-wizard id="wizard-player" title="Deportista" subtitle="" step-size="md" class="circle"
                            color="#4361ee" ref="wizard" :start-index="0" @on-validate="handleValidation"
                            @on-complete="onComplete" nextButtonText="Siguiente" backButtonText="Atras"
                            finishButtonText="Guardar" :disableBackOnClickStep="true">

                            <tab-content title="Información personal" icon="far fa-user" :before-change="validateStep1">
                                <Form :validation-schema="schemas.step1" :initial-values="step1Initial" ref="step1Form">
                                    <fieldset class="col-md-12 p-3">
                                        <legend>Personal</legend>
                                        <div class="row col-md-12">
                                            <div class="col-md-3 text-center">
                                                <div class="form-group">
                                                    <inputFileImage label="Foto JPG, JPEG, PNG" name="photo" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <inputField label="Código único" name="unique_code"
                                                        :is-required="true" readonly="true" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <inputField label="# Documento de identidad"
                                                        name="identification_document" :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_birth" class="form-label">Fecha de
                                                        nacimiento</label>
                                                    <Field name="date_birth" v-slot="{ field }">
                                                        <flat-pickr v-bind="field" v-model="field.value"
                                                            :config="flatpickrConfig"
                                                            class="form-control form-control-sm flatpickr"
                                                            id="date_birth"></flat-pickr>
                                                    </Field>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <inputField label="Nombres" name="names" :is-required="true" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <inputField label="Tipo Documento" name="document_type"
                                                        :is-required="true" />
                                                </div>

                                                <div class="form-group">
                                                    <inputField label="Lugar de nacimiento" name="place_birth"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-2">
                                                    <inputField label="Apellidos" name="last_names"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group mb-2">
                                                    <inputField label="Genero" name="gender" :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="Grupo sanguíneo" name="rh" :is-required="true" />
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </Form>
                            </tab-content>

                            <tab-content title="Información general" icon="far fa-list-alt"
                                :before-change="validateStep2">
                                <Form :validation-schema="schemas.step2" :initial-values="step2Initial" ref="step2Form">
                                    <fieldset class="col-md-12 p-3">
                                        <legend>General</legend>
                                        <div class="row col-md-12">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Correo Electrónico" name="email"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="Entidad prestadora de salud" name="eps"
                                                        :is-required="true" />
                                                </div>

                                                <div class="form-group">
                                                    <inputField label="Direccion de residencia" name="address"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Municipio de residencia" name="municipality"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="Barrio de residencia" name="neighborhood"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="# Teléfonicos/Celular'" name="phones"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Institución educativa" name="school"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="Grado que cursa" name="degree"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <inputField label="Jornada de estudio" name="jornada"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Seguro Estudiantil" name="student_insurance"
                                                        :is-required="true" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="medical_history" class="form-label">Antecedentes
                                                        Médicos</label>
                                                    <Field id="medical_history" name="medical_history" as="textarea"
                                                        class="form-control" rows="5">
                                                    </Field>
                                                    <ErrorMessage name="medical_history" class="custom-error" />
                                                </div>

                                            </div>

                                        </div>
                                    </fieldset>
                                </Form>
                            </tab-content>

                            <tab-content title="Información familiar" icon="far fa-user" :before-change="validateStep3">
                                <Form :validation-schema="schemas.step3" :initial-values="step3Initial" ref="step3Form">
                                    <fieldset class="col-md-12 p-3">
                                        <legend>Tutor</legend>
                                        <div class="row col-md-12">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Nombres completos" name="tutor_names"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="# Doc de identidad" name="tutor_document"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="# WhatsApp ó télefono" name="tutor_phone"
                                                        :is-required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <inputField label="Ocupación" name="tutor_business"
                                                        :is-required="true" />
                                                </div>
                                            </div>

                                        </div>
                                    </fieldset>
                                </Form>
                            </tab-content>


                        </form-wizard>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
export default {
    name: 'modal_player'
}
</script>
<script setup>
import dayjs from '@/utils/dayjs';
import { useAppState } from '@/store/app-state'
import { FormWizard, TabContent } from 'vue3-form-wizard'
import { getCurrentInstance, useTemplateRef, ref, onMounted, watch } from "vue";
import { ErrorMessage, Form, Field } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import flatPickr from 'vue-flatpickr-component';
import { Spanish } from "flatpickr/dist/l10n/es.js"

import 'vue3-form-wizard/dist/style.css'
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";

const appState = useAppState()
const props = defineProps({
    unique_code: {
        type: String,
        default: null,
    },
});
const emit = defineEmits(["update", "cancel"]);
const wizard = useTemplateRef("wizard");
const step1Form = useTemplateRef('step1Form');
const step2Form = useTemplateRef('step2Form');
const step3Form = useTemplateRef('step3Form');
const composeModalPlayer = ref(null);

const flatpickrConfig = {
    locale: Spanish,
    // time: null,
    // dateFormat: 'H:i',
    // wrap: true,
    minDate: dayjs().subtract(20, 'year').format('YYYY-M-D'),
    maxDate: dayjs().subtract(1, 'year').endOf('year').format('YYYY-M-D')
    // enableTime: false,
    // enableSeconds: false,
    // noCalendar: true,
    // time_24hr: true,
}

const step1Initial = ref({
    photo: null,
    unique_code: null,
    identification_document: null,
    document_type: null,
    date_birth: null,
    names: null,
    last_names: null,
    place_birth: null,
    gender: null,
    rh: null,
})

const step2Initial = ref({
    email: null,
    eps: null,
    address: null,
    municipality: null,
    neighborhood: null,
    phones: null,
    school: null,
    degree: null,
    jornada: null,
    student_insurance: null,
    medical_history: null,
})

const step3Initial = ref({
    tutor_names: null,
    tutor_document: null,
    tutor_phone: null,
    tutor_business: null,
})

const schemas = {
    step1: yup.object({
        photo: yup.mixed().nullable(),
        unique_code: yup.string().trim().required(),
        identification_document: yup.string().trim().required(),
        document_type: yup.string().trim().required(),
        date_birth: yup.string().trim().required(),
        names: yup.string().trim().required(),
        last_names: yup.string().trim().required(),
        place_birth: yup.string().trim().required(),
        gender: yup.string().trim().required(),
        rh: yup.string().trim().required(),
    }),
    step2: yup.object({
        email: yup.string().required(),
        eps: yup.string().required(),
        address: yup.string().required(),
        municipality: yup.string().required(),
        neighborhood: yup.string().required(),
        phones: yup.string().required(),
        school: yup.string().required(),
        degree: yup.string().required(),
        jornada: yup.string().required(),
        student_insurance: yup.string().required(),
        medical_history: yup.string().required()
    }),
    step3: yup.object({
        tutor_names: yup.string().required(),
        tutor_document: yup.string().required(),
        tutor_phone: yup.string().required(),
        tutor_business: yup.string().required()
    })
}



const handleValidation = (isValid, tabIndex) => {
    console.log("Tab: " + tabIndex + " valid: " + isValid);
};

const validateStep1 = async () => {
    const { valid } = await step1Form.value.validate();
    return valid;
}
const validateStep2 = async () => {
    const { valid } = await step2Form.value.validate();
    return valid;
}
const validateStep3 = async () => {
    const { valid } = await step3Form.value.validate();
    return valid;
}

const onComplete = () => {

    console.log(step1Form.value.values, step2Form.value.values, step3Form.value.values)


    // resetForm()
    // wizard.value.reset()
    // modalHidden();
    // composeModalPlayer.value.hide();
    emit("update");
}

const onLoadData = async () => {

    try {

        const response = await api.get(`/api/v2/players/${props.unique_code}/edit`)

        step1Form.value.setValues({
            photo: response.data.photo_url,
            unique_code: response.data.unique_code,
            identification_document: response.data.identification_document,
            document_type: response.data.document_type,
            date_birth: response.data.date_birth,
            names: response.data.names,
            last_names: response.data.last_names,
            place_birth: response.data.place_birth,
            gender: response.data.gender,
            rh: response.data.rh,
        })

        step2Form.value.setValues({
            email: response.data.email,
            eps: response.data.eps,
            address: response.data.address,
            municipality: response.data.municipality,
            neighborhood: response.data.neighborhood,
            phones: response.data.phones,
            school: response.data.school,
            degree: response.data.degree,
            jornada: response.data.jornada,
            student_insurance: response.data.student_insurance,
            medical_history: response.data.medical_history,
        })

        composeModalPlayer.value.show()

    } catch (error) {

    }

}

const resetForm = () => {
    step1Form.value.resetForm()
    step2Form.value.resetForm()
    step3Form.value.resetForm()
}


const onCancel = async () => {
    resetForm()
    wizard.value.reset()
    modalHidden();
    composeModalPlayer.value.hide();
    emit("cancel");
};

watch(() => props.unique_code, (newValue) => {
    if (newValue !== null) {
        onLoadData();
    }
});

onMounted(() => {
    composeModalPlayer.value = new window.bootstrap.Modal(
        document.getElementById("composeModalPlayer"),
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    )

    if (props.unique_code) {
        onLoadData()
    }
});
</script>