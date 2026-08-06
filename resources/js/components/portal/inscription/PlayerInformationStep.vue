<template>
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
                    :accept="photoFileAccept"
                    :default-preview="defaultUserPhoto"
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
                <InputField name="names" label="Nombres" :is-required="true" />
                <InputField name="last_names" label="Apellidos" :is-required="true" />
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
                    @blur="$emit('normalize-email', 'email')"
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
</template>

<script setup>
import { ErrorMessage, Field } from 'vee-validate';
import InputField from '@/components/form/Input.vue';
import InputFileImage from '@/components/form/FileInputImage.vue';

defineProps({
    errors: {
        type: Object,
        required: true,
    },
    documentTypeOptions: {
        type: Array,
        required: true,
    },
    genderOptions: {
        type: Array,
        required: true,
    },
    photoFileAccept: {
        type: String,
        required: true,
    },
    defaultUserPhoto: {
        type: String,
        required: true,
    },
    minBirthDate: {
        type: String,
        required: true,
    },
    maxBirthDate: {
        type: String,
        required: true,
    },
});

defineEmits(['normalize-email']);
</script>
