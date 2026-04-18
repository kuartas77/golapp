<template>
    <panel>
        <template #body>
            <div class="row g-4 align-items-start">
                <div :class="canBilling ? 'col-12 col-xl-7 col-xxl-8' : 'col-12 col-xxl-9 mx-auto'">
                    <Form
                        ref="form"
                        v-slot="{ values }"
                        :validation-schema="schema"
                        :initial-values="formData"
                        @submit="submit"
                    >
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
                                    <inputField label="Nombre Escuela" name="name" />
                                </div>
                                <div class="form-group">
                                    <inputField label="Correo Escuela" type="email" name="email" readonly="true" />
                                </div>
                                <div class="form-group">
                                    <inputField label="Representante" name="agent" />
                                </div>
                            </div>

                            <div class="col-md-4" data-tour="admin-school-brand">
                                <div class="form-group">
                                    <inputField label="Dirección" name="address" />
                                </div>
                                <div class="form-group">
                                    <inputField label="Teléfono(s)" name="phone" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6>Configuraciones</h6>
                            <hr>
                        </div>

                        <div class="row g-4" data-tour="admin-school-settings">
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <inputField label="Precio de la Matricula" name="INSCRIPTION_AMOUNT" :currency="true" />
                                        </div>
                                        <div class="form-group">
                                            <inputField label="Precio de la Mensualidad" name="MONTHLY_PAYMENT" :currency="true" />
                                        </div>
                                        <div class="form-group">
                                            <inputField label="Precio Mensualidad Hermano" name="BROTHER_MONTHLY_PAYMENT" :currency="true" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <inputField label="Precio de la Anualidad / Mes" name="ANNUITY" :currency="true" />
                                        </div>
                                        <div class="form-group">
                                            <inputField label="Día de notificación" type="number" name="NOTIFY_PAYMENT_DAY" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6" data-tour="admin-school-flags">
                                <div class="row">
                                    <div class="col-md-6">
                                        <checkbox label="Inscripciones habilitadas" name="inscriptions_enabled" v-tooltip.top="'Habilita las inscripciones por medio del enlace.'" />
                                        <checkbox label="Creación de contratos?" name="create_contract" disabled="disabled" v-tooltip.top="'Se requiere el formato del contrato.'" />
                                        <checkbox label="Envio documentos?" name="send_documents" disabled="disabled" v-tooltip.top="'Los documentos que adjuntan en la inscripción serán enviados al correo de la escuela.'" />
                                    </div>
                                    <div class="col-md-6">
                                        <checkbox label="Plataforma Tutores?" name="tutor_platform" v-tooltip.top="'Permite el ingreso de los acudientes a la plataforma, sólo podrá ver información del deportista.'" />
                                        <checkbox label="Firma Deportistas?" name="sign_player" disabled="disabled" v-tooltip.top="'Sí firma el acudiente y el deportista ó sólo el acudiente.'" />
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

                        <div class="text-center mt-4" data-tour="admin-school-actions">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </Form>
                </div>

                <div v-if="canBilling" class="col-12 col-xl-5 col-xxl-4">
                    <InvoiceCustomItemsCard :item-types="uniformRequestTypes" />
                </div>
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Escuela'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import { Form } from 'vee-validate'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useFormSchool from '@/composables/admin/school/formSchool'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import InvoiceCustomItemsCard from './InvoiceCustomItemsCard.vue'
import { updateSchoolTutorial } from '@/tutorials/admin'

usePageTitle('Escuela')
const { form, formData, schema, submit, uniformRequestTypes } = useFormSchool()
const { access } = useBackofficeAccess()
const tutorial = usePageTutorial(updateSchoolTutorial)
const canBilling = access.billing

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
