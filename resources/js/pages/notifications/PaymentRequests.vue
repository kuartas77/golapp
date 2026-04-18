<template>
    <panel>
        <template #body>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" @click="tutorial.start()">
                    Guia
                </button>
            </div>
            <p data-tour="payment-requests-intro">Podrás encontrar todos los comprobantes de pago subidos desde la App GOLAPPLINK.</p>

            <div class="table-responsive-md" data-tour="payment-requests-table">
                <DatatableTemplate :options="options" id="payment_requests_table" ref="paymentRequestsTable"
                    @click="handleTableClick">
                    <template #thead>
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Deportista</th>
                                <th>Grupo</th>
                                <th class="text-right">Monto Factura</th>
                                <th>Enviado en</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th class="text-right">Monto Comprobante</th>
                                <th>Comprobante</th>
                                <th>Marcar cómo pagada</th>
                            </tr>
                        </thead>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Facturación'" :current="'Comprobantes de Pago'" />
    <PageTutorialOverlay :tutorial="tutorial" />

    <div ref="imageModalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Referencia: {{ selectedImage.title || 'Comprobante' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img :src="selectedImage.url" :alt="selectedImage.title || 'Comprobante de pago'"
                        class="img-fluid rounded proof-image" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, useTemplateRef } from 'vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import { paymentRequestsTutorial } from '@/tutorials/notifications'

usePageTitle('Comprobantes de Pago')

const paymentRequestsTable = useTemplateRef('paymentRequestsTable')
const imageModalElement = ref(null)
const selectedImage = ref({ url: '', title: '' })
const tutorial = usePageTutorial(paymentRequestsTutorial)

let imageModalInstance = null

const formatMoney = (amount) => window.moneyFormat ? window.moneyFormat(Number(amount) || 0) : amount

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
    order: [[0, 'desc']],
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: async (data, callback) => {
        try {
            const response = await api.get('/payment-request/invoices', { params: data })
            callback({
                data: response.data.data,
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            })
        } catch {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 })
        }
    },
    columnDefs: [
        { targets: [3, 7], className: 'dt-body-right' },
        { targets: [8, 9], className: 'dt-body-center' },
    ],
    columns: [
        {
            data: 'invoice.invoice_number',
            name: 'invoice_number',
            searchable: true,
            orderable: true,
            render: (data, type, row) => `<a href="/facturas/${row.invoice_id}" target="_blank">${data}</a>`,
        },
        {
            data: 'player.full_names',
            searchable: false,
            orderable: false,
        },
        {
            data: 'name',
            searchable: false,
            orderable: false,
        },
        {
            data: 'invoice.total_amount',
            name: 'invoices.total_amount',
            searchable: false,
            orderable: true,
            render: (data) => formatMoney(data),
        },
        {
            data: 'created_at',
            searchable: false,
            orderable: false,
            render: (data) => dayjs(data).format('DD-MM-YYYY'),
        },
        {
            data: 'payment_method',
            searchable: false,
            orderable: false,
            render: (data) => {
                if (data === 'cash') {
                    return '<span class="badge badge-success">Efectivo</span>'
                }
                if (data === 'card') {
                    return '<span class="badge badge-primary">Tarjeta</span>'
                }
                if (data === 'transfer') {
                    return '<span class="badge badge-info">Transferencia</span>'
                }

                return '<span class="badge badge-secondary">Otro</span>'
            },
        },
        {
            data: 'reference_number',
            searchable: false,
            orderable: false,
        },
        {
            data: 'amount',
            name: 'amount',
            searchable: false,
            orderable: false,
            render: (data) => formatMoney(data),
        },
        {
            data: 'id',
            searchable: false,
            orderable: false,
            render: (data, type, row) => `
                <button
                    type="button"
                    class="btn btn-sm btn-info"
                    data-action="view-proof"
                    data-image-url="${row.url_image}"
                    data-reference="${row.reference_number ?? ''}">
                    Ver
                </button>
            `,
        },
        {
            data: 'id',
            searchable: false,
            orderable: false,
            render: (data, type, row) => `
                <button
                    type="button"
                    class="btn btn-sm btn-success"
                    data-action="mark-paid"
                    data-invoice-id="${row.invoice_id}"
                    data-payment-request-id="${row.id}">
                    Pagar
                </button>
            `,
        },
    ],
}

const reloadTable = () => {
    const dt = paymentRequestsTable.value?.table?.dt

    if (dt) {
        dt.ajax.reload(null, false)
    }
}

const openImageModal = (imageUrl, reference) => {
    selectedImage.value = {
        url: imageUrl,
        title: reference,
    }
    imageModalInstance?.show()
}

const markAsPaid = async (invoiceId, paymentRequestId) => {
    const result = await Swal.fire({
        title: window.__APP_CONFIG__?.appName ?? 'GOLAPP',
        text: '¿Pagar factura?',
        icon: 'warning',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
    })

    if (!result.isConfirmed) {
        return
    }

    await api.put(`/invoice/${invoiceId}/payment-request/${paymentRequestId}`)

    await Swal.fire({
        title: window.__APP_CONFIG__?.appName ?? 'GOLAPP',
        text: 'Pago de factura realizado',
        icon: 'success',
    })

    reloadTable()
}

const handleTableClick = async (event) => {
    const button = event.target.closest('[data-action]')

    if (!button) {
        return
    }

    if (button.dataset.action === 'view-proof') {
        openImageModal(button.dataset.imageUrl, button.dataset.reference)
        return
    }

    if (button.dataset.action === 'mark-paid') {
        await markAsPaid(button.dataset.invoiceId, button.dataset.paymentRequestId)
    }
}

onMounted(() => {
    if (imageModalElement.value) {
        imageModalInstance = new window.bootstrap.Modal(imageModalElement.value)
    }
})
</script>

<style scoped>
.proof-image {
    max-height: 70vh;
    object-fit: contain;
}
</style>
