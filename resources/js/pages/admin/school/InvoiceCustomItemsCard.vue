<template>
    <div class="card border h-100 invoice-custom-items-card">
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h5 class="mb-1">Items personalizados de facturación</h5>
                    <p class="text-muted small mb-0">
                        Se agregan por defecto a las facturas. Excepto en "Otro", sólo puede existir un item activo por tipo.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn btn-info btn-sm flex-shrink-0"
                    :disabled="isLoading || !itemTypeOptions.length"
                    @click="openCreateModal"
                >
                    <i class="fa fa-plus me-1" aria-hidden="true"></i>
                    Agregar
                </button>
            </div>

            <div v-if="listError" class="alert alert-danger py-2" role="alert">
                {{ listError }}
            </div>

            <div v-if="isLoading" class="py-5 text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="text-muted mb-0">Cargando items personalizados...</p>
            </div>

            <template v-else>
                <div v-if="items.length" class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Precio</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td>
                                    <div class="fw-semibold">{{ item.name }}</div>
                                    <small class="text-muted">{{ resolveTypeLabel(item.type) }}</small>
                                </td>
                                <td>{{ moneyFormat(Number(item.unit_price)) }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            :disabled="isDeletingId === item.id"
                                            @click="openEditModal(item)"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            :disabled="isDeletingId === item.id"
                                            @click="confirmDelete(item)"
                                        >
                                            <span v-if="isDeletingId === item.id" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="border rounded-3 p-4 text-center text-muted h-100 d-flex align-items-center justify-content-center">
                    Todavía no hay items personalizados para facturación.
                </div>
            </template>
        </div>
    </div>

    <div ref="modalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="submitForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">
                                {{ isEditMode ? 'Editar item personalizado' : 'Nuevo item personalizado' }}
                            </h5>
                            <small class="text-muted">Este item se agregará por defecto a nuevas facturas.</small>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Cerrar"
                            :disabled="isSaving"
                            @click="closeModal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            En los tipos predefinidos, el nombre se completa automáticamente para mantener consistencia con las solicitudes de uniforme.
                        </div>

                        <div v-if="formMessage" class="alert alert-danger" role="alert">
                            {{ formMessage }}
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="invoice-custom-item-type">Item</label>
                                <select
                                    id="invoice-custom-item-type"
                                    v-model="form.item_type"
                                    class="form-select"
                                    :class="{ 'is-invalid': formErrors.item_type }"
                                    :disabled="isSaving"
                                    @change="handleTypeChange"
                                >
                                    <option value="">Selecciona...</option>
                                    <option v-for="[value, label] in itemTypeOptions" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <div v-if="formErrors.item_type" class="invalid-feedback">
                                    {{ formErrors.item_type }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="invoice-custom-item-name">Nombre</label>
                                <input
                                    id="invoice-custom-item-name"
                                    v-model.trim="form.item_name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': formErrors.item_name }"
                                    :readonly="isReadonlyName"
                                    :disabled="isSaving"
                                    autocomplete="off"
                                >
                                <div v-if="formErrors.item_name" class="invalid-feedback">
                                    {{ formErrors.item_name }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="invoice-custom-item-unit-price">Precio unitario</label>
                                <input
                                    id="invoice-custom-item-unit-price"
                                    :value="form.item_unit_price"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': formErrors.item_unit_price }"
                                    :disabled="isSaving"
                                    autocomplete="off"
                                    inputmode="numeric"
                                    placeholder="0"
                                    @input="handlePriceInput"
                                >
                                <div v-if="formErrors.item_unit_price" class="invalid-feedback">
                                    {{ formErrors.item_unit_price }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" :disabled="isSaving" @click="closeModal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ isEditMode ? 'Guardar cambios' : 'Guardar item' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import api from '@/utils/axios'

const props = defineProps({
    itemTypes: {
        type: Object,
        default: () => ({}),
    },
})

const items = ref([])
const isLoading = ref(true)
const listError = ref('')
const isSaving = ref(false)
const isDeletingId = ref(null)
const editingItemId = ref(null)
const modalElement = ref(null)
const formMessage = ref('')

const form = reactive({
    item_type: '',
    item_name: '',
    item_unit_price: '',
})

const formErrors = reactive({
    item_type: '',
    item_name: '',
    item_unit_price: '',
})

let modalInstance = null

const CURRENCY_INPUT_FORMATTER = new Intl.NumberFormat('es-CO')
const DATE_FORMATTER = new Intl.DateTimeFormat('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
})

const isEditMode = computed(() => editingItemId.value !== null)
const itemTypeOptions = computed(() => Object.entries(props.itemTypes ?? {}))
const isReadonlyName = computed(() => form.item_type !== '' && form.item_type !== 'OTHER')

const resetErrors = () => {
    formMessage.value = ''
    formErrors.item_type = ''
    formErrors.item_name = ''
    formErrors.item_unit_price = ''
}

const resetForm = () => {
    editingItemId.value = null
    form.item_type = ''
    form.item_name = ''
    form.item_unit_price = ''
    resetErrors()
}

const resolveTypeLabel = (type) => props.itemTypes?.[type] ?? type

const normalizeAmount = (value) => Number(String(value ?? '').replace(/\D/g, ''))

const formatDigitsAsCurrency = (value) => {
    const amount = normalizeAmount(value)
    return amount > 0 ? CURRENCY_INPUT_FORMATTER.format(amount) : ''
}

const formatStoredAmount = (value) => {
    const amount = Math.round(Number(value))
    return Number.isFinite(amount) && amount > 0 ? CURRENCY_INPUT_FORMATTER.format(amount) : ''
}

const formatDate = (value) => {
    if (!value) {
        return 'N/D'
    }

    const parsedDate = new Date(value)
    return Number.isNaN(parsedDate.getTime()) ? 'N/D' : DATE_FORMATTER.format(parsedDate)
}

const fetchItems = async () => {
    isLoading.value = true
    listError.value = ''

    try {
        const { data } = await api.get('/api/v2/admin/invoice-items-custom')
        items.value = Array.isArray(data) ? data : data.data ?? []
    } catch (error) {
        listError.value = error.response?.data?.message || 'No fue posible cargar los items personalizados.'
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    resetForm()
    modalInstance?.show()
}

const openEditModal = (item) => {
    resetForm()
    editingItemId.value = item.id
    form.item_type = item.type
    form.item_name = item.name
    form.item_unit_price = formatStoredAmount(item.unit_price)
    modalInstance?.show()
}

const closeModal = () => {
    modalInstance?.hide()
}

const handleTypeChange = () => {
    formErrors.item_type = ''
    formErrors.item_name = ''

    if (form.item_type && form.item_type !== 'OTHER') {
        form.item_name = resolveTypeLabel(form.item_type)
        return
    }

    if (form.item_type === 'OTHER') {
        form.item_name = ''
        return
    }

    form.item_name = ''
}

const handlePriceInput = (event) => {
    formErrors.item_unit_price = ''
    form.item_unit_price = formatDigitsAsCurrency(event.target.value)
}

const validateForm = () => {
    resetErrors()

    if (!form.item_type) {
        formErrors.item_type = 'Selecciona un tipo de item.'
    }

    if (!form.item_name.trim()) {
        formErrors.item_name = 'Ingresa el nombre del item.'
    }

    if (normalizeAmount(form.item_unit_price) <= 0) {
        formErrors.item_unit_price = 'Ingresa un precio unitario válido.'
    }

    return !formErrors.item_type && !formErrors.item_name && !formErrors.item_unit_price
}

const applyBackendErrors = (error) => {
    const backendErrors = error.response?.data?.errors ?? {}
    const keyMap = {
        type: 'item_type',
        name: 'item_name',
        unit_price: 'item_unit_price',
        item_type: 'item_type',
        item_name: 'item_name',
        item_unit_price: 'item_unit_price',
    }

    Object.entries(backendErrors).forEach(([backendKey, messages]) => {
        const targetKey = keyMap[backendKey]

        if (!targetKey) {
            return
        }

        formErrors[targetKey] = Array.isArray(messages) ? messages[0] : messages
    })

    if (!Object.values(formErrors).some(Boolean)) {
        formMessage.value = error.response?.data?.message || 'No fue posible guardar el item personalizado.'
    }
}

const submitForm = async () => {
    if (!validateForm()) {
        return
    }

    isSaving.value = true

    const payload = {
        item_type: form.item_type,
        item_name: form.item_name.trim(),
        item_unit_price: normalizeAmount(form.item_unit_price),
    }

    try {
        if (isEditMode.value) {
            const { data } = await api.put(`/api/v2/admin/invoice-items-custom/${editingItemId.value}`, payload)
            showMessage(data.message || 'Item personalizado actualizado correctamente.')
        } else {
            const { data } = await api.post('/api/v2/admin/invoice-items-custom', payload)
            showMessage(data.message || 'Item personalizado creado correctamente.')
        }

        closeModal()
        await fetchItems()
    } catch (error) {
        resetErrors()
        applyBackendErrors(error)
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = async (item) => {
    const result = await window.Swal.fire({
        title: '¿Eliminar item personalizado?',
        text: `Se eliminará "${item.name}" del listado por defecto de facturación.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    })

    if (!result.isConfirmed) {
        return
    }

    isDeletingId.value = item.id

    try {
        await api.delete(`/api/v2/admin/invoice-items-custom/${item.id}`)
        items.value = items.value.filter((currentItem) => currentItem.id !== item.id)
        showMessage('Item personalizado eliminado correctamente.')
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible eliminar el item personalizado.', 'error')
    } finally {
        isDeletingId.value = null
    }
}

const handleModalHidden = () => {
    resetForm()
}

onMounted(() => {
    if (modalElement.value) {
        modalInstance = new window.bootstrap.Modal(modalElement.value, {
            backdrop: 'static',
            keyboard: false,
        })

        modalElement.value.addEventListener('hidden.bs.modal', handleModalHidden)
    }

    fetchItems()
})

onBeforeUnmount(() => {
    modalElement.value?.removeEventListener('hidden.bs.modal', handleModalHidden)
})
</script>

<style scoped>
.invoice-custom-items-card {
    min-height: 100%;
}

.invoice-custom-items-card td:first-child {
    min-width: 220px;
}
</style>
