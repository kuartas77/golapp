<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <p class="mb-0">
                    En esta página podrás encontrar los items de todas las facturas.
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
            </div>

            <div class="table-responsive-md">
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
                                <th>Totales:</th>
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
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Items Facturas'" />
</template>

<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue';
import useInvoiceItemsList from '@/composables/invoices/invoiceItemsList';

const { options } = useInvoiceItemsList();

const exportUrl = '/api/v2/invoices/items/invoices/export-pending';
</script>
