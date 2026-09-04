<template>
    <panel>
        <template #header>
            <AppPageHeader
                :title="`Ítems de ${documentPlural.toLowerCase()}`"
                :subtitle="`Consulta los conceptos incluidos en ${documentPlural.toLowerCase()} de la escuela.`"
                icon="fa fa-list-check"
                data-tour="invoice-items-filters"
            >
                <template #actions>
                    <a
                        :href="exportUrl"
                        class="btn btn-info btn-sm"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="fa fa-print me-2" aria-hidden="true"></i>
                        Exportar pendientes en PDF
                    </a>
                    <AppButton variant="info" size="sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                        Guía
                    </AppButton>
                </template>
            </AppPageHeader>
        </template>
        <template #body>
            <div class="table-responsive-md" data-tour="invoice-items-table">
                <ContentState
                    v-if="globalError"
                    type="error"
                    title="No fue posible cargar los ítems de los documentos"
                    :message="globalError"
                    action-label="Reintentar"
                    class="mb-3"
                    @action="reloadTable"
                />
                <div v-show="!globalError">
                <DatatableTemplate
                    id="invoice_items_table"
                    :aria-label="`Ítems de ${documentPlural.toLowerCase()}`"
                    ref="invoice_items_table"
                    :options="options"
                >
                    <template #thead>
                        <thead class="align-middle">
                            <tr>
                                <th>{{ documentSingular }}</th>
                                <th>Creado</th>
                                <th>Deportista</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Método Pago</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Totales de esta página:</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-end"></th>
                                <th class="text-end"></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </template>
                </DatatableTemplate>
                </div>
            </div>
        </template>
    </panel>

    <PageTutorialOverlay :tutorial="tutorial" />
    <breadcrumb :parent="'Plataforma'" :current="`Ítems de ${documentPlural.toLowerCase()}`" />
</template>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue';
import ContentState from '@/components/general/ContentState.vue';
import AppButton from '@/components/general/AppButton.vue';
import AppPageHeader from '@/components/general/AppPageHeader.vue';
import useInvoiceItemsList from '@/composables/invoices/invoiceItemsList';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue';
import { usePageTutorial } from '@/composables/usePageTutorial';
import { invoiceItemsTutorial } from '@/tutorials/invoices';
import { computed } from 'vue';
import { useAuthUser } from '@/store/auth-user';
import { invoiceDocumentPlural, invoiceDocumentSingularForSchool } from '@/utils/invoiceTerminology';

const auth = useAuthUser();
const documentPlural = computed(() => invoiceDocumentPlural(Boolean(auth.user?.electronic_invoicing_enabled)));
const documentSingular = computed(() => invoiceDocumentSingularForSchool(Boolean(auth.user?.electronic_invoicing_enabled)));
const { options, globalError, reloadTable } = useInvoiceItemsList();
const tutorial = usePageTutorial(invoiceItemsTutorial);

const exportUrl = '/api/v2/invoices/items/invoices/export-pending';
</script>
