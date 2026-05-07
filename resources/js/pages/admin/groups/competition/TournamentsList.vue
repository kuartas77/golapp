<template>
    <panel>
        <template #header>
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="mb-1">Torneos</h5>
                    <p class="mb-0 text-muted">
                        Se usan para poblar el selector de los grupos de competencia.
                    </p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <router-link :to="{ name: 'competition-groups' }" class="btn btn-outline-secondary">
                        Volver a grupos
                    </router-link>
                    <button type="button" class="btn btn-primary" :disabled="isSaving" @click="openCreateModal">
                        Agregar torneo
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div v-if="listError" class="alert alert-danger py-2" role="alert">
                {{ listError }}
            </div>

            <div v-if="isLoading" class="py-5 text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="text-muted mb-0">Cargando torneos...</p>
            </div>

            <template v-else>
                <div v-if="items.length" class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Torneo</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td>
                                    <div class="fw-semibold">{{ item.name }}</div>
                                </td>
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
                                            <span
                                                v-if="isDeletingId === item.id"
                                                class="spinner-border spinner-border-sm me-1"
                                                role="status"
                                            ></span>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-else
                    class="border rounded-3 p-4 text-center text-muted d-flex align-items-center justify-content-center"
                >
                    Todavía no hay torneos configurados para esta escuela.
                </div>
            </template>
        </template>
    </panel>

    <div ref="modalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <Form
                ref="tournamentForm"
                :validation-schema="schema"
                :initial-values="buildDefaultFormValues()"
                @submit="submitForm"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">
                                {{ isEditMode ? 'Editar torneo' : 'Nuevo torneo' }}
                            </h5>
                            <small class="text-muted">Este torneo se podrá asociar a grupos de competencia.</small>
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
                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>

                        <div>
                            <label class="form-label" for="tournament-name">Nombre</label>
                            <Field name="name" v-slot="{ field, errorMessage, meta }">
                                <input
                                    id="tournament-name"
                                    v-bind="field"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': meta.touched && errorMessage }"
                                    :disabled="isSaving"
                                    autocomplete="off"
                                    placeholder="TORNEO APERTURA"
                                >
                            </Field>
                            <ErrorMessage name="name" class="invalid-feedback d-block" />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" :disabled="isSaving" @click="closeModal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ isEditMode ? 'Guardar cambios' : 'Guardar torneo' }}
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>

    <breadcrumb :parent="'Administración'" :current="'Torneos'" />
</template>

<script setup>
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
import * as yup from 'yup'
import api from '@/utils/axios'
import { usePageTitle } from '@/composables/use-meta'

const items = ref([])
const isLoading = ref(true)
const listError = ref('')
const isSaving = ref(false)
const isDeletingId = ref(null)
const editingId = ref(null)
const modalElement = ref(null)
const globalError = ref('')
const tournamentForm = useTemplateRef('tournamentForm')
const { proxy } = getCurrentInstance()

const isEditMode = computed(() => editingId.value !== null)

let modalInstance = null

const schema = yup.object().shape({
    name: yup.string().required(),
})

const buildDefaultFormValues = () => ({
    name: '',
})

const resetFormState = (values = buildDefaultFormValues()) => {
    globalError.value = ''
    tournamentForm.value?.resetForm({ values })
}

const fetchTournaments = async () => {
    isLoading.value = true
    listError.value = ''

    try {
        const { data } = await api.get('/api/v2/admin/tournaments')
        items.value = Array.isArray(data) ? data : []
    } catch (error) {
        listError.value = error.response?.data?.message || 'No fue posible cargar los torneos.'
        items.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingId.value = null
    resetFormState()
    modalInstance?.show()
}

const openEditModal = (item) => {
    editingId.value = item.id
    resetFormState({
        name: item.name ?? '',
    })
    modalInstance?.show()
}

const closeModal = () => {
    modalInstance?.hide()
}

const submitForm = async (values, actions) => {
    globalError.value = ''
    isSaving.value = true

    try {
        const endpoint = isEditMode.value
            ? `/api/v2/admin/tournaments/${editingId.value}`
            : '/api/v2/admin/tournaments'
        const request = isEditMode.value
            ? api.put(endpoint, values)
            : api.post(endpoint, values)

        const { data } = await request

        showMessage(data.message || (isEditMode.value ? 'Torneo actualizado correctamente.' : 'Torneo creado correctamente.'))
        closeModal()
        await fetchTournaments()
    } catch (error) {
        const fallbackMessage = error.response?.data?.message || 'No fue posible guardar el torneo.'

        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (message) => (globalError.value = message || fallbackMessage)
        )

        if (error.response?.status !== 422) {
            showMessage(fallbackMessage, 'error')
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = async (item) => {
    const result = await window.Swal.fire({
        title: '¿Eliminar torneo?',
        text: `Se eliminará ${item.name} del catálogo disponible.`,
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
        await api.delete(`/api/v2/admin/tournaments/${item.id}`)
        showMessage('Torneo eliminado correctamente.')
        await fetchTournaments()
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible eliminar el torneo.', 'error')
    } finally {
        isDeletingId.value = null
    }
}

const handleModalHidden = () => {
    editingId.value = null
    resetFormState()
}

onMounted(() => {
    usePageTitle('Torneos')
    fetchTournaments()

    modalInstance = new window.bootstrap.Modal(modalElement.value)
    modalElement.value?.addEventListener('hidden.bs.modal', handleModalHidden)
})

onBeforeUnmount(() => {
    modalElement.value?.removeEventListener('hidden.bs.modal', handleModalHidden)
    modalInstance?.hide()
})
</script>
