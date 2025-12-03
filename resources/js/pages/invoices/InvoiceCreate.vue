<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-file-invoice"></i> Crear Factura</h4>
                    </div>

                    <div class="card-body">
                        <!-- Información del estudiante -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información del Estudiante</h5>
                                <p v-if="inscription"><strong>Nombre:</strong> {{ inscription.student.name }}</p>
                                <p v-if="inscription"><strong>Grupo:</strong> {{ inscription.training_group.name }}</p>
                                <p><strong>Año:</strong> {{ currentYear }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h5>Factura #</h5>
                                <p class="text-muted">Se generará automáticamente</p>
                            </div>
                        </div>

                        <!-- Formulario -->
                        <form @submit.prevent="submitInvoice">
                            <input type="hidden" name="inscription_id" :value="inscriptionId">
                            <input type="hidden" name="training_group_id" :value="inscription?.training_group_id">
                            <input type="hidden" name="year" :value="currentYear">
                            <input type="hidden" name="student_name" :value="inscription?.student?.name">

                            <!-- Sección de meses pendientes -->
                            <div v-if="pendingMonths.length > 0" class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-alt"></i> Meses Pendientes
                                        <small class="text-muted">(Desde tabla payments)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">Incluir</th>
                                                    <th width="25%">Mes</th>
                                                    <th width="20%">Descripción</th>
                                                    <th width="15%">Cantidad</th>
                                                    <th width="15%">Precio Unitario</th>
                                                    <th width="15%">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(month, index) in pendingMonths"
                                                    :key="month.month"
                                                    :class="{ 'table-secondary': !month.include }">
                                                    <td>
                                                        <input type="checkbox"
                                                               v-model="month.include"
                                                               class="form-check-input">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               :value="month.name"
                                                               readonly>
                                                        <input type="hidden"
                                                               :name="`items[${index}][month]`"
                                                               :value="month.month">
                                                        <input type="hidden"
                                                               :name="`items[${index}][payment_id]`"
                                                               :value="month.payment_id">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               v-model="month.description"
                                                               :disabled="!month.include"
                                                               required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               v-model="month.quantity"
                                                               min="1"
                                                               :disabled="!month.include"
                                                               @input="calculateMonthTotal(month)">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               v-model="month.unit_price"
                                                               step="0.01"
                                                               :disabled="!month.include"
                                                               @input="calculateMonthTotal(month)">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               :value="month.total"
                                                               readonly>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de ítems adicionales -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-plus-circle"></i> Ítems Adicionales
                                        <small class="text-muted">(Textos libres)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="25%">Descripción</th>
                                                    <th width="15%">Cantidad</th>
                                                    <th width="20%">Precio Unitario</th>
                                                    <th width="20%">Total</th>
                                                    <th width="15%">Tipo</th>
                                                    <th width="5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in additionalItems" :key="index">
                                                    <td>
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               v-model="item.description"
                                                               required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               v-model="item.quantity"
                                                               min="1"
                                                               @input="calculateItemTotal(item)">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               v-model="item.unit_price"
                                                               step="0.01"
                                                               min="0"
                                                               @input="calculateItemTotal(item)">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm"
                                                               :value="item.total"
                                                               readonly>
                                                    </td>
                                                    <td>
                                                        <select class="form-control form-control-sm"
                                                                v-model="item.type">
                                                            <option value="additional">Adicional</option>
                                                            <option value="enrollment">Matrícula</option>
                                                            <option value="monthly">Mensualidad</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                @click="removeAdditionalItem(index)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button"
                                            class="btn btn-sm btn-success"
                                            @click="addAdditionalItem">
                                        <i class="fas fa-plus"></i> Agregar Ítem
                                    </button>
                                </div>
                            </div>

                            <!-- Información de la factura -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información de Factura</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha de Vencimiento</label>
                                                <input type="date"
                                                       class="form-control"
                                                       v-model="dueDate"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Notas</label>
                                                <textarea class="form-control"
                                                          v-model="notes"
                                                          rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Totales -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Totales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th class="text-right">Subtotal:</th>
                                                    <td width="150" class="text-right">
                                                        <span>${{ subtotal.toFixed(2) }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right">Total Factura:</th>
                                                    <td class="text-right">
                                                        <h4>${{ total.toFixed(2) }}</h4>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="button"
                                        class="btn btn-secondary"
                                        @click="cancel">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="submit"
                                        class="btn btn-primary"
                                        :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                                    <i v-else class="fas fa-save"></i>
                                    {{ loading ? 'Guardando...' : 'Guardar Factura' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

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
        total += item.total
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
        const response = await axios.get(`/api/inscriptions/${inscriptionId}/invoice-data`)

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

        // Establecer fecha de vencimiento por defecto (15 días)
        const today = new Date()
        const due = new Date(today)
        due.setDate(today.getDate() + 15)
        dueDate.value = due.toISOString().split('T')[0]

    } catch (error) {
        console.error('Error al cargar datos:', error)
        alert('Error al cargar los datos del estudiante')
    } finally {
        loading.value = false
    }
}

const calculateMonthTotal = (month) => {
    if (month.include) {
        month.total = month.quantity * month.unit_price
    }
}

const calculateItemTotal = (item) => {
    item.total = item.quantity * item.unit_price
}

const addAdditionalItem = () => {
    additionalItems.value.push({
        type: 'additional',
        description: '',
        quantity: 1,
        unit_price: 0,
        total: 0
    })
}

const removeAdditionalItem = (index) => {
    additionalItems.value.splice(index, 1)
}

const submitInvoice = async () => {
    try {
        loading.value = true

        // Preparar datos para enviar
        const data = {
            inscription_id: inscription.value.id,
            training_group_id: inscription.value.training_group_id,
            year: currentYear,
            student_name: inscription.value.student.name,
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
                    payment_id: month.payment_id
                })
            }
        })

        // Agregar ítems adicionales
        additionalItems.value.forEach(item => {
            data.items.push({
                type: item.type,
                description: item.description,
                quantity: item.quantity,
                unit_price: item.unit_price,
                month: null,
                payment_id: null
            })
        })

        // Enviar a la API
        const response = await axios.post('/api/invoices', data)

        // Redirigir a la vista de la factura creada
        router.push(`/invoices/${response.data.invoice.id}`)

    } catch (error) {
        console.error('Error al crear factura:', error)
        alert('Error al crear la factura: ' + (error.response?.data?.message || error.message))
    } finally {
        loading.value = false
    }
}

const cancel = () => {
    router.push(`/inscriptions/${inscriptionId}`)
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