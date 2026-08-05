<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3" data-tour="invoice-items-filters">
                <p class="mb-0">
                    Consulta aquí los ítems incluidos en las facturas de la escuela.
                </p>
                <a
                    :href="exportUrl"
                    class="btn btn-info btn-sm align-self-md-start"
                    target="_blank"
                    rel="noopener"
                >
                    <i class="fa fa-print me-2" aria-hidden="true"></i>
                    Exportar pendientes en PDF
                </a>
                <button type="button" class="btn btn-info btn-sm align-self-md-start" @click="tutorial.start()"><i class="fa-regular fa-circle-question me-2"></i>Guía</button>
            </div>

            <div class="table-responsive-md" data-tour="invoice-items-table">
                <ContentState
                    v-if="globalError"
                    type="error"
                    title="No fue posible cargar los ítems facturados"
                    :message="globalError"
                    action-label="Reintentar"
                    class="mb-3"
                    @action="reloadTable"
                />
                <div v-show="!globalError">
                <DatatableTemplate
                    id="invoice_items_table"
                    ref="invoice_items_table"
                    :options="options"
                >
                    <template #thead>
                        <thead class="align-middle">
                            <tr>
                                <th>Factura</th>
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
    <breadcrumb :parent="'Plataforma'" :current="'Ítems de factura'" />
</template>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue';
import ContentState from '@/components/general/ContentState.vue';
import useInvoiceItemsList from '@/composables/invoices/invoiceItemsList';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue';
import { usePageTutorial } from '@/composables/usePageTutorial';
import { invoiceItemsTutorial } from '@/tutorials/invoices';

const { options, globalError, reloadTable } = useInvoiceItemsList();
const tutorial = usePageTutorial(invoiceItemsTutorial);

const exportUrl = '/api/v2/invoices/items/invoices/export-pending';
</script>
