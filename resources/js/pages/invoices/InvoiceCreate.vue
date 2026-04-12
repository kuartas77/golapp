<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fa fa-file-invoice"></i> Crear Factura</h4>
                        </div>

                        <div class="card-body">
                            <!-- Información del estudiante -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Información del Estudiante</h5>
                                    <p v-if="inscription"><strong>Nombre:</strong> {{ inscription.player.full_names }}</p>
                                    <p v-if="inscription"><strong>Grupo:</strong> {{ inscription.training_group.name }}
                                    </p>
                                    <p><strong>Año:</strong> {{ currentYear }}</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <h5>Factura #</h5>
                                    <p class="text-muted">Se generará automáticamente</p>
                                </div>
                            </div>

                            <!-- Formulario -->
                            <form @submit.prevent="confirmCreate">
                                <input type="hidden" name="inscription_id" :value="inscriptionId">
                                <input type="hidden" name="training_group_id" :value="inscription?.training_group_id">
                                <input type="hidden" name="year" :value="currentYear">
                                <input type="hidden" name="student_name" :value="inscription?.player?.full_names">

                                <!-- Sección de meses pendientes -->
                                <div v-if="pendingMonths.length" class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-calendar-alt"></i> Mensualidades Pendientes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="5%" class="text-center">
                                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                                <input
                                                                    id="month-include-all"
                                                                    type="checkbox"
                                                                    checked="true"
                                                                    class="custom-control-input"
                                                                    v-model="selectAllMonts"
                                                                >
                                                                <label
                                                                    class="custom-control-label"
                                                                    for="month-include-all"
                                                                ></label>
                                                            </div>
                                                        </th>
                                                        <th width="25%">Descripción</th>
                                                        <th width="15%">Cantidad</th>
                                                        <th width="20%">Precio Unitario</th>
                                                        <th width="20%">Total</th>
                                                        <th width="7%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(month, index) in pendingMonths" :key="month.month"
                                                        :class="{ 'table-secondary': !month.include }">
                                                        <td class="text-center">
                                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                                <input
                                                                    :id="`month-include-${index}`"
                                                                    v-model="month.include"
                                                                    type="checkbox"
                                                                    class="custom-control-input"
                                                                >
                                                                <label
                                                                    class="custom-control-label"
                                                                    :for="`month-include-${index}`"
                                                                ></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                :value="month.name" readonly>
                                                            <input type="hidden" :name="`items[${index}][month]`"
                                                                :value="month.month">
                                                            <input type="hidden" :name="`items[${index}][payment_id]`"
                                                                :value="month.payment_id">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                v-model="month.quantity" min="1"
                                                                :disabled="true"
                                                                @input="calculateMonthTotal(month)">
                                                        </td>
                                                        <td>
                                                            <CurrencyInput class="form-control form-control-sm"
                                                            v-model="month.unit_price" autocomplete="off"
                                                            :disabled="!month.include"
                                                             @input="calculateItemTotal(month)"/>
                                                        </td>
                                                        <td>
                                                            <CurrencyInput class="form-control form-control-sm"
                                                            v-model="month.total" autocomplete="off" readonly/>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección de ítems adicionales -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fa fa-plus-circle"></i> Ítems Adicionales
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="5%" class="text-center">
                                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                                <input
                                                                    id="item-include-all"
                                                                    type="checkbox"
                                                                    checked="true"
                                                                    class="custom-control-input"
                                                                    v-model="selectAllAdditionalItems"
                                                                >
                                                                <label
                                                                    class="custom-control-label"
                                                                    for="item-include-all"
                                                                ></label>
                                                            </div>
                                                        </th>
                                                        <th width="25%">Descripción</th>
                                                        <th width="15%">Cantidad</th>
                                                        <th width="20%">Precio Unitario</th>
                                                        <th width="20%">Total</th>
                                                        <th width="5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(item, index) in additionalItems" :key="index"
                                                        :class="{ 'table-secondary': !item.include }">
                                                        <td class="text-center">
                                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                                <input
                                                                    :id="`item-include-${index}`"
                                                                    v-model="item.include"
                                                                    type="checkbox"
                                                                    class="custom-control-input"
                                                                >
                                                                <label
                                                                    class="custom-control-label"
                                                                    :for="`item-include-${index}`"
                                                                ></label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                v-model="item.description" :disabled="!item.include" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                v-model="item.quantity" min="1"
                                                                :disabled="!item.include"
                                                                @input="calculateItemTotal(item)">
                                                        </td>
                                                        <td>
                                                            <CurrencyInput class="form-control form-control-sm"
                                                            v-model="item.unit_price" autocomplete="off"
                                                            :disabled="!item.include"
                                                             @input="calculateItemTotal(item)"/>
                                                        </td>
                                                        <td>
                                                            <CurrencyInput class="form-control form-control-sm"
                                                            v-model="item.total" autocomplete="off" readonly/>
                                                        </td>

                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                @click="removeAdditionalItem(index)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success" @click="addAdditionalItem">
                                            <i class="fa fa-plus"></i> Agregar Ítem
                                        </button>
                                    </div>
                                </div>

                                <!-- Información de la factura -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fa fa-info-circle"></i> Información de Factura</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Fecha de Vencimiento</label>
                                                    <input type="date" class="form-control form-control-sm" v-model="dueDate" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Notas</label>
                                                    <textarea class="form-control form-control-sm" v-model="notes" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Totales -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fa fa-calculator"></i> Totales</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 offset-md-6">
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <th class="text-right"><h6>Subtotal:</h6></th>
                                                        <td width="150" class="text-right">
                                                            <h6>{{ moneyFormat(subtotal) }}</h6>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-right"><h6>Total Factura:</h6></th>
                                                        <td class="text-right">
                                                            <h4>{{ moneyFormat(total) }}</h4>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group text-right">
                                    <button type="button" class="btn btn-secondary me-1" @click="cancel">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary me-1" :disabled="loading || total == 0">
                                        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="fa fa-save"></i>
                                        {{ loading ? 'Guardando...' : 'Guardar Factura' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import CurrencyInput from '@/components/general/CurrencyInput';

const route = useRoute()
const router = useRouter()
const inscriptionId = route.params.inscription

// Estado reactivo
const inscription = ref(null)
const pendingMonths = ref([])
const additionalItems = ref([])
const dueDate = ref('')
const notes = ref('')
const loading = ref(false)
const selectAllMonts = ref(true)
const selectAllAdditionalItems = ref(true)
// Fecha actual
const currentYear = new Date().getFullYear()

// Calcular totales
const subtotal = computed(() => {
    let total = 0

    // Sumar meses pendientes incluidos
    pendingMonths.value.forEach(month => {
        if (month.include) {
            total += month.total
        }
    })

    // Sumar ítems adicionales
    additionalItems.value.forEach(item => {
        if(item.include){
            total += item.total
        }
    })

    return total
})

const total = computed(() => {
    return subtotal.value
})

// Métodos
const loadData = async () => {
    try {
        loading.value = true
        const response = await axios.get(`/api/v2/invoices/create/${inscriptionId}`)

        inscription.value = response.data.inscription

        // Inicializar meses pendientes
        pendingMonths.value = response.data.pendingMonths.map(month => ({
            ...month,
            include: true,
            description: month.name,
            quantity: 1,
            unit_price: month.amount,
            total: month.amount
        }))

        const uniformRequest = response.data.pendingUniformRequests.map(item => ({
            ...item,
            include:true,
            description: item.description,
            quantity: 1,
            unit_price: Math.trunc(item.unit_price),
            total: Math.trunc(item.unit_price),
            type: 'additional',
            uniform_request_id: item.uniform_request_id
        }))

        const customItems = response.data.customItems.map(item => ({
            ...item,
            include:true,
            description: item.name,
            quantity: 1,
            unit_price: Math.trunc(item.unit_price),
            total: Math.trunc(item.unit_price),
            type: 'additional'
        }))


        additionalItems.value = [...uniformRequest, ...customItems]

        // Establecer fecha de vencimiento por defecto (15 días)
        const today = new Date()
        const due = new Date(today)
        due.setDate(today.getDate() + 15)
        dueDate.value = due.toISOString().split('T')[0]

    } catch (error) {
        console.error('Error al cargar datos:', error)
        showMessage('Error al cargar los datos del estudiante', 'error')
    } finally {
        loading.value = false
    }
}


watch(selectAllMonts, (checked) => {
    pendingMonths.value.forEach(month => {
        month.include = checked
    })
})
watch(selectAllAdditionalItems, (checked) => {
    additionalItems.value.forEach(item => {
        item.include = checked
    })
})

const calculateMonthTotal = (month) => {
    if (month.include) {
        month.total = month.quantity * month.unit_price
    }
}

const calculateItemTotal = (item) => {
    if(item.include){
        item.total = item.quantity * item.unit_price
    }
}

const addAdditionalItem = () => {
    additionalItems.value.push({
        include: false,
        type: 'additional',
        description: '',
        quantity: 1,
        unit_price: 0,
        total: 0,
        uniform_request_id: null,
        month: null,
        payment_id: null
    })
}

const removeAdditionalItem = (index) => {
    additionalItems.value.splice(index, 1)
}

const confirmCreate = async () => {
    Swal.fire({
            title: `¿Guardar factura por: ${moneyFormat(total.value)} ?`,
            text: "¡Puedes cancelar y verificar la factura!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "¡Sí, guardar!"
        }).then((result) => {
            if (result.isConfirmed) {
                submitInvoice()
            }
        })
}

const submitInvoice = async () => {
    try {
        loading.value = true

        // Preparar datos para enviar
        const data = {
            inscription_id: inscription.value.id,
            training_group_id: inscription.value.training_group_id,
            year: currentYear,
            student_name: inscription.value.player.full_names,
            due_date: dueDate.value,
            notes: notes.value,
            items: []
        }

        // Agregar meses pendientes incluidos
        pendingMonths.value.forEach(month => {
            if (month.include) {
                data.items.push({
                    type: 'monthly',
                    description: month.description,
                    quantity: month.quantity,
                    unit_price: month.unit_price,
                    month: month.month,
                    payment_id: month.payment_id,
                    uniform_request_id: null
                })
            }
        })

        // Agregar ítems adicionales
        additionalItems.value.forEach(item => {
            if (item.include) {
                data.items.push({
                    type: item.type,
                    description: item.description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    month: null,
                    payment_id: null,
                    uniform_request_id: item?.uniform_request_id
                })
            }
        })

        // Enviar a la API
        const response = await axios.post('/api/v2/invoices', data)

        // Redirigir a la vista de la factura creada
        router.push({ name: 'invoices.show', params: {id: response.data.id}})

    } catch (error) {
        console.error('Error al crear factura:', error)
        showMessage('Error al crear la factura: ' + (error.response?.data?.message || error.message), 'error')
    } finally {
        loading.value = false
    }
}

const cancel = () => {
    router.push({name: 'inscriptions'})
}

// Cargar datos al montar el componente
onMounted(() => {
    loadData()
})
</script>

<style scoped>
.table-secondary input:disabled {
    background-color: #e9ecef;
    border-color: #dee2e6;
    opacity: 0.6;
}

.table-secondary {
    opacity: 0.6;
}

.form-check-input {
    margin-top: 0;
}
</style>