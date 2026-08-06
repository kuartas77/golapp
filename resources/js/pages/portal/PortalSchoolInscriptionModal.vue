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

                        <div class="alert alert-info portal-email-verification-notice" role="status">
                            <strong class="d-block mb-1">Verificación del correo del acudiente</strong>
                            Durante la inscripción enviaremos un código de verificación al correo electrónico del acudiente.
                            Asegúrate de ingresar un correo válido y al que tengas acceso, ya que deberás confirmarlo para
                            completar la inscripción y posteriormente ingresar al portal de acudientes.
                        </div>

                        <Wizard v-model="currentStep" :options="wizardOptions">

                            <template #info>
                                <h6 class="d-flex block-helper justify-content-center">Los campos con <span
                                        class="text-danger">&ensp;(*)&ensp;</span> son requeridos.</h6>
                            </template>

                            <Step title="Información Del Deportista">
                                <PlayerInformationStep
                                    :errors="errors"
                                    :document-type-options="documentTypeOptions"
                                    :gender-options="genderOptions"
                                    :photo-file-accept="photoFileAccept"
                                    :default-user-photo="assets.defaultUserPhoto"
                                    :min-birth-date="minBirthDate"
                                    :max-birth-date="maxBirthDate"
                                    @normalize-email="normalizeEmailField"
                                />
                            </Step>

                            <Step title="Información general">
                                <GeneralInformationStep
                                    :errors="errors"
                                    :blood-type-options="bloodTypeOptions"
                                    :degree-options="degreeOptions"
                                    :jornada-options="jornadaOptions"
                                />
                            </Step>

                            <Step title="Información Familiar">
                                <GuardianInformationStep
                                    :school-name="school.name"
                                    :errors="errors"
                                    :relationship-options="relationshipOptions"
                                    :email-verification-loading="emailVerificationLoading"
                                    :email-verified="emailVerified"
                                    :email-code-requested="emailCodeRequested"
                                    :email-verification-code="emailVerificationCode"
                                    :email-verification-error="emailVerificationError"
                                    :resend-seconds="resendSeconds"
                                    :resend-button-label="resendButtonLabel"
                                    @normalize-email="normalizeEmailField"
                                    @request-email-code="requestGuardianEmailCode"
                                    @confirm-email-code="confirmGuardianEmailCode"
                                    @update:email-verification-code="emailVerificationCode = $event"
                                />
                            </Step>

                            <Step v-if="hasTermsStep" title="T y C">
                                <TermsAndContractsStep
                                    :requires-tutor-signature="requiresTutorSignature"
                                    :requires-player-signature="requiresPlayerSignature"
                                    :school-name="school.name"
                                    :data-processing-policy="dataProcessingPolicy"
                                    :acceptance-contracts="acceptanceContracts"
                                />
                            </Step>

                            <Step v-if="hasDocumentsStep" title="Documentos">
                                <DocumentsStep
                                    :file-size-mb="fileSizeMb"
                                    :document-file-accept="documentFileAccept"
                                />
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
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useForm } from 'vee-validate';
import { useReCaptcha } from 'vue-recaptcha-v3';
import Loader from '@/components/general/Loader.vue';
import DocumentsStep from '@/components/portal/inscription/DocumentsStep.vue';
import GeneralInformationStep from '@/components/portal/inscription/GeneralInformationStep.vue';
import GuardianInformationStep from '@/components/portal/inscription/GuardianInformationStep.vue';
import PlayerInformationStep from '@/components/portal/inscription/PlayerInformationStep.vue';
import TermsAndContractsStep from '@/components/portal/inscription/TermsAndContractsStep.vue';
import { useGuardianEmailVerification } from '@/composables/portal/inscription/useGuardianEmailVerification';
import { useInscriptionPersistence } from '@/composables/portal/inscription/useInscriptionPersistence';
import { useInscriptionSubmission } from '@/composables/portal/inscription/useInscriptionSubmission';
import { usePlayerDocumentLookup } from '@/composables/portal/inscription/usePlayerDocumentLookup';
import {
    createBirthDateRange,
    createDefaultInscriptionValues,
    createInscriptionSchema,
    DATA_PROCESSING_POLICY_FIELD,
    DOCUMENT_FILE_ACCEPT,
    LEGACY_CONTRACTS,
    PHOTO_FILE_ACCEPT,
} from '@/validation/portal/inscriptionSchema';
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
    dataProcessingPolicy: {
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

const normalizeEmail = (value) => String(value ?? '').trim().toLowerCase();

const toOptions = (options, { useArrayIndexAsValue = false } = {}) => {
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
                value: String(useArrayIndexAsValue ? index : item),
                label: String(item),
            };
        });
    }

    return Object.entries(options ?? {}).map(([value, label]) => ({
        value: String(value),
        label: String(label),
    }));
};

const photoFileAccept = PHOTO_FILE_ACCEPT;
const documentFileAccept = DOCUMENT_FILE_ACCEPT;

const modalRef = ref(null);
const currentStep = ref(0);
const globalError = ref('');
const submitting = ref(false);
const appName = window.__APP_CONFIG__?.appName ?? 'Golapp';
const recaptchaClient = useReCaptcha();
const appConfig = window.__APP_CONFIG__ ?? {};

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

const {
    minValue: minBirthDateValue,
    maxValue: maxBirthDateValue,
    min: minBirthDate,
    max: maxBirthDate,
} = createBirthDateRange();

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
    [
        DATA_PROCESSING_POLICY_FIELD,
        ...acceptanceContracts.value
            .map((contract) => contract.acceptance_field)
            .filter(Boolean),
    ]
))

const requiresTutorSignature = computed(() => (
    availableContracts.value.some((contract) => contract.requires_tutor_signature)
))

const requiresPlayerSignature = computed(() => (
    availableContracts.value.some((contract) => contract.requires_player_signature)
))

const schema = createInscriptionSchema({
    fileSizeMb: props.fileSizeMb,
    school: props.school,
    acceptanceContracts: acceptanceContracts.value,
    requiresTutorSignature: requiresTutorSignature.value,
    requiresPlayerSignature: requiresPlayerSignature.value,
    minBirthDateValue,
    maxBirthDateValue,
});

const defaultValues = () => createDefaultInscriptionValues({
    year: props.year,
    checkboxFields: checkboxFields.value,
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

const {
    clearEmailVerification,
    confirmGuardianEmailCode,
    emailCodeRequested,
    emailVerificationCode,
    emailVerificationError,
    emailVerificationLoading,
    emailVerificationToken,
    emailVerified,
    requestGuardianEmailCode,
    resendButtonLabel,
    resendSeconds,
} = useGuardianEmailVerification({
    api,
    endpoints: props.endpoints,
    values,
    normalizeEmail,
});

const hasTermsStep = computed(() => Boolean(props.dataProcessingPolicy?.url));
const hasDocumentsStep = computed(() => Boolean(props.school.send_documents));

const genderOptions = computed(() => toOptions(props.options.genders));
const documentTypeOptions = computed(() => toOptions(props.options.documentTypes));
const bloodTypeOptions = computed(() => toOptions(props.options.bloodTypes));
const relationshipOptions = computed(() => toOptions(
    props.options.relationships,
    { useArrayIndexAsValue: true },
));
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
                'tutor_doc_exp',
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

const { resetWizard, restorePersistedValues } = useInscriptionPersistence({
    storageKey: props.storageKey,
    values,
    defaultValues,
    resetForm,
    currentStep,
    globalError,
    clearEmailVerification,
});

const { autocomplete, fetchAutocompleteOptions } = usePlayerDocumentLookup({
    api,
    endpoints: props.endpoints,
    schoolId: props.school.id,
    values,
    setFieldValue,
    normalizeEmail,
});

const { cancelWizard, finishWizard } = useInscriptionSubmission({
    api,
    endpoints: props.endpoints,
    school: props.school,
    year: props.year,
    recaptcha: props.recaptcha,
    appConfig,
    recaptchaClient,
    checkboxFields,
    guardianEmailVerificationToken: emailVerificationToken,
    handleSubmit,
    steps,
    currentStep,
    setErrors,
    modalRef,
    resetWizard,
    normalizeEmail,
    appName,
    submitting,
    globalError,
});

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

    if (step.key === 'family' && !emailVerified.value) {
        currentStep.value = index;
        globalError.value = 'Debes verificar el correo electrónico del acudiente para continuar.';
        return false;
    }

    return true;
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

onMounted(async () => {
    await restorePersistedValues();
    await fetchAutocompleteOptions();
});
</script>

<script>
export default {
    name: 'PortalSchoolInscriptionModal',
};
</script>

<style scoped>
.portal-email-verification-notice {
    border-left: 4px solid var(--bs-info);
}

.wizard-content .modal-body {
    padding-bottom: 0;
}

</style>
