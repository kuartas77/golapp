<template>
    <h6
        v-if="requiresTutorSignature || requiresPlayerSignature"
        class="d-flex block-helper justify-content-center"
    >
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
                Firma del <strong>&nbsp;Deportista&nbsp;</strong> que hará parte de {{ schoolName }}
            </h6>
        </fieldset>

        <fieldset class="col-md-12 p-2">
            <div class="row">
                <div class="check col">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox checkbox-primary">
                            <Field name="data_processing_policy_accepted" v-slot="{ field, value, handleChange }">
                                <input
                                    v-bind="field"
                                    id="data_processing_policy_accepted"
                                    type="checkbox"
                                    value="true"
                                    class="custom-control-input"
                                    :checked="Boolean(value)"
                                    @change="handleChange($event.target.checked)"
                                >
                            </Field>
                            <label
                                for="data_processing_policy_accepted"
                                class="custom-control-label checkboxsizeletter"
                            >
                                (<span class="text-danger">*</span>) He leído y autorizo el
                                <a
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :href="dataProcessingPolicy.url"
                                >
                                    tratamiento de mis datos personales y los del menor que represento
                                </a>.
                            </label>
                            <ErrorMessage name="data_processing_policy_accepted" class="custom-error d-block" />
                        </div>
                    </div>
                </div>
            </div>

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
                            <label
                                :for="contract.acceptance_field"
                                class="custom-control-label checkboxsizeletter"
                            >
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
</template>

<script setup>
import { ErrorMessage, Field } from 'vee-validate';
import SignaturePadField from '@/components/portal/SignaturePadField.vue';

defineProps({
    requiresTutorSignature: {
        type: Boolean,
        required: true,
    },
    requiresPlayerSignature: {
        type: Boolean,
        required: true,
    },
    schoolName: {
        type: String,
        required: true,
    },
    dataProcessingPolicy: {
        type: Object,
        required: true,
    },
    acceptanceContracts: {
        type: Array,
        required: true,
    },
});
</script>

<style scoped>
.checkboxsizeletter {
    display: inline;
}
</style>
