<template>
    <panel>
        <template #lateral />
        <template #body>
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
                                        <inputFileImage label="Foto JPG, JPEG, PNG" name="photo" />
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
                                        <Field name="date_birth" v-slot="{ field }">
                                            <flat-pickr v-bind="field" v-model="field.value" :config="flatpickrConfig"
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
                                        <label for="document_type" class="form-label">Tipo Documento<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="document_type" as="select" id="document_type"
                                            class="form-select form-select-sm">
                                            <option :value="key" v-for="value, key in settings.document_types"
                                                :key="value">
                                                {{ value }}</option>
                                        </Field>
                                        <ErrorMessage name="document_type" class="custom-error" />
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
                                        <Field name="gender" as="select" id="gender" class="form-select form-select-sm">
                                            <option :value="key" v-for="value, key in settings.genders" :key="value">{{
                                                value }}</option>
                                        </Field>
                                        <ErrorMessage name="gender" class="custom-error" />
                                    </div>
                                    <div class="form-group">
                                        <label for="rh" class="form-label">Grupo sanguíneo<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="rh" as="select" id="rh" class="form-select form-select-sm">
                                            <option :value="key" v-for="value, key in settings.blood_types"
                                                :key="value">{{ value }}
                                            </option>
                                        </Field>
                                        <ErrorMessage name="rh" class="custom-error"/>
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
                                        <inputField label="Entidad de salud" name="eps" />
                                    </div>

                                    <div class="form-group">
                                        <inputField label="Direccion de residencia" name="address" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Municipio de residencia" name="municipality" />
                                    </div>
                                    <div class="form-group">
                                        <inputField label="Barrio de residencia" name="neighborhood" />
                                    </div>
                                    <div class="form-group">
                                        <inputField label="# Teléfonicos/Celular" name="phones" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Institución educativa" name="school" />
                                    </div>
                                    <div class="form-group">
                                        <label for="degree" class="form-label">Grado que cursa</label>
                                        <Field name="degree" as="select" id="degree" class="form-select form-select-sm">
                                            <option value="Preescolar">Preescolar</option>
                                            <option :value="value" v-for="value in degrees" :key="value">{{
                                                value }}</option>
                                        </Field>
                                        <ErrorMessage name="degree" class="custom-error" />
                                    </div>
                                    <div class="form-group">
                                        <label for="jornada" class="form-label">Jornada de estudio</label>
                                        <Field name="jornada" as="select" id="jornada"
                                            class="form-select form-select-sm">
                                            <option :value="value" v-for="value in ['Mañana', 'Tarde']" :key="value">{{
                                                value }}</option>
                                        </Field>
                                        <ErrorMessage name="jornada" class="custom-error" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <inputField label="Seguro Estudiantil" name="student_insurance" />
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
                    </Step>

                    <Step title="Información familiar">
                        <fieldset class="col-md-12 p-3" v-for="value, key in parients" :key="`${value}_${key}`">
                            <legend>
                                Parentesco:
                                <Field :name="`relationship_${key}`" :id="`relationship_${key}`" as="select"
                                    class="form-select form-select-sm">
                                    <option :value="key" v-for="value, key in settings.relationships">{{
                                        value }}</option>
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
import { Form, Field, ErrorMessage } from 'vee-validate'
import flatPickr from 'vue-flatpickr-component';
import Loader from '@/components/general/Loader';
import 'vue3-form-wizard/dist/style.css'
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";
import usePlayerDetail from '@/composables/player/playerDetail'

const { onSubmit, wizardOptions, currentTextPlayer, step, initialValues, flatpickrConfig, settings, schema, degrees, parients, loadingText, isLoading } = usePlayerDetail()

</script>