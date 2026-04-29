<template>
    <div
        id="modal_inscription"
        ref="modalRef"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-xl mw-90 w-100" role="document">
            <div class="wizard-content">
                <div class="modal-content">
                    <div class="modal-body position-relative" :aria-busy="submitting ? 'true' : 'false'">
                        <Loader :is-loading="submitting" loading-text="Guardando inscripción..." />

                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>

                        <Wizard v-model="currentStep" :options="wizardOptions">

                            <template #info>
                                <h6 class="d-flex block-helper justify-content-center">Los campos con <span
                                        class="text-danger">&ensp;(*)&ensp;</span> son requeridos.</h6>
                            </template>

                            <Step title="Información Del Deportista">

                                <h6 class="d-flex block-helper justify-content-center">
                                    La Foto debe ser tipo documento de lo contrario abstenerse de agregarla.
                                </h6>

                                <fieldset class="col-md-12 p-2">

                                    <div class="row col-md-12">
                                        <div class="col-md-3">
                                            <span class="text-muted d-flex justify-content-center">La cabeza hacía arriba</span>
                                            <InputFileImage
                                                name="photo"
                                                label="Foto tipo documento"
                                                accept="image/png, image/jpeg"
                                                :default-preview="assets.defaultUserPhoto"
                                            />
                                        </div>

                                        <div class="col-md-3">
                                            <InputField
                                                name="identification_document"
                                                label="# Doc de identidad"
                                                :is-required="true"
                                                inputmode="numeric"
                                            />


                                                <label for="document_type">
                                                    Tipo Documento
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="document_type"
                                                    name="document_type"
                                                    as="select"
                                                    class="form-select form-select-sm"
                                                    :class="{ 'is-invalid': errors.document_type }"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option
                                                        v-for="option in documentTypeOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </Field>
                                                <ErrorMessage name="document_type" class="invalid-feedback d-block" />


                                            <InputField
                                                name="date_birth"
                                                label="Fecha de nacimiento"
                                                :is-required="true"
                                                type="date"
                                                :max="maxBirthDate"
                                                :min="minBirthDate"
                                            />
                                        </div>

                                        <div class="col-md-3">
                                            <InputField
                                                name="names"
                                                label="Nombres"
                                                :is-required="true"
                                            />

                                            <InputField
                                                name="last_names"
                                                label="Apellidos"
                                                :is-required="true"
                                            />

                                            <InputField
                                                name="place_birth"
                                                label="Lugar de nacimiento"
                                                :is-required="true"
                                                list="place-birth-list"
                                            />
                                        </div>

                                        <div class="col-md-3">

                                                <label for="gender">
                                                    Género
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="gender"
                                                    name="gender"
                                                    as="select"
                                                    class="form-select form-select-sm"
                                                    :class="{ 'is-invalid': errors.gender }"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option
                                                        v-for="option in genderOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </Field>
                                                <ErrorMessage name="gender" class="invalid-feedback d-block" />


                                            <InputField
                                                name="email"
                                                label="Correo Electrónico"
                                                :is-required="true"
                                                type="email"
                                                @blur="normalizeEmailField('email')"
                                            />

                                            <InputField
                                                name="mobile"
                                                label="# Telefónicos / Celular"
                                                :is-required="true"
                                            />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="medical_history">Antecedentes Médicos</label>
                                                <Field
                                                    id="medical_history"
                                                    name="medical_history"
                                                    as="textarea"
                                                    class="form-control"
                                                    rows="5"
                                                    :class="{ 'is-invalid': errors.medical_history }"
                                                />
                                                <ErrorMessage name="medical_history" class="invalid-feedback d-block" />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </Step>

                            <Step title="Información general">
                                <fieldset class="col-md-12 p-2">
                                    <div class="row col-md-12">
                                        <div class="col-md-4 mb-2">
                                            <InputField
                                                name="address"
                                                label="Dirección de residencia"
                                                :is-required="true"
                                            />

                                            <InputField
                                                name="municipality"
                                                label="Municipio de residencia"
                                                :is-required="true"
                                                list="municipality-list"
                                            />

                                            <InputField
                                                name="neighborhood"
                                                label="Barrio de residencia"
                                                :is-required="true"
                                                list="neighborhood-list"
                                            />
                                        </div>

                                        <div class="col-md-4 mb-2">

                                                <label for="rh">
                                                    Grupo sanguíneo
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="rh"
                                                    name="rh"
                                                    as="select"
                                                    class="form-control"
                                                    :class="{ 'is-invalid': errors.rh }"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option
                                                        v-for="option in bloodTypeOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </Field>
                                                <ErrorMessage name="rh" class="invalid-feedback d-block" />


                                            <InputField
                                                name="eps"
                                                label="EPS"
                                                :is-required="true"
                                                list="eps-list"
                                            />

                                            <InputField
                                                name="student_insurance"
                                                label="Nombre del Seguro Estudiantil"
                                            />
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <InputField
                                                name="school"
                                                label="Institución educativa"
                                                :is-required="true"
                                                list="school-list"
                                            />


                                                <label for="degree">
                                                    Grado que cursa
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="degree"
                                                    name="degree"
                                                    as="select"
                                                    class="form-control"
                                                    :class="{ 'is-invalid': errors.degree }"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option
                                                        v-for="option in degreeOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </Field>
                                                <ErrorMessage name="degree" class="invalid-feedback d-block" />



                                                <label for="jornada">
                                                    Jornada de estudio
                                                    <span class="text-danger">&nbsp;(*)</span>
                                                </label>
                                                <Field
                                                    id="jornada"
                                                    name="jornada"
                                                    as="select"
                                                    class="form-control"
                                                    :class="{ 'is-invalid': errors.jornada }"
                                                >
                                                    <option value="">Selecciona...</option>
                                                    <option
                                                        v-for="option in jornadaOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </Field>
                                                <ErrorMessage name="jornada" class="invalid-feedback d-block" />

                                        </div>
                                    </div>
                                </fieldset>
                            </Step>

                            <Step title="Información Familiar">
                                <div class="row">
                                    <fieldset class="col-md-12 p-2">
                                        <legend>Acudiente:</legend>
                                        <h6 class="d-flex block-helper justify-content-center">
                                            Esta persona es la que va a figurar en el <strong>&nbsp;CONTRATO&nbsp;</strong> con
                                            <strong>&nbsp;{{ school.name }}&nbsp;</strong>
                                        </h6>

                                        <div class="row col-md-12">
                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_name"
                                                    label="Nombres completos"
                                                    :is-required="true"
                                                />
                                            </div>

                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_num_doc"
                                                    label="Número de documento del acudiente"
                                                    :is-required="true"
                                                />
                                            </div>

                                            <div class="col-md-4">

                                                    <label for="tutor_relationship">
                                                        Parentesco
                                                        <span class="text-danger">&nbsp;(*)</span>
                                                    </label>
                                                    <Field
                                                        id="tutor_relationship"
                                                        name="tutor_relationship"
                                                        as="select"
                                                        class="form-control form-control-sm"
                                                        :class="{ 'is-invalid': errors.tutor_relationship }"
                                                    >
                                                        <option value="">Selecciona...</option>
                                                        <option
                                                            v-for="option in relationshipOptions"
                                                            :key="option.value"
                                                            :value="option.value"
                                                        >
                                                            {{ option.label }}
                                                        </option>
                                                    </Field>
                                                    <ErrorMessage name="tutor_relationship" class="invalid-feedback d-block" />

                                            </div>

                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_phone"
                                                    label="Whatsapp"
                                                    :is-required="true"
                                                />
                                            </div>

                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_work"
                                                    label="Empresa donde labora"
                                                    :is-required="true"
                                                />
                                            </div>

                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_position_held"
                                                    label="Cargo que desempeña"
                                                    :is-required="true"
                                                />
                                            </div>

                                            <div class="col-md-4">
                                                <InputField
                                                    name="tutor_email"
                                                    label="Correo electrónico"
                                                    :is-required="true"
                                                    type="email"
                                                    @blur="normalizeEmailField('tutor_email')"
                                                />
                                                <small class="form-text text-muted">Correo electrónico para enviar notificaciones.</small>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </Step>

                            <Step v-if="hasTermsStep" title="T y C">
                                <h6 v-if="requiresTutorSignature || requiresPlayerSignature" class="d-flex block-helper justify-content-center">
                                    <strong>Desliza con el mouse de tu ordenador o si estás en dispositivo móvil firma en el área indicada.</strong>
                                </h6>

                                <div class="row">
                                    <fieldset v-if="requiresTutorSignature" class="col-md-6 p-2">
                                        <legend>
                                            Firma Del Acudiente
                                            <span class="text-danger">&nbsp;(*)</span>
                                        </legend>

                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-center">
                                                <SignaturePadField
                                                    name="signatureTutor"
                                                    label="Firma del acudiente"
                                                    help=""
                                                    :required="true"
                                                />
                                            </div>
                                        </div>
                                        <h6 class="block-helper justify-content-center">
                                            Firma de la persona que va a figurar en el <strong>&nbsp;CONTRATO&nbsp;</strong>
                                        </h6>
                                    </fieldset>

                                    <fieldset v-if="requiresPlayerSignature" class="col-md-6 p-2">
                                        <legend>
                                            Firma del Deportista
                                            <span class="text-danger">&nbsp;(*)</span>
                                        </legend>

                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-center">
                                                <SignaturePadField
                                                    name="signatureAlumno"
                                                    label="Firma del deportista"
                                                    help=""
                                                    :required="requiresPlayerSignature"
                                                />
                                            </div>
                                        </div>
                                        <h6 class="block-helper justify-content-center">
                                            Firma del <strong>&nbsp;Deportista&nbsp;</strong> que hará parte de {{ school.name }}
                                        </h6>
                                    </fieldset>

                                    <fieldset class="col-md-12 p-2">
                                        <div
                                            v-for="contract in acceptanceContracts"
                                            :key="contract.code"
                                            class="row"
                                        >
                                            <div class="check col">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox checkbox-primary">
                                                        <Field :name="contract.acceptance_field" v-slot="{ field, value, handleChange }">
                                                            <input
                                                                v-bind="field"
                                                                :id="contract.acceptance_field"
                                                                type="checkbox"
                                                                value="true"
                                                                class="custom-control-input"
                                                                :checked="Boolean(value)"
                                                                @change="handleChange($event.target.checked)"
                                                            >
                                                        </Field>
                                                        <label :for="contract.acceptance_field" class="custom-control-label checkboxsizeletter">
                                                            (<span class="text-danger">*</span>) Acepta los términos y condiciones del
                                                            <a target="_blank" :href="contract.url">{{ contract.label }}</a>
                                                        </label>
                                                        <ErrorMessage :name="contract.acceptance_field" class="custom-error d-block" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </Step>

                            <Step v-if="hasDocumentsStep" title="Documentos">
                                <h6 class="d-flex block-helper justify-content-center">
                                    <strong>Los archivos que van a ser cargados deben ser JPG, JPEG, PNG o PDF, con un tamaño máximo de {{ fileSizeMb }} MB.</strong>
                                </h6>

                                <fieldset class="col-md-12 p-2">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <PortalFileInput
                                                name="player_document"
                                                label="Documento de identidad del deportista escaneado"
                                                accept="image/png, image/jpeg, application/pdf"
                                                :required="true"
                                            />
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <PortalFileInput
                                                name="medical_certificate"
                                                label="Certificado EPS escaneado"
                                                accept="image/png, image/jpeg, application/pdf"
                                                :required="true"
                                            />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <PortalFileInput
                                                name="tutor_document"
                                                label="Documento de identidad del acudiente escaneado"
                                                accept="image/png, image/jpeg, application/pdf"
                                                :required="true"
                                            />
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <PortalFileInput
                                                name="payment_receipt"
                                                label="Adjunte recibo de consignación o transferencia (Opcional)"
                                                accept="image/png, image/jpeg, application/pdf"
                                            />
                                        </div>
                                    </div>
                                </fieldset>
                            </Step>
                        </Wizard>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <datalist id="place-birth-list">
        <option v-for="option in autocomplete.place_birth" :key="`place-${option}`" :value="option" />
    </datalist>

    <datalist id="municipality-list">
        <option v-for="option in autocomplete.place_birth" :key="`municipality-${option}`" :value="option" />
    </datalist>

    <datalist id="neighborhood-list">
        <option v-for="option in autocomplete.neighborhood" :key="`neighborhood-${option}`" :value="option" />
    </datalist>

    <datalist id="eps-list">
        <option v-for="option in autocomplete.eps" :key="`eps-${option}`" :value="option" />
    </datalist>

    <datalist id="school-list">
        <option v-for="option in autocomplete.school" :key="`school-${option}`" :value="option" />
    </datalist>
</template>

<script setup>
import axios from 'axios';
import * as yup from 'yup';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { ErrorMessage, Field, useForm } from 'vee-validate';
import { useReCaptcha } from 'vue-recaptcha-v3';
import Loader from '@/components/general/Loader.vue';
import InputField from '@/components/form/Input.vue';
import InputFileImage from '@/components/form/FileInputImage.vue';
import PortalFileInput from '@/components/portal/PortalFileInput.vue';
import SignaturePadField from '@/components/portal/SignaturePadField.vue';
import Wizard from '@/plugins/wizard/Wizard.vue';
import Step from '@/plugins/wizard/Step.vue';

const props = defineProps({
    school: {
        type: Object,
        required: true,
    },
    year: {
        type: [String, Number],
        required: true,
    },
    fileSizeMb: {
        type: Number,
        default: 3,
    },
    storageKey: {
        type: String,
        required: true,
    },
    endpoints: {
        type: Object,
        required: true,
    },
    assets: {
        type: Object,
        required: true,
    },
    contracts: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        required: true,
    },
    recaptcha: {
        type: Object,
        default: () => ({
            enabled: false,
            action: 'inscriptions',
        }),
    },
});

const FILE_FIELDS = ['photo', 'player_document', 'medical_certificate', 'tutor_document', 'payment_receipt'];
const SIGNATURE_FIELDS = ['signatureTutor', 'signatureAlumno'];
const LEGACY_CONTRACTS = {
    inscription: {
        code: 'inscription',
        label: 'Contrato de inscripción',
        url: '',
        acceptance_field: 'contrato_insc',
        requires_acceptance: true,
        requires_tutor_signature: true,
        requires_player_signature: false,
    },
    affiliate: {
        code: 'affiliate',
        label: 'Contrato de afiliación y corresponsabilidad deportiva',
        url: '',
        acceptance_field: 'contrato_aff',
        requires_acceptance: true,
        requires_tutor_signature: true,
        requires_player_signature: true,
    },
};

const modalRef = ref(null);
const currentStep = ref(0);
const globalError = ref('');
const submitting = ref(false);
const appName = window.__APP_CONFIG__?.appName ?? 'Golapp';
const recaptcha = useReCaptcha();
const appConfig = window.__APP_CONFIG__ ?? {};
const autocomplete = ref({
    school: [],
    place_birth: [],
    neighborhood: [],
    eps: [],
});
const persistencePaused = ref(false);

let persistTimeout = null;
let lookupTimeout = null;
let lookupCounter = 0;

const api = axios.create({
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

api.interceptors.request.use((config) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    return config;
});

const normalizeEmail = (value) => String(value ?? '').trim().toLowerCase();

const parseDate = (value) => {
    const [year, month, day] = String(value ?? '').split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const formatDate = (value) => {
    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const normalizeDate = (value) => {
    const match = String(value ?? '').match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
};

const toOptions = (options) => {
    if (Array.isArray(options)) {
        return options.map((item, index) => {
            if (item && typeof item === 'object' && !Array.isArray(item)) {
                const optionValue = item.value ?? item.id ?? index;
                const optionLabel = item.label ?? item.name ?? optionValue;

                return {
                    value: String(optionValue),
                    label: String(optionLabel),
                };
            }

            return {
                value: String(item),
                label: String(item),
            };
        });
    }

    return Object.entries(options ?? {}).map(([value, label]) => ({
        value: String(value),
        label: String(label),
    }));
};

const today = new Date();
const minBirthDateValue = new Date(today.getFullYear() - 20, today.getMonth(), today.getDate());
const maxBirthDateValue = new Date(today.getFullYear() - 3, today.getMonth(), today.getDate());
const minBirthDate = formatDate(minBirthDateValue);
const maxBirthDate = formatDate(maxBirthDateValue);

const fileFieldSchema = (label, required = false, requiredMessage = `${label} es obligatorio.`) => {
    let schema = yup
        .mixed()
        .nullable()
        .test('file-type', `${label} debe estar en formato JPG, JPEG, PNG o PDF.`, (value) => {
            if (!value) {
                return true;
            }

            return [
                'image/png',
                'image/jpeg',
                'application/pdf',
            ].includes(value.type);
        })
        .test('file-size', `${label} no puede superar ${props.fileSizeMb} MB.`, (value) => {
            if (!value) {
                return true;
            }

            return value.size <= props.fileSizeMb * 1024 * 1024;
        });

    if (required) {
        schema = schema.required(requiredMessage);
    }

    return schema;
};

const photoFieldSchema = yup
    .mixed()
    .nullable()
    .test('file-type', 'La foto debe estar en formato JPG, JPEG o PNG.', (value) => {
        if (!value) {
            return true;
        }

        return ['image/png', 'image/jpeg'].includes(value.type);
    })
    .test('file-size', `La foto no puede superar ${props.fileSizeMb} MB.`, (value) => {
        if (!value) {
            return true;
        }

        return value.size <= props.fileSizeMb * 1024 * 1024;
    });

const availableContracts = computed(() => {
    if (Array.isArray(props.contracts?.available)) {
        return props.contracts.available
            .filter((contract) => Boolean(contract?.code) && Boolean(contract?.url))
            .map((contract) => ({
                ...(LEGACY_CONTRACTS[contract.code] ?? {}),
                ...contract,
            }))
    }

    return Object.entries(LEGACY_CONTRACTS)
        .flatMap(([code, contract]) => (
            props.contracts?.[code]
                ? [{ ...contract, url: props.contracts[code] }]
                : []
        ))
})

const acceptanceContracts = computed(() => (
    availableContracts.value.filter((contract) => contract.requires_acceptance && contract.acceptance_field)
))

const checkboxFields = computed(() => (
    acceptanceContracts.value
        .map((contract) => contract.acceptance_field)
        .filter(Boolean)
))

const requiresTutorSignature = computed(() => (
    availableContracts.value.some((contract) => contract.requires_tutor_signature)
))

const requiresPlayerSignature = computed(() => (
    availableContracts.value.some((contract) => contract.requires_player_signature)
))

const acceptanceRules = Object.fromEntries(
    acceptanceContracts.value.map((contract) => [
        contract.acceptance_field,
        props.school.create_contract
            ? yup.boolean().oneOf([true], `Debes aceptar ${contract.label.toLowerCase()}.`)
            : yup.boolean(),
    ])
)

const schema = yup.object({
    year: yup.string().required(),
    photo: photoFieldSchema,

    names: yup.string().trim().required('Ingresa los nombres.').max(50, 'Los nombres no pueden superar 50 caracteres.'),
    last_names: yup.string().trim().required('Ingresa los apellidos.').max(50, 'Los apellidos no pueden superar 50 caracteres.'),
    date_birth: yup
        .string()
        .required('Ingresa la fecha de nacimiento.')
        .test('date-format', 'Ingresa una fecha válida.', (value) => Boolean(parseDate(value)))
        .test('date-range', 'La fecha de nacimiento debe estar entre 3 y 20 años.', (value) => {
            const date = parseDate(value);

            if (!date) {
                return false;
            }

            return date >= minBirthDateValue && date <= maxBirthDateValue;
        }),
    place_birth: yup.string().trim().required('Ingresa el lugar de nacimiento.').max(100, 'El lugar de nacimiento no puede superar 100 caracteres.'),
    identification_document: yup
        .string()
        .trim()
        .required('Ingresa el documento de identidad.')
        .max(50, 'El documento no puede superar 50 caracteres.')
        .matches(/^\d+$/, 'El documento solo debe contener números.'),
    document_type: yup.string().trim().required('Selecciona el tipo de documento.').max(50),
    gender: yup.string().trim().required('Selecciona el género.').max(50),
    email: yup.string().trim().required('Ingresa el correo electrónico.').email('Ingresa un correo válido.'),
    mobile: yup.string().trim().required('Ingresa un número telefónico.').max(50, 'El teléfono no puede superar 50 caracteres.'),
    medical_history: yup.string().nullable().max(200, 'Los antecedentes médicos no pueden superar 200 caracteres.'),

    address: yup.string().trim().required('Ingresa la dirección de residencia.').max(50, 'La dirección no puede superar 50 caracteres.'),
    municipality: yup.string().trim().required('Ingresa el municipio de residencia.').max(50, 'El municipio no puede superar 50 caracteres.'),
    neighborhood: yup.string().trim().required('Ingresa el barrio de residencia.').max(50, 'El barrio no puede superar 50 caracteres.'),
    rh: yup.string().trim().required('Selecciona el grupo sanguíneo.').max(50),
    eps: yup.string().trim().required('Ingresa la EPS.').max(50, 'La EPS no puede superar 50 caracteres.'),
    student_insurance: yup.string().nullable().max(50, 'El seguro estudiantil no puede superar 50 caracteres.'),
    school: yup.string().trim().required('Ingresa la institución educativa.').max(50, 'La institución educativa no puede superar 50 caracteres.'),
    degree: yup.string().trim().required('Selecciona el grado.').max(50),
    jornada: yup.string().trim().required('Selecciona la jornada.').max(50),

    tutor_name: yup.string().trim().required('Ingresa los nombres del acudiente.').max(50),
    tutor_num_doc: yup.string().trim().required('Ingresa el numero de documento del acudiente.').max(50),
    tutor_relationship: yup.string().trim().required('Selecciona el parentesco del acudiente.').max(50),
    tutor_phone: yup.string().trim().required('Ingresa el teléfono del acudiente.').max(50),
    tutor_work: yup.string().trim().required('Ingresa la empresa del acudiente.').max(50),
    tutor_position_held: yup.string().trim().required('Ingresa el cargo del acudiente.').max(50),
    tutor_email: yup.string().trim().required('Ingresa el correo del acudiente.').email('Ingresa un correo válido.').max(50),

    signatureTutor: props.school.create_contract && requiresTutorSignature.value
        ? yup.string().required('Ingresa la firma del acudiente para continuar.')
        : yup.string().nullable(),
    signatureAlumno: props.school.create_contract && requiresPlayerSignature.value
        ? yup.string().required('Ingresa la firma del deportista para continuar.')
        : yup.string().nullable(),
    ...acceptanceRules,

    player_document: props.school.send_documents
        ? fileFieldSchema('El documento de identidad del deportista', true)
        : fileFieldSchema('El documento de identidad del deportista'),
    medical_certificate: props.school.send_documents
        ? fileFieldSchema('El certificado EPS', true)
        : fileFieldSchema('El certificado EPS'),
    tutor_document: props.school.send_documents
        ? fileFieldSchema(
            'El documento de identidad del acudiente escaneado',
            true,
            'Adjunta el documento de identidad escaneado del acudiente.'
        )
        : fileFieldSchema('El documento del acudiente'),
    payment_receipt: fileFieldSchema('El recibo de pago'),
});

const defaultValues = () => ({
    year: String(props.year),
    photo: null,

    names: '',
    last_names: '',
    date_birth: '',
    place_birth: '',
    identification_document: '',
    document_type: '',
    gender: '',
    email: '',
    mobile: '',
    medical_history: '',

    address: '',
    municipality: '',
    neighborhood: '',
    rh: '',
    eps: '',
    student_insurance: 'Sura',
    school: '',
    degree: '',
    jornada: '',

    tutor_name: '',
    tutor_num_doc: '',
    tutor_relationship: '',
    tutor_phone: '',
    tutor_work: '',
    tutor_position_held: '',
    tutor_email: '',

    signatureTutor: '',
    signatureAlumno: '',
    ...Object.fromEntries(checkboxFields.value.map((field) => [field, false])),

    player_document: null,
    medical_certificate: null,
    tutor_document: null,
    payment_receipt: null,
});

const {
    errors,
    handleSubmit,
    resetForm,
    setErrors,
    setFieldValue,
    validateField,
    values,
} = useForm({
    validationSchema: schema,
    initialValues: defaultValues(),
    keepValuesOnUnmount: true,
});

const hasTermsStep = computed(() => Boolean(props.school.create_contract) && availableContracts.value.length > 0);
const hasDocumentsStep = computed(() => Boolean(props.school.send_documents));

const genderOptions = computed(() => toOptions(props.options.genders));
const documentTypeOptions = computed(() => toOptions(props.options.documentTypes));
const bloodTypeOptions = computed(() => toOptions(props.options.bloodTypes));
const relationshipOptions = computed(() => toOptions(props.options.relationships));
const jornadaOptions = computed(() => toOptions(props.options.jornada));
const degreeOptions = computed(() => Array.from({ length: 12 }, (_, index) => ({
    value: String(index),
    label: String(index),
})));

const steps = computed(() => {
    const baseSteps = [
        {
            key: 'player',
            title: 'Información Del Deportista',
            fields: [
                'photo',
                'identification_document',
                'document_type',
                'date_birth',
                'names',
                'last_names',
                'place_birth',
                'gender',
                'email',
                'mobile',
                'medical_history',
            ],
        },
        {
            key: 'general',
            title: 'Información general',
            fields: [
                'address',
                'municipality',
                'neighborhood',
                'rh',
                'eps',
                'student_insurance',
                'school',
                'degree',
                'jornada',
            ],
        },
        {
            key: 'family',
            title: 'Información Familiar',
            fields: [
                'tutor_name',
                'tutor_num_doc',
                'tutor_relationship',
                'tutor_phone',
                'tutor_work',
                'tutor_position_held',
                'tutor_email',
            ],
        },
    ];

    if (hasTermsStep.value) {
        const termFields = [...checkboxFields.value];

        if (requiresTutorSignature.value) {
            termFields.unshift('signatureTutor');
        }

        if (requiresPlayerSignature.value) {
            termFields.push('signatureAlumno');
        }

        baseSteps.push({
            key: 'terms',
            title: 'T y C',
            fields: termFields,
        });
    }

    if (hasDocumentsStep.value) {
        baseSteps.push({
            key: 'documents',
            title: 'Documentos',
            fields: [
                'player_document',
                'medical_certificate',
                'tutor_document',
                'payment_receipt',
            ],
        });
    }

    return baseSteps;
});

const wizardOptions = computed(() => ({
    transitionEffect: 1,
    enableCancelButton: true,
    labels: {
        cancel: 'Cancelar Y Borrar Formulario',
        finish: submitting.value ? 'Guardando...' : 'Guardar',
        next: 'Siguiente',
        previous: 'Anterior',
    },
    onStepChanging: async (currentIndex, nextIndex) => {
        if (submitting.value) {
            return false;
        }

        if (currentIndex > nextIndex) {
            return true;
        }

        globalError.value = '';
        return validateStep(currentIndex);
    },
    onFinishing: async () => {
        if (submitting.value) {
            return false;
        }

        globalError.value = '';
        return validateStep(currentStep.value);
    },
    onFinished: async () => {
        await finishWizard();
    },
    onCanceled: async () => {
        await cancelWizard();
    },
}));

const readPersistedValues = () => {
    try {
        const serialized = localStorage.getItem(props.storageKey);
        return serialized ? JSON.parse(serialized) : null;
    } catch (error) {
        return null;
    }
};

const clearPersistedValues = () => {
    localStorage.removeItem(props.storageKey);
};

const sanitizeValuesForStorage = (currentValues) => {
    return Object.fromEntries(
        Object.entries(currentValues)
            .filter(([key]) => !FILE_FIELDS.includes(key) && !SIGNATURE_FIELDS.includes(key))
            .map(([key, value]) => [key, value ?? ''])
    );
};

const pausePersistence = async (callback) => {
    persistencePaused.value = true;
    await callback();
    await nextTick();
    persistencePaused.value = false;
};

const resetWizard = async () => {
    await pausePersistence(async () => {
        clearPersistedValues();
        resetForm({
            values: defaultValues(),
        });
        currentStep.value = 0;
        globalError.value = '';
    });
};

const goToStep = (index) => {
    if (index >= 0 && index < steps.value.length) {
        currentStep.value = index;
    }
};

const validateStep = async (index) => {
    const step = steps.value[index];

    if (!step) {
        return true;
    }

    await nextTick();

    const validationResults = await Promise.all(
        step.fields.map((field) => validateField(field))
    );

    const firstInvalidFieldIndex = validationResults.findIndex((result) => result?.valid === false);
    const firstInvalidField = firstInvalidFieldIndex >= 0
        ? step.fields[firstInvalidFieldIndex]
        : null;

    if (firstInvalidField) {
        currentStep.value = index;
        return false;
    }

    return true;
};

const executeRecaptcha = async () => {
    if (!props.recaptcha?.enabled) {
        return '';
    }

    if (!appConfig.recaptchaSiteKey) {
        return null;
    }

    if (!recaptcha?.executeRecaptcha || !recaptcha?.recaptchaLoaded) {
        throw new Error('No pudimos validar el captcha. Recarga la página e inténtalo nuevamente.');
    }

    await recaptcha.recaptchaLoaded();

    return recaptcha.executeRecaptcha(props.recaptcha.action || 'inscriptions');
};

const buildFormData = (payload, recaptchaToken) => {
    const formData = new FormData();

    Object.entries(payload).forEach(([key, value]) => {
        if (FILE_FIELDS.includes(key)) {
            if (value instanceof File) {
                formData.append(key, value);
            }
            return;
        }

        if (checkboxFields.value.includes(key)) {
            if (value) {
                formData.append(key, '1');
            }
            return;
        }

        if (value === null || value === undefined) {
            return;
        }

        formData.append(key, typeof value === 'string' ? value : String(value));
    });

    if (recaptchaToken) {
        formData.append('g-recaptcha-response', recaptchaToken);
    }

    return formData;
};

const hideModal = () => {
    const modalElement = modalRef.value;

    if (!modalElement) {
        return;
    }

    const modalInstance = window.bootstrap?.Modal?.getInstance(modalElement)
        ?? window.bootstrap?.Modal?.getOrCreateInstance?.(modalElement);

    if (modalInstance) {
        modalInstance.hide();
        return;
    }

    modalElement.classList.remove('show');
    modalElement.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach((element) => element.remove());
};

const finishWizard = async () => {
    const result = await window.Swal.fire({
        title: appName,
        text: '¿Deseas enviar el formulario y crear una inscripción?',
        icon: 'warning',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
    });

    if (!result.isConfirmed) {
        return;
    }

    await submitForm();
};

const submitForm = handleSubmit(async (submittedValues) => {
    submitting.value = true;
    globalError.value = '';

    try {
        const recaptchaToken = await executeRecaptcha();
        const payload = {
            ...submittedValues,
            email: normalizeEmail(submittedValues.email),
            tutor_email: normalizeEmail(submittedValues.tutor_email),
            year: String(submittedValues.year ?? props.year),
        };

        const formData = buildFormData(payload, recaptchaToken);

        await api.post(props.endpoints.store, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        await resetWizard();
        hideModal();

        await window.Swal.fire({
            icon: 'success',
            title: appName,
            text: 'Se ha creado la inscripción correctamente, se enviará al correo de notificación la información necesaria.',
        });

        window.location.reload();
    } catch (error) {
        const response = error.response;
        const backendErrors = response?.data?.errors ?? {};
        const fieldErrors = Object.fromEntries(
            Object.entries(backendErrors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
        );

        if (Object.keys(fieldErrors).length > 0) {
            const firstInvalidStep = steps.value.findIndex((step) =>
                step.fields.some((field) => Boolean(fieldErrors[field]))
            );

            if (firstInvalidStep >= 0) {
                currentStep.value = firstInvalidStep;
                await nextTick();
            }

            setErrors(fieldErrors);
        }

        globalError.value = response?.data?.message || error.message || 'Algo salió mal, no hemos podido procesar la información en este momento.';

        await window.Swal.fire({
            icon: 'error',
            title: appName,
            text: globalError.value,
        });
    } finally {
        submitting.value = false;
    }
});

const cancelWizard = async () => {
    const result = await window.Swal.fire({
        title: '¡Atención!',
        text: 'Esta acción borrará la información agregada en el formulario ¿Deseas proceder?',
        icon: 'warning',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
    });

    if (!result.isConfirmed) {
        return;
    }

    await resetWizard();
    hideModal();
};

const populatePlayerData = (player) => {
    if (!player || typeof player !== 'object') {
        return;
    }

    const fieldsToPopulate = {
        names: player.names ?? '',
        last_names: player.last_names ?? '',
        date_birth: normalizeDate(player.date_birth),
        place_birth: player.place_birth ?? '',
        document_type: player.document_type ? String(player.document_type) : '',
        gender: player.gender ? String(player.gender) : '',
        email: normalizeEmail(player.email),
        mobile: player.mobile ?? '',
        medical_history: player.medical_history ?? '',
        address: player.address ?? '',
        municipality: player.municipality ?? '',
        neighborhood: player.neighborhood ?? '',
        rh: player.rh ? String(player.rh) : '',
        eps: player.eps ?? '',
        student_insurance: player.student_insurance ?? 'Sura',
        school: player.school ?? '',
        degree: player.degree !== null && player.degree !== undefined ? String(player.degree) : '',
        jornada: player.jornada ? String(player.jornada) : '',
    };

    Object.entries(fieldsToPopulate).forEach(([field, value]) => {
        setFieldValue(field, value);
    });
};

const fetchAutocompleteOptions = async () => {
    try {
        const response = await api.get(props.endpoints.autocomplete, {
            params: {
                fields: ['school', 'place_birth', 'neighborhood', 'eps', 'commune'],
            },
        });

        autocomplete.value = {
            school: response.data?.school ?? response.data?.data?.school ?? [],
            place_birth: response.data?.place_birth ?? response.data?.data?.place_birth ?? [],
            neighborhood: response.data?.neighborhood ?? response.data?.data?.neighborhood ?? [],
            eps: response.data?.eps ?? response.data?.data?.eps ?? [],
        };
    } catch (error) {
        autocomplete.value = {
            school: [],
            place_birth: [],
            neighborhood: [],
            eps: [],
        };
    }
};

const lookupDocument = async (documentNumber) => {
    const requestId = ++lookupCounter;

    try {
        const response = await api.get(props.endpoints.searchDoc, {
            params: {
                doc: documentNumber,
                school_id: props.school.id,
            },
        });

        if (requestId !== lookupCounter || String(values.identification_document).trim() !== documentNumber) {
            return;
        }

        populatePlayerData(response.data?.data ?? {});
    } catch (error) {
        //
    }
};

const normalizeEmailField = (fieldName) => {
    setFieldValue(fieldName, normalizeEmail(values[fieldName]));
};

watch(
    () => values.email,
    (currentValue, previousValue) => {
        const normalizedEmail = normalizeEmail(currentValue);

        if (currentValue !== normalizedEmail) {
            setFieldValue('email', normalizedEmail);
            return;
        }

        const previousEmail = normalizeEmail(previousValue);
        const currentTutorEmail = normalizeEmail(values.tutor_email);

        if (!currentTutorEmail || currentTutorEmail === previousEmail) {
            setFieldValue('tutor_email', normalizedEmail);
        }
    }
);

watch(
    () => values.identification_document,
    (currentValue) => {
        const documentNumber = String(currentValue ?? '').trim();

        clearTimeout(lookupTimeout);

        if (!/^\d{8,}$/.test(documentNumber)) {
            return;
        }

        lookupTimeout = window.setTimeout(() => {
            lookupDocument(documentNumber);
        }, 400);
    }
);

watch(
    values,
    (currentValues) => {
        if (persistencePaused.value) {
            return;
        }

        clearTimeout(persistTimeout);

        persistTimeout = window.setTimeout(() => {
            localStorage.setItem(props.storageKey, JSON.stringify(sanitizeValuesForStorage(currentValues)));
        }, 250);
    },
    { deep: true }
);

onMounted(async () => {
    const persistedValues = readPersistedValues();

    if (persistedValues) {
        await pausePersistence(async () => {
            resetForm({
                values: {
                    ...defaultValues(),
                    ...persistedValues,
                },
            });
        });
    }

    await fetchAutocompleteOptions();
});

onBeforeUnmount(() => {
    clearTimeout(persistTimeout);
    clearTimeout(lookupTimeout);
});
</script>

<script>
export default {
    name: 'PortalSchoolInscriptionModal',
};
</script>

<style scoped>
.wizard-content .modal-body {
    padding-bottom: 0;
}

.checkboxsizeletter {
    display: inline;
}
</style>
