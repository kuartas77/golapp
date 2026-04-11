<template>
    <panel>
        <template #lateral />
        <template #body>
            <div v-if="hasGeneralErrors" class="alert alert-danger mb-3" role="alert">
                <div class="fw-semibold">Hay errores por corregir antes de guardar.</div>
                <div v-if="globalError" class="mt-1">{{ globalError }}</div>
                <ul v-if="formErrorSummary.length" class="mb-0 mt-2 ps-3">
                    <li v-for="error in formErrorSummary" :key="`${error.field}_${error.message}`">
                        <button v-if="error.stepIndex !== null" type="button" class="btn btn-link btn-sm alert-link p-0 align-baseline"
                            @click="goToStep(error.stepIndex)">
                            Paso {{ error.stepIndex + 1 }} · {{ error.stepTitle }}
                        </button>
                        <span v-if="error.stepIndex !== null">:</span>
                        <span>{{ error.label }}. {{ error.message }}</span>
                    </li>
                </ul>
            </div>
            <Form v-slot="{ validate, handleSubmit }" :validation-schema="schema" :initial-values="initialValues"
                :keep-values="true" @submit="onSubmit" ref="form-player">

                <Loader :is-loading="isLoading" :loading-text="loadingText" />



                <Wizard v-model="step" :options="wizardOptions(validate)" @finish="handleSubmit(onSubmit)">

                    <template #info>
                        <h6 class="d-flex block-helper justify-content-center">Los campos con <span
                                class="text-danger">&ensp;(*)&ensp;</span> son requeridos.</h6>
                    </template>

                    <Step title="Información personal">
                        <fieldset class="col-md-12 p-3">
                            <legend>Personal</legend>
                            <div class="row col-md-12">
                                <div class="col-md-3 text-center">
                                    <div class="form-group">
                                        <inputFileImage label="Foto jpg, jpeg, png" name="photo" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <inputField label="Código único" name="unique_code" :is-required="true"
                                            readonly="true" />
                                    </div>
                                    <div class="form-group mb-2">
                                        <inputField label="# Doc de identidad" name="identification_document"
                                            :is-required="true" />
                                    </div>
                                    <div class="form-group">
                                        <label for="date_birth" class="form-label">Fecha de
                                            nacimiento<span class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="date_birth" v-slot="{ field, errorMessage, meta }">
                                            <flat-pickr v-bind="field" v-model="field.value" :config="flatpickrConfig"
                                                class="form-control form-control-sm flatpickr"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }"
                                                id="date_birth"></flat-pickr>
                                        </Field>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <inputField label="Nombres" name="names" :is-required="true" />
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="document_type" class="form-label">Tipo Documento<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="document_type" v-slot="{ field, errorMessage, meta }">
                                            <select v-bind="field" id="document_type" class="form-select form-select-sm"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                                <option :value="key" v-for="value, key in settings.document_types"
                                                    :key="value">{{ value }}</option>
                                            </select>
                                        </Field>
                                    </div>

                                    <div class="form-group">
                                        <inputField label="Lugar de nacimiento" name="place_birth"
                                            :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <inputField label="Apellidos" name="last_names" :is-required="true" />
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="gender" class="form-label">Genero<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="gender" v-slot="{ field, errorMessage, meta }">
                                            <select v-bind="field" id="gender" class="form-select form-select-sm"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                                <option :value="key" v-for="value, key in settings.genders"
                                                    :key="value">{{ value }}</option>
                                            </select>
                                        </Field>
                                    </div>
                                    <div class="form-group">
                                        <label for="rh" class="form-label">Grupo sanguíneo<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="rh" v-slot="{ field, errorMessage, meta }">
                                            <select v-bind="field" id="rh" class="form-select form-select-sm"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                                <option :value="key" v-for="value, key in settings.blood_types"
                                                    :key="value">{{ value }}
                                                </option>
                                            </select>
                                        </Field>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </Step>

                    <Step title="Información general">
                        <fieldset class="col-md-12 p-3">
                            <legend>General</legend>
                            <div class="row col-md-12">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Correo Electrónico" name="email" :is-required="true" />
                                    </div>
                                    <div class="form-group">
                                        <inputField label="Entidad de salud" name="eps" :is-required="true" />
                                    </div>

                                    <div class="form-group">
                                        <inputField label="Direccion de residencia" name="address" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Municipio de residencia" name="municipality" :is-required="true" />
                                    </div>
                                    <div class="form-group">
                                        <inputField label="Barrio de residencia" name="neighborhood" :is-required="true" />
                                    </div>
                                    <div class="form-group">
                                        <inputField label="# Teléfonicos/Celular" name="phones" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Institución educativa" name="school" :is-required="true" />
                                    </div>
                                    <div class="form-group">
                                        <label for="degree" class="form-label">Grado que cursa</label>
                                        <Field name="degree" v-slot="{ field, errorMessage, meta }">
                                            <select v-bind="field" id="degree" class="form-select form-select-sm"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                                <option value="Preescolar">Preescolar</option>
                                                <option :value="value" v-for="value in degrees" :key="value">{{ value }}</option>
                                            </select>
                                        </Field>

                                    </div>
                                    <div class="form-group">
                                        <label for="jornada" class="form-label">Jornada de estudio</label>
                                        <Field name="jornada"  v-slot="{ field, errorMessage, meta }">
                                            <select v-bind="field" id="jornada" class="form-select form-select-sm"
                                                :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                                <option :value="value" v-for="value in ['Mañana', 'Tarde']" :key="value">{{
                                                value }}</option>
                                            </select>
                                        </Field>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Seguro Estudiantil" name="student_insurance" />
                                    </div>
                                    <div class="form-group">
                                        <label for="medical_history" class="form-label">Antecedentes
                                            Médicos</label>
                                        <Field name="medical_history" v-slot="{ field, errorMessage, meta }">
                                            <textarea v-bind="field" id="medical_history" class="form-control form-control-sm" rows="5" :class="{ 'is-invalid': !meta.valid && errorMessage }"></textarea>
                                        </Field>
                                    </div>

                                </div>

                            </div>
                        </fieldset>
                    </Step>

                    <Step title="Información familiar">
                        <fieldset class="col-md-12 p-3" v-for="value, key in parients" :key="`${value}_${key}`">
                            <legend>
                                Parentesco:
                                <Field :name="`relationship_${key}`"  v-slot="{ field, errorMessage, meta }">
                                    <select v-bind="field" :id="`relationship_${key}`" class="form-select form-select-sm"
                                        :class="{ 'is-invalid': !meta.valid && errorMessage }">
                                        <option :value="key" v-for="value, key in settings.relationships">{{
                                        value }}</option>
                                    </select>
                            </Field>
                            </legend>
                            <div class="row col-md-12">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Nombres completos" :name="`names_${key}`" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="# Doc de identidad" :name="`document_${key}`" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="# WhatsApp ó télefono" :name="`phone_${key}`" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Ocupación" :name="`business_${key}`" />
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </Step>

                </Wizard>
            </Form>
        </template>
    </panel>
    <breadcrumb :parent="'Plataforma'" :current="currentTextPlayer" />
</template>
<script setup>
import { Form, Field } from 'vee-validate'
import flatPickr from 'vue-flatpickr-component';
import Loader from '@/components/general/Loader';
import 'vue3-form-wizard/dist/style.css'
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";
import usePlayerDetail from '@/composables/player/playerDetail'

const { globalError, onSubmit, wizardOptions, currentTextPlayer, step, initialValues, flatpickrConfig, settings, schema, degrees, parients, loadingText, isLoading, formErrorSummary, hasGeneralErrors, goToStep } = usePlayerDetail()

</script>
