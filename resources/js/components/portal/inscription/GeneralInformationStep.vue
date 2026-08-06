<template>
    <fieldset class="col-md-12 p-2">
        <div class="row col-md-12">
            <div class="col-md-4 mb-2">
                <InputField name="address" label="Dirección de residencia" :is-required="true" />
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

                <InputField name="eps" label="EPS" :is-required="true" list="eps-list" />
                <InputField name="student_insurance" label="Nombre del Seguro Estudiantil" />
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
</template>

<script setup>
import { ErrorMessage, Field } from 'vee-validate';
import InputField from '@/components/form/Input.vue';

defineProps({
    errors: {
        type: Object,
        required: true,
    },
    bloodTypeOptions: {
        type: Array,
        required: true,
    },
    degreeOptions: {
        type: Array,
        required: true,
    },
    jornadaOptions: {
        type: Array,
        required: true,
    },
});
</script>
