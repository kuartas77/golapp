<template>
    <panel>
        <template #header>
            <AppPageHeader
                title="Numeración de facturas"
                subtitle="Administra la serie interna y los rangos autorizados de la escuela."
                icon="fa fa-hashtag"
            >
                <template #actions>
                    <AppButton v-if="!isReadOnly" variant="primary" size="sm" @click="openCreate">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        Nueva resolución
                    </AppButton>
                </template>
            </AppPageHeader>
        </template>

        <template #body>
            <ContentState v-if="loading" type="loading" title="Cargando numeración" message="Consultando la configuración de la escuela." />
            <ContentState
                v-else-if="loadError"
                type="error"
                title="No fue posible cargar la numeración"
                :message="loadError"
                action-label="Reintentar"
                @action="load"
            />

            <template v-else>
                <div v-if="actionError" class="alert alert-danger" role="alert">{{ actionError }}</div>

                <div class="card mb-4">
                    <div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <h5 class="mb-1">Facturación electrónica</h5>
                            <p class="text-muted mb-0">
                                Al habilitarla, las nuevas facturas exigirán una resolución activa, vigente y con numeración disponible.
                            </p>
                        </div>
                        <div class="form-check form-switch align-self-lg-center">
                            <input
                                id="electronic-invoicing-mode"
                                v-model="electronicEnabled"
                                class="form-check-input"
                                type="checkbox"
                                :disabled="!canToggle || togglingMode"
                                @change="toggleElectronicMode"
                            >
                            <label class="form-check-label" for="electronic-invoicing-mode">
                                {{ electronicEnabled ? 'Habilitada' : 'Deshabilitada' }}
                            </label>
                            <div v-if="!canToggle" class="small text-muted">Sólo un super-admin puede cambiar este estado.</div>
                        </div>
                    </div>
                </div>

                <div v-if="showForm" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ editingId ? 'Editar resolución' : 'Nueva resolución' }}</h5>
                    </div>
                    <form class="card-body" @submit.prevent="submitRange">
                        <div v-if="formError" class="alert alert-danger" role="alert">{{ formError }}</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="resolution-number">Número de resolución</label>
                                <input id="resolution-number" v-model.trim="form.resolution_number" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="resolution-date">Fecha de resolución</label>
                                <input id="resolution-date" v-model="form.resolution_date" type="date" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="resolution-prefix">Prefijo</label>
                                <input id="resolution-prefix" v-model.trim="form.prefix" class="form-control text-uppercase" maxlength="4" placeholder="FE" :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="range-start">Rango desde</label>
                                <input id="range-start" v-model.number="form.range_start" type="number" min="1" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="range-end">Rango hasta</label>
                                <input id="range-end" v-model.number="form.range_end" type="number" min="1" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="next-number">Próximo consecutivo</label>
                                <input id="next-number" v-model.number="form.next_number" type="number" min="1" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="valid-from">Vigente desde</label>
                                <input id="valid-from" v-model="form.valid_from" type="date" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="valid-until">Vigente hasta</label>
                                <input id="valid-until" v-model="form.valid_until" type="date" class="form-control" required :disabled="immutableForm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="technical-key">Clave técnica</label>
                                <input id="technical-key" v-model="form.technical_key" type="password" class="form-control" autocomplete="new-password" placeholder="Dejar vacío para conservar">
                                <small v-if="editingHasTechnicalKey" class="text-muted">Ya existe una clave técnica configurada.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" :disabled="saving" @click="closeForm">Cancelar</button>
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                {{ saving ? 'Guardando...' : 'Guardar resolución' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Resolución</th>
                                <th>Serie</th>
                                <th>Vigencia</th>
                                <th>Próximo</th>
                                <th>Disponibles</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="range in ranges" :key="range.id">
                                <td>
                                    <div class="fw-semibold">{{ range.resolution_number }}</div>
                                    <small class="text-muted">{{ range.has_technical_key ? 'Clave técnica configurada' : 'Sin clave técnica' }}</small>
                                </td>
                                <td>{{ range.prefix || 'Sin prefijo' }} {{ range.range_start }}–{{ range.range_end }}</td>
                                <td>{{ range.valid_from }} – {{ range.valid_until }}</td>
                                <td>{{ `${range.prefix || ''}${range.next_number}` }}</td>
                                <td>{{ range.remaining_numbers }}</td>
                                <td><span class="badge" :class="stateClass(range.state)">{{ stateLabel(range.state) }}</span></td>
                                <td class="text-end">
                                    <div v-if="!isReadOnly" class="d-inline-flex flex-wrap justify-content-end gap-1">
                                        <button type="button" class="btn btn-outline-primary btn-sm" @click="openEdit(range)">Editar</button>
                                        <button v-if="range.is_active" type="button" class="btn btn-outline-warning btn-sm" @click="deactivate(range)">Desactivar</button>
                                        <button v-else-if="range.state === 'available'" type="button" class="btn btn-outline-success btn-sm" @click="activate(range)">Activar</button>
                                        <button v-if="!range.used_at && !range.is_active" type="button" class="btn btn-outline-danger btn-sm" @click="remove(range)">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!ranges.length">
                                <td colspan="7" class="text-center text-muted py-4">No hay resoluciones registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </template>
    </panel>
    <breadcrumb :parent="'Configuración'" :current="'Numeración'" />
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import api from '@/utils/axios'
import AppButton from '@/components/general/AppButton.vue'
import AppPageHeader from '@/components/general/AppPageHeader.vue'
import ContentState from '@/components/general/ContentState.vue'
import { useAuthUser } from '@/store/auth-user'

const auth = useAuthUser()
const isReadOnly = computed(() => auth.hasRole('viewer'))

const blankForm = () => ({
    resolution_number: '', resolution_date: '', prefix: '', range_start: 1, range_end: 1,
    next_number: 1, valid_from: '', valid_until: '', technical_key: '',
})

const loading = ref(true)
const loadError = ref('')
const actionError = ref('')
const formError = ref('')
const ranges = ref([])
const electronicEnabled = ref(false)
const canToggle = ref(false)
const togglingMode = ref(false)
const showForm = ref(false)
const saving = ref(false)
const editingId = ref(null)
const editingHasTechnicalKey = ref(false)
const immutableForm = ref(false)
const form = reactive(blankForm())

const errorMessage = error => error.response?.data?.message
    || Object.values(error.response?.data?.errors || {}).flat()[0]
    || 'No fue posible completar la operación.'

const load = async () => {
    loading.value = true
    loadError.value = ''
    try {
        const { data } = await api.get('/api/v2/admin/invoice-number-ranges')
        ranges.value = data.ranges || []
        electronicEnabled.value = Boolean(data.electronic_invoicing_enabled)
        canToggle.value = Boolean(data.can_toggle_electronic_invoicing)
    } catch (error) {
        loadError.value = errorMessage(error)
    } finally {
        loading.value = false
    }
}

const openCreate = () => {
    Object.assign(form, blankForm())
    editingId.value = null
    editingHasTechnicalKey.value = false
    immutableForm.value = false
    formError.value = ''
    showForm.value = true
}

const openEdit = range => {
    Object.assign(form, blankForm(), range, { technical_key: '' })
    editingId.value = range.id
    editingHasTechnicalKey.value = Boolean(range.has_technical_key)
    immutableForm.value = Boolean(range.used_at)
    formError.value = ''
    showForm.value = true
}

const closeForm = () => { showForm.value = false }

const submitRange = async () => {
    saving.value = true
    formError.value = ''
    try {
        if (editingId.value) {
            await api.put(`/api/v2/admin/invoice-number-ranges/${editingId.value}`, { ...form })
        } else {
            await api.post('/api/v2/admin/invoice-number-ranges', { ...form })
        }
        showForm.value = false
        await load()
    } catch (error) {
        formError.value = errorMessage(error)
    } finally {
        saving.value = false
    }
}

const activate = async range => {
    actionError.value = ''
    try { await api.patch(`/api/v2/admin/invoice-number-ranges/${range.id}/activate`); await load() }
    catch (error) { actionError.value = errorMessage(error) }
}
const deactivate = async range => {
    actionError.value = ''
    try { await api.patch(`/api/v2/admin/invoice-number-ranges/${range.id}/deactivate`); await load() }
    catch (error) { actionError.value = errorMessage(error) }
}
const remove = async range => {
    actionError.value = ''
    try { await api.delete(`/api/v2/admin/invoice-number-ranges/${range.id}`); await load() }
    catch (error) { actionError.value = errorMessage(error) }
}

const toggleElectronicMode = async () => {
    const requested = electronicEnabled.value
    togglingMode.value = true
    actionError.value = ''
    try {
        const { data } = await api.patch('/api/v2/admin/invoice-numbering/electronic-mode', { enabled: requested })
        electronicEnabled.value = Boolean(data.electronic_invoicing_enabled)
    } catch (error) {
        electronicEnabled.value = !requested
        actionError.value = errorMessage(error)
    } finally {
        togglingMode.value = false
    }
}

const stateLabel = state => ({ active: 'Activa', available: 'Disponible', future: 'Próxima', expired: 'Vencida', exhausted: 'Agotada' }[state] || state)
const stateClass = state => ({ active: 'bg-success', available: 'bg-secondary', future: 'bg-info', expired: 'bg-warning text-dark', exhausted: 'bg-danger' }[state] || 'bg-secondary')

load()
</script>
