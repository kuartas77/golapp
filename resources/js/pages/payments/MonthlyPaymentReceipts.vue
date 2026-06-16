<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Recibos de mensualidad</h4>
                    <p class="text-muted mb-0">Consulta mensualidades pagadas y descarga el recibo individual.</p>
                </div>
                <router-link :to="{ name: 'payments' }" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i>
                    Mensualidades
                </router-link>
            </div>

            <Form :validation-schema="schema" :initial-values="formData" class="row align-items-end" @submit="searchReceipts">
                <div class="col-xl-2 col-lg-3 col-sm-6 mb-2">
                    <label for="receipt_unique_code" class="form-label">Codigo unico</label>
                    <Field
                        id="receipt_unique_code"
                        name="unique_code"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Ej: 20190000"
                    />
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6 mb-2">
                    <label for="receipt_player_name" class="form-label">Deportista</label>
                    <Field
                        id="receipt_player_name"
                        name="player_name"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Nombre o apellido"
                    />
                </div>
                <div class="col-xl-2 col-lg-3 col-sm-6 mb-2">
                    <label for="receipt_training_group_id" class="form-label">Grupo</label>
                    <Field
                        id="receipt_training_group_id"
                        name="training_group_id"
                        as="CustomSelect2"
                        :options="groups"
                        placeholder="Grupo"
                    />
                </div>
                <div class="col-xl-2 col-lg-2 col-sm-6 mb-2">
                    <label for="receipt_category" class="form-label">Categoria</label>
                    <Field
                        id="receipt_category"
                        name="category"
                        as="CustomSelect2"
                        :options="categories"
                        placeholder="Categoria"
                    />
                </div>
                <div class="col-xl-1 col-lg-2 col-sm-6 mb-2">
                    <label for="receipt_year" class="form-label">Año</label>
                    <Field
                        id="receipt_year"
                        name="year"
                        as="CustomSelect2"
                        :options="years"
                        placeholder="Año"
                    />
                    <ErrorMessage name="year" class="custom-error" />
                </div>
                <div class="col-xl-2 col-lg-2 col-sm-12 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100" :disabled="isLoading">
                        <i class="fa fa-search me-1"></i>
                        Buscar recibos
                    </button>
                </div>
            </Form>

            <hr class="bg-primary border-2 border-top border-primary" />

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Mostrando {{ receiptCount }} recibos.</span>
                <span class="badge outline-badge-info">Total página {{ moneyFormat(totalAmount) }}</span>
            </div>

            <div class="table-responsive-md">
                <DatatableTemplate
                    id="monthly-payment-receipts-table"
                    ref="receiptsTable"
                    :options="tableOptions"
                >
                    <template #thead>
                        <thead>
                            <tr>
                                <th>Deportista</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Estado</th>
                                <th class="text-end">Valor</th>
                                <th class="text-center">PDF</th>
                            </tr>
                        </thead>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Mensualidades'" :current="'Recibos'" />
</template>

<script setup>
import { computed, onMounted, reactive, ref, useTemplateRef } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
import * as yup from 'yup'
import api from '@/utils/axios'
import { useSetting } from '@/store/settings-store'
import { usePageTitle } from '@/composables/use-meta'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import configLanguaje from '@/utils/datatableUtils'

const currentYear = new Date().getFullYear()
const settings = useSetting()
const groups = computed(() => settings.groups
    .filter((group) => group.name !== 'Provisional')
    .map((group) => ({ value: group.id, label: group.full_group })))
const categories = computed(() => settings.categories.map((item) => ({ value: item.category, label: item.category })))
const years = computed(() => settings.inscription_years)
const defaultYear = computed(() => years.value.find((year) => Number(year.value) === currentYear)?.value
    ?? years.value[years.value.length - 1]?.value
    ?? currentYear)

const schema = yup.object().shape({
    year: yup.mixed().required(),
    unique_code: yup.string().nullable().optional(),
    player_name: yup.string().nullable().optional(),
    training_group_id: yup.mixed().nullable().optional(),
    category: yup.string().nullable().optional(),
})

const formData = computed(() => ({
    year: defaultYear.value,
    unique_code: '',
    player_name: '',
    training_group_id: null,
    category: null,
}))

const receiptCount = ref(0)
const currentPageAmount = ref(0)
const isLoading = ref(false)
const receiptsTable = useTemplateRef('receiptsTable')
const filters = reactive({
    year: null,
    unique_code: null,
    player_name: null,
    training_group_id: null,
    category: null,
})
const totalAmount = computed(() => currentPageAmount.value)
const formatMoney = (amount) => window.moneyFormat ? window.moneyFormat(Number(amount) || 0) : amount

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const normalizeFilters = (values) =>
    Object.entries(values).reduce((params, [key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
            params[key] = value
        }

        return params
    }, {})

const tableOptions = computed(() => ({
    ...configLanguaje,
    lengthMenu: [[10, 30, 50, 100], [10, 30, 50, 100]],
    pageLength: 10,
    processing: true,
    serverSide: true,
    pipeline: { pages: 5 },
    deferRender: true,
    searching: false,
    searchDelay: 400,
    order: [[0, 'asc'], [2, 'asc']],
    ajax: async (data, callback) => {
        isLoading.value = true

        try {
            const response = await api.get('/api/v2/payments/monthly-receipts', {
                params: {
                    ...data,
                    ...normalizeFilters(filters),
                },
                skipGlobalLoader: true,
            })

            const payload = response.data
            receiptCount.value = payload.recordsFiltered ?? 0
            currentPageAmount.value = (payload.data ?? []).reduce((sum, receipt) => sum + Number(receipt.amount || 0), 0)
            callback({
                draw: data.draw,
                data: payload.data ?? [],
                recordsTotal: payload.recordsTotal ?? 0,
                recordsFiltered: payload.recordsFiltered ?? 0,
            })
        } catch (error) {
            receiptCount.value = 0
            currentPageAmount.value = 0
            showMessage(error.response?.data?.message || 'No fue posible consultar los recibos', 'error')
            callback(emptyDataTableResponse(data.draw))
        } finally {
            isLoading.value = false
        }
    },
    columnDefs: [
        { targets: [4], className: 'dt-body-right' },
        { targets: [5], className: 'dt-body-center' },
    ],
    columns: [
        {
            data: 'player_name',
            name: 'player_name',
            render: (data, type, row) => {
                if (type !== 'display') {
                    return `${data} ${row.unique_code} ${row.training_group || ''} ${row.category || ''}`
                }

                const details = [
                    row.unique_code,
                    row.training_group,
                    row.category,
                ].filter(Boolean).join(' | ')

                return `
                    <div>
                        <div class="fw-semibold">${data}</div>
                        <small class="text-muted">${details}</small>
                    </div>
                `
            },
        },
        { data: 'month_label', name: 'month_order' },
        { data: 'year', name: 'year' },
        {
            data: 'status_label',
            name: 'status',
            render: (data, type, row) => `<span class="badge payments-c-${row.status}">${data}</span>`,
        },
        {
            data: 'amount',
            name: 'amount',
            render: (data) => formatMoney(data),
        },
        {
            data: 'pdf_url',
            name: 'payment_id',
            orderable: false,
            searchable: false,
            render: (data) => `<a href="${data}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" title="Abrir recibo"><i class="far fa-file-pdf"></i></a>`,
        },
    ],
}))

const searchReceipts = async (values) => {
    Object.assign(filters, {
        year: values.year ?? defaultYear.value,
        unique_code: values.unique_code || null,
        player_name: values.player_name || null,
        training_group_id: values.training_group_id || null,
        category: values.category || null,
    })

    const dt = receiptsTable.value?.table?.dt

    if (dt) {
        dt.clearPipeline().draw()
    }
}

onMounted(() => {
    usePageTitle('Recibos de mensualidad')
    filters.year = defaultYear.value
})
</script>
