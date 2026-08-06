<template>
    <div class="row">
        <fieldset class="col-md-12 p-2">
            <legend>Acudiente:</legend>
            <h6 class="d-flex block-helper justify-content-center">
                Esta persona es la que va a figurar en el <strong>&nbsp;CONTRATO&nbsp;</strong> con
                <strong>&nbsp;{{ schoolName }}&nbsp;</strong>
            </h6>

            <div class="row col-md-12">
                <div class="col-md-4">
                    <InputField name="tutor_name" label="Nombres completos" :is-required="true" />
                </div>

                <div class="col-md-4">
                    <InputField
                        name="tutor_num_doc"
                        label="Número de documento del acudiente"
                        :is-required="true"
                    />
                </div>

                <div class="col-md-4">
                    <InputField
                        name="tutor_doc_exp"
                        label="Lugar de expedición del documento"
                        :is-required="true"
                        list="place-birth-list"
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
                    <InputField name="tutor_phone" label="Whatsapp" :is-required="true" />
                </div>

                <div class="col-md-4">
                    <InputField name="tutor_work" label="Empresa donde labora" :is-required="true" />
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
                        @blur="$emit('normalize-email', 'tutor_email')"
                    />
                    <small class="form-text text-muted d-block">
                        Enviaremos un código a este correo para verificarlo.
                    </small>

                    <div class="mt-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            :disabled="emailVerificationLoading || emailVerified || resendSeconds > 0"
                            @click="$emit('request-email-code')"
                        >
                            {{ emailCodeRequested ? resendButtonLabel : 'Enviar código' }}
                        </button>
                    </div>
                    <div
                        v-if="emailVerificationError && !emailCodeRequested"
                        class="text-danger small mt-1"
                        role="alert"
                    >
                        {{ emailVerificationError }}
                    </div>
                </div>

                <div v-if="emailCodeRequested && !emailVerified" class="col-md-4">
                    <label for="guardian_email_verification_code">
                        Código de verificación <span class="text-danger">(*)</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <input
                            id="guardian_email_verification_code"
                            :value="emailVerificationCode"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            class="form-control"
                            :class="{ 'is-invalid': emailVerificationError }"
                            @input="updateEmailVerificationCode"
                        >
                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="emailVerificationLoading || emailVerificationCode.length !== 6"
                            @click="$emit('confirm-email-code')"
                        >
                            Verificar correo
                        </button>
                    </div>
                    <div v-if="emailVerificationError" class="invalid-feedback d-block">
                        {{ emailVerificationError }}
                    </div>
                    <small v-else class="form-text text-muted">El código vence en 10 minutos.</small>
                </div>

                <div v-if="emailVerified" class="col-md-4 d-flex align-items-center">
                    <div class="alert alert-success py-2 px-3 mb-0" role="status">
                        <i class="fas fa-check-circle me-1" aria-hidden="true"></i>
                        Correo del acudiente verificado.
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script setup>
import { ErrorMessage, Field } from 'vee-validate';
import InputField from '@/components/form/Input.vue';

defineProps({
    schoolName: {
        type: String,
        required: true,
    },
    errors: {
        type: Object,
        required: true,
    },
    relationshipOptions: {
        type: Array,
        required: true,
    },
    emailVerificationLoading: {
        type: Boolean,
        required: true,
    },
    emailVerified: {
        type: Boolean,
        required: true,
    },
    emailCodeRequested: {
        type: Boolean,
        required: true,
    },
    emailVerificationCode: {
        type: String,
        required: true,
    },
    emailVerificationError: {
        type: String,
        required: true,
    },
    resendSeconds: {
        type: Number,
        required: true,
    },
    resendButtonLabel: {
        type: String,
        required: true,
    },
});

const emit = defineEmits([
    'normalize-email',
    'request-email-code',
    'confirm-email-code',
    'update:email-verification-code',
]);

const updateEmailVerificationCode = (event) => {
    emit(
        'update:email-verification-code',
        event.target.value.replace(/\D/g, '').slice(0, 6),
    );
};
</script>
