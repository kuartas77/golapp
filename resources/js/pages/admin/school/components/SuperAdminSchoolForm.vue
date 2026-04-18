<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">{{ title }}</h3>
                    <p class="text-muted mb-0">
                        {{ description }}
                    </p>
                </div>
                <div class="small text-muted text-lg-end">
                    El logo se procesa con la misma experiencia visual del perfil de escuela y el grupo de sedes se sincroniza en todas las vinculadas.
                </div>
            </div>
        </template>
        <template #body>
            <div v-if="isLoading" class="py-5 text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Cargando formulario de escuela...</p>
            </div>

            <Form
                v-else
                ref="form"
                v-slot="{ values, setFieldValue }"
                :validation-schema="schema"
                :initial-values="initialValues"
                @submit="submit"
            >
                <div v-if="globalError" class="alert alert-danger">
                    {{ globalError }}
                </div>

                <div class="row g-4 align-items-start">
                    <div class="col-12 col-xl-3 text-center">
                        <div class="form-group mb-0">
                            <inputFileImage label="Logo de la escuela" name="logo" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <inputField
                                        label="Nombre Escuela"
                                        name="name"
                                        :readonly="isEditMode"
                                        :is-required="true"
                                    />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <inputField
                                        label="Correo Escuela"
                                        name="email"
                                        type="email"
                                        :readonly="isEditMode"
                                        :is-required="true"
                                    />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <inputField label="Representante" name="agent" :is-required="true" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <inputField label="Teléfono(s)" name="phone" :is-required="true" />
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <inputField label="Dirección" name="address" :is-required="true" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <Field name="is_enable" v-slot="{ field, handleChange, handleBlur }">
                                        <label class="form-label">Estado <span class="text-danger">&nbsp;(*)</span></label>
                                        <select
                                            class="form-select form-select-sm"
                                            v-bind="field"
                                            @change="handleChange"
                                            @blur="handleBlur"
                                        >
                                            <option value="1">Activa</option>
                                            <option value="0">Inactiva</option>
                                        </select>
                                    </Field>
                                    <ErrorMessage name="is_enable" class="custom-error" as="div" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 p-lg-4 mt-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Sedes y escuela administradora</h5>
                            <p class="text-muted mb-0">
                                Activa esta opción cuando la misma administración deba poder alternar entre varias escuelas relacionadas.
                            </p>
                        </div>
                        <span class="badge text-bg-light align-self-start align-self-lg-center">
                            MULTIPLE_SCHOOLS
                        </span>
                    </div>

                    <Field name="is_campus" v-slot="{ value, handleChange }">
                        <div class="form-check form-switch mb-3">
                            <input
                                id="is_campus"
                                class="form-check-input"
                                type="checkbox"
                                :checked="Boolean(value)"
                                @change="(event) => onCampusChange(event, handleChange, setFieldValue)"
                            >
                            <label class="form-check-label fw-semibold" for="is_campus">
                                Es sede / comparte administración con otras escuelas
                            </label>
                        </div>
                    </Field>

                    <div v-if="values.is_campus" class="form-group">
                        <label class="form-label" for="multiple_schools">
                            Escuelas relacionadas <span class="text-danger">&nbsp;(*)</span>
                        </label>
                        <Field name="multiple_schools" v-slot="{ field, handleChange }">
                            <CustomSelect2
                                id="multiple_schools"
                                :modelValue="field.value"
                                :options="schoolOptions"
                                :multiple="true"
                                placeholder="Selecciona una o varias escuelas"
                                search-placeholder="Buscar escuela..."
                                @update:modelValue="handleChange"
                            />
                        </Field>
                        <ErrorMessage name="multiple_schools" class="custom-error mt-2" as="div" />
                        <small class="text-muted d-block mt-2">
                            La relación se guardará como un grupo completo y se sincronizará automáticamente en todas las escuelas seleccionadas.
                        </small>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" :disabled="isSaving" @click="onCancel">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="isSaving">
                        {{ submitLabel }}
                    </button>
                </div>
            </Form>
        </template>
    </panel>

    <breadcrumb :parent="'Adminstración'" :current="title" />
</template>

<script setup>
import { ErrorMessage, Field, Form } from 'vee-validate'
import useSuperAdminSchoolForm from '@/composables/admin/school/superAdminSchoolForm'

const props = defineProps({
    mode: {
        type: String,
        default: 'create',
    },
})

const {
    description,
    form,
    globalError,
    initialValues,
    isEditMode,
    isLoading,
    isSaving,
    onCancel,
    schoolOptions,
    schema,
    submit,
    submitLabel,
    title,
} = useSuperAdminSchoolForm(props.mode)

const onCampusChange = (event, handleChange, setFieldValue) => {
    handleChange(event)

    if (!event.target.checked) {
        setFieldValue('multiple_schools', [])
    }
}
</script>
