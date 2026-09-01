<template>
    <panel>
        <template #body>
            <Form
                ref="form"
                v-slot="{ values, isSubmitting }"
                :validation-schema="schema"
                :initial-values="formData"
                @submit="submit"
            >
                <div v-if="globalError" class="alert alert-danger" role="alert">
                    {{ globalError }}
                </div>

                <div v-if="isReadOnly" class="alert alert-info" role="status">
                    Estás consultando la configuración de la escuela en modo de solo lectura.
                </div>

                <fieldset :disabled="isReadOnly" class="border-0 p-0 m-0">

                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>

                <div class="row g-3 align-items-start">
                    <div class="col-md-4 text-center" data-tour="admin-school-brand">
                        <div class="form-group">
                            <inputFileImage label="Logo" name="logo" />
                        </div>
                    </div>

                    <div class="col-md-4" data-tour="admin-school-brand">
                        <div class="form-group">
                            <inputField label="Nombre Escuela" name="name" :is-required="true" />
                        </div>
                        <div class="form-group">
                            <inputField label="Correo Escuela" type="email" name="email" readonly="true" :is-required="true" />
                        </div>
                        <div class="form-group">
                            <inputField label="Representante" name="agent" :is-required="true" />
                        </div>
                    </div>

                    <div class="col-md-4" data-tour="admin-school-brand">
                        <div class="form-group">
                            <inputField label="Dirección" name="address" :is-required="true" />
                        </div>
                        <div class="form-group">
                            <inputField label="Teléfono(s)" name="phone" :is-required="true" />
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>Configuraciones</h6>
                    <hr>
                </div>

                <div class="row g-3" data-tour="admin-school-settings">
                    <div class="col-12 col-xl-7">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-3 d-flex align-items-center gap-2">
                                Precios de inscripción y mensualidades
                                <i
                                    class="fa-regular fa-circle-question text-info"
                                    v-tooltip.top="'Si Precio Mensualidad Hermano, Mensualidad 5 dias, Mensualidad 4 dias o Mensualidad 3 dias tienen un valor mayor a 0, aparecerán como opciones en la Tarifa mensual al diligenciar una inscripción. La tarifa seleccionada se usará para calcular los valores de las mensualidades.'"
                                ></i>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Precio de la Matricula/Inscripción" name="INSCRIPTION_AMOUNT" :currency="true" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Precio de la Mensualidad" name="MONTHLY_PAYMENT" :currency="true" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Precio Mensualidad Hermano" name="BROTHER_MONTHLY_PAYMENT" :currency="true" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Mensualidad 5 días" name="MONTHLY_PAYMENT_OPTION_1" :currency="true" :is-required="!values.training_group_monthly_payment_enabled" :disabled="values.training_group_monthly_payment_enabled" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Mensualidad 4 días" name="MONTHLY_PAYMENT_OPTION_2" :currency="true" :is-required="!values.training_group_monthly_payment_enabled" :disabled="values.training_group_monthly_payment_enabled" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group">
                                        <inputField label="Mensualidad 3 días" name="MONTHLY_PAYMENT_OPTION_3" :currency="true" :is-required="!values.training_group_monthly_payment_enabled" :disabled="values.training_group_monthly_payment_enabled" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <div class="form-group mb-0">
                                        <inputField label="Precio de la Anualidad / Mes" name="ANNUITY" :currency="true" :is-required="true" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-5">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-3">Notificaciones de deuda</h6>
                            <div class="form-group mb-2">
                                <inputField label="Día de notificación" type="number" name="NOTIFY_PAYMENT_DAY" :is-required="true" />
                            </div>
                            <p class="small text-muted mb-0">
                                Este es el día del mes en que se enviarán por correo electrónico las notificaciones de deudas que tengan las inscripciones.
                            </p>
                        </div>
                    </div>

                    <div class="col-12" data-tour="admin-school-flags">
                        <div class="border rounded p-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <h6 class="mb-0">Opciones de plataforma</h6>
                                <span class="badge text-bg-secondary align-self-start">Solo lectura · Solicitar cambios al super-admin</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Inscripciones habilitadas" name="inscriptions_enabled" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Plataforma Tutores?" name="tutor_platform" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Firma Deportistas?" name="sign_player" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Creación de contratos?" name="create_contract" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Enviar notificaciones de deuda automáticamente" name="send_debt_notifications" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Envio documentos?" name="send_documents" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Enviar recibos de mensualidad?" name="send_monthly_payment_receipts" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Tarifa mensual por grupo" name="training_group_monthly_payment_enabled" disabled />
                                </div>
                                <div class="col-md-6 col-xl">
                                    <checkbox label="Limitar edición de instructores al mes actual" name="INSTRUCTOR_MONTH_LOCK_ENABLED" disabled />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <h6 class="mb-0">Formato de categorías deportivas</h6>
                                <span class="badge text-bg-secondary align-self-start">Solo lectura · Solicitar cambios al super-admin</span>
                            </div>
                            <div class="row align-items-start g-3">
                                <div class="col-md-5">
                                    <label for="CATEGORY_FORMAT" class="form-label">Formato</label>
                                    <Field id="CATEGORY_FORMAT" name="CATEGORY_FORMAT" as="select" class="form-select" disabled>
                                        <option value="sub_age">SUB-11</option>
                                        <option value="birth_year">CAT-2017</option>
                                    </Field>
                                </div>
                                <div class="col-md-7">
                                    <div class="alert alert-info mb-0 py-2" role="status">
                                        Este formato solo puede ser modificado por el super-admin, previa solicitud de la escuela.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="values.inscriptions_enabled && values.slug" class="border rounded-3 p-3 mt-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h6 class="mb-1">Enlace público de inscripciones</h6>
                            <a
                                :href="buildPublicInscriptionLink(values.slug)"
                                class="d-inline-block text-break"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ buildPublicInscriptionLink(values.slug) }}
                            </a>
                            <div class="small text-muted mt-1">
                                Puedes compartir este enlace con acudientes o usar el botón para copiarlo.
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            @click="copyPublicInscriptionLink(values.slug)"
                        >
                            Copiar enlace
                        </button>
                    </div>
                </div>
                </fieldset>

                <FormSubmitActions
                    v-if="!isReadOnly"
                    :submitting="isSubmitting"
                    :show-cancel="false"
                    wrapper-class="text-center mt-4"
                    data-tour="admin-school-actions"
                />
            </Form>

            <div v-if="canBilling" class="mt-4">
                <InvoiceCustomItemsCard :item-types="uniformRequestTypes" :read-only="isReadOnly" />
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Escuela'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import { Field, Form } from 'vee-validate'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useFormSchool from '@/composables/admin/school/formSchool'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import InvoiceCustomItemsCard from './InvoiceCustomItemsCard.vue'
import { updateSchoolTutorial } from '@/tutorials/admin'
import FormSubmitActions from '@/components/form/FormSubmitActions.vue'
import { useAuthUser } from '@/store/auth-user'

usePageTitle('Escuela')
const { form, formData, schema, submit, uniformRequestTypes, globalError } = useFormSchool()
const { access } = useBackofficeAccess()
const tutorial = usePageTutorial(updateSchoolTutorial)
const canBilling = access.billing
const auth = useAuthUser()
const isReadOnly = auth.isReadOnly()

const buildPublicInscriptionLink = (slug) => {
    const encodedSlug = encodeURIComponent(slug)

    return new URL(`/portal/escuelas/${encodedSlug}`, window.location.origin).toString()
}

const copyTextToClipboard = async (text) => {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text)
        return
    }

    const textarea = document.createElement('textarea')
    textarea.value = text
    textarea.setAttribute('readonly', 'readonly')
    textarea.style.position = 'fixed'
    textarea.style.left = '-9999px'
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
}

const copyPublicInscriptionLink = async (slug) => {
    try {
        await copyTextToClipboard(buildPublicInscriptionLink(slug))
        showMessage('Enlace copiado al portapapeles.')
    } catch (error) {
        showMessage('No fue posible copiar el enlace.', 'error')
    }
}

</script>
