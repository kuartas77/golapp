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

                            <div class="col-md-4">
                                <div class="form-group">
                                    <inputField
                                        label="Límite de inscripciones por año"
                                        name="max_inscriptions"
                                        type="number"
                                        :is-required="true"
                                    />
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
                                @update:modelValue="(value) => onSchoolsChange(value, handleChange, setFieldValue)"
                            />
                        </Field>
                        <ErrorMessage name="multiple_schools" class="custom-error mt-2" as="div" />
                        <small class="text-muted d-block mt-2">
                            La relación se guardará como un grupo completo y se sincronizará automáticamente en todas las escuelas seleccionadas.
                        </small>
                    </div>
                </div>

                <div class="border rounded-3 p-3 p-lg-4 mt-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Opciones de plataforma</h5>
                            <p class="text-muted mb-0">
                                Controla qué funciones quedan activas para el portal público, tutores, contratos y documentos.
                            </p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Inscripciones habilitadas" name="inscriptions_enabled" v-tooltip.top="'Habilita las inscripciones por medio del enlace.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Plataforma Tutores?" name="tutor_platform" v-tooltip.top="'Permite el ingreso de los acudientes a la plataforma, sólo podrá ver información del deportista.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Firma Deportistas?" name="sign_player" v-tooltip.top="'Sí firma el acudiente y el deportista ó sólo el acudiente.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Creación de contratos?" name="create_contract" v-tooltip.top="'Se requiere el formato del contrato.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Envio documentos?" name="send_documents" v-tooltip.top="'Los documentos que adjuntan en la inscripción serán enviados al correo de la escuela.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Enviar recibos de mensualidad?" name="send_monthly_payment_receipts" v-tooltip.top="'Envía al acudiente tutor el recibo PDF cuando una mensualidad cambia a pagada.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Enviar recibos de caja al completar el pago" name="send_invoice_receipts" v-tooltip.top="'Envía al acudiente tutor el recibo de caja PDF cuando un documento interno o legacy queda totalmente pagado.'" />
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Enviar notificaciones de deuda automáticamente" name="send_debt_notifications" v-tooltip.top="'Habilita el envío automático de correos de deuda en el día configurado por la escuela.'" />
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <label class="form-label fw-semibold d-block mb-2">Tipo de tarifa mensual</label>
                                <Field name="training_group_monthly_payment_enabled" v-slot="{ value, handleChange }">
                                    <div class="d-flex flex-column flex-md-row gap-3">
                                        <label class="form-check mb-0">
                                            <input
                                                id="monthly_pricing_normal"
                                                class="form-check-input"
                                                type="radio"
                                                :checked="!Boolean(value)"
                                                @change="handleChange(false)"
                                            >
                                            <span class="form-check-label">
                                                <strong>Tarifa normal</strong>
                                                <small class="text-muted d-block">Usa las tarifas configuradas por la escuela.</small>
                                            </span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input
                                                id="monthly_pricing_training_group"
                                                class="form-check-input"
                                                type="radio"
                                                :checked="Boolean(value)"
                                                @change="handleChange(true)"
                                            >
                                            <span class="form-check-label">
                                                <strong>Tarifa por grupos de entrenamiento</strong>
                                                <small class="text-muted d-block">Cada grupo principal define la tarifa de sus nuevas inscripciones.</small>
                                            </span>
                                        </label>
                                    </div>
                                </Field>
                                <ErrorMessage name="training_group_monthly_payment_enabled" class="custom-error" as="div" />
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <checkbox label="Limitar edición de instructores al mes actual" name="instructor_monthly_edit_lock_enabled" v-tooltip.top="'Cuando está activo, los instructores sólo pueden modificar registros operativos del mes calendario actual.'" />
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 p-lg-4 mt-4">
                    <div class="row align-items-start g-3">
                        <div class="col-md-5">
                            <h5 class="mb-2">Formato de categorías deportivas</h5>
                            <label for="category_format" class="form-label">Formato</label>
                            <Field id="category_format" name="category_format" as="select" class="form-select">
                                <option value="sub_age">SUB-11</option>
                                <option value="birth_year">CAT-2017</option>
                            </Field>
                            <ErrorMessage name="category_format" class="custom-error" as="div" />
                        </div>
                        <div class="col-md-7">
                            <div class="alert alert-warning mb-0" role="alert">
                                Al cambiar el formato se convertirán las categorías existentes de deportistas, inscripciones y grupos de esta escuela.
                            </div>
                        </div>
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

const onSchoolsChange = (value, handleChange, setFieldValue) => {
    handleChange(value)

    if (!Array.isArray(value) || value.length === 0) {
        setFieldValue('is_campus', false)
    }
}
</script>
