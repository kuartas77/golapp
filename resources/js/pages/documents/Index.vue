<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h4 class="mb-1">{{ pageTitle }}</h4>
                    <p class="mb-0 text-muted">{{ pageDescription }}</p>
                </div>
                <button type="button" class="btn btn-primary" @click="openModal">
                    <i class="fa fa-plus me-1"></i> Agregar documento
                </button>
            </div>
        </template>

        <template #body>
            <DatatableTemplate
                :key="tableKey"
                id="school-documents-table"
                :options="tableOptions"
                :data="documents"
            >
                <template #thead>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Archivo</th>
                            <th>Tipo / tamaño</th>
                            <th>Autor</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </template>
                <template #actions="props">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-info btn-sm" title="Descargar" @click="download(props.rowData)">
                            <i class="fa fa-download"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" @click="confirmDelete(props.rowData)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </template>
            </DatatableTemplate>
        </template>
    </panel>

    <div ref="modalRef" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" @submit.prevent="save">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar documento</h5>
                    <button type="button" class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
                    <div class="mb-3">
                        <label for="document-title" class="form-label">Título *</label>
                        <input id="document-title" v-model.trim="form.title" class="form-control" maxlength="255" required />
                    </div>
                    <div class="mb-3">
                        <label for="document-description" class="form-label">Descripción</label>
                        <textarea id="document-description" v-model.trim="form.description" class="form-control" rows="3" maxlength="5000"></textarea>
                    </div>
                    <div>
                        <label for="document-file" class="form-label">Archivo *</label>
                        <input
                            id="document-file"
                            ref="fileInput"
                            type="file"
                            class="form-control"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                            required
                            @change="selectFile"
                        />
                        <small class="text-muted">PDF, Word, Excel o PowerPoint. Máximo 20 MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="closeModal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" :disabled="isSaving">
                        {{ isSaving ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <breadcrumb :parent="breadcrumbParent" :current="pageTitle" />
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/utils/axios'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'

const route = useRoute()
const documents = ref([])
const tableKey = ref(0)
const modalRef = ref(null)
const fileInput = ref(null)
const modal = ref(null)
const isSaving = ref(false)
const formError = ref('')
const form = reactive({ title: '', description: '', file: null })

const isClub = computed(() => route.meta.documentScope === 'club_document')
const pageTitle = computed(() => isClub.value ? 'Documentos del club' : 'Planificación documental')
const pageDescription = computed(() => isClub.value
    ? 'Administra los documentos legales y administrativos del club.'
    : 'Administra programas y documentos de planificación del club.')
const endpoint = computed(() => isClub.value ? '/api/v2/club-documents' : '/api/v2/document-planning')
const breadcrumbParent = computed(() => isClub.value ? 'Configuración' : 'Módulos deportivos')

usePageTitle(pageTitle)

const formatSize = (bytes) => bytes >= 1048576
    ? `${(bytes / 1048576).toFixed(1)} MB`
    : `${Math.max(1, Math.round(bytes / 1024))} KB`

const tableOptions = {
    ...configLanguaje,
    responsive: true,
    order: [[5, 'desc']],
    columns: [
        { data: 'title' },
        { data: 'description', defaultContent: '' },
        { data: 'original_name' },
        { data: null, render: (_, __, row) => `${row.extension.toUpperCase()} · ${formatSize(row.size_bytes)}` },
        { data: 'uploader_name', defaultContent: '' },
        { data: 'created_at', render: (value) => value ? new Date(value).toLocaleDateString('es-CO') : '' },
        { data: null, name: 'actions', orderable: false, searchable: false, render: '#actions' },
    ],
}

async function loadDocuments() {
    const { data } = await api.get(endpoint.value)
    documents.value = data.data
    tableKey.value += 1
}

function resetForm() {
    Object.assign(form, { title: '', description: '', file: null })
    formError.value = ''
    if (fileInput.value) fileInput.value.value = ''
}

function openModal() {
    resetForm()
    modal.value.show()
}

function closeModal() {
    modal.value.hide()
}

function selectFile(event) {
    form.file = event.target.files?.[0] ?? null
}

async function save() {
    if (!form.file) return
    isSaving.value = true
    formError.value = ''
    const payload = new FormData()
    payload.append('title', form.title)
    if (form.description) payload.append('description', form.description)
    payload.append('file', form.file)

    try {
        await api.post(endpoint.value, payload)
        closeModal()
        await loadDocuments()
        window.Swal?.fire({ icon: 'success', title: 'Documento guardado', timer: 1600, showConfirmButton: false })
    } catch (error) {
        formError.value = error.response?.data?.message ?? 'No fue posible guardar el documento.'
    } finally {
        isSaving.value = false
    }
}

async function download(document) {
    const response = await api.get(`${endpoint.value}/${document.id}/download`, { responseType: 'blob' })
    const url = URL.createObjectURL(response.data)
    const link = window.document.createElement('a')
    link.href = url
    link.download = document.original_name
    link.click()
    URL.revokeObjectURL(url)
}

async function confirmDelete(document) {
    const result = await window.Swal.fire({
        icon: 'warning',
        title: '¿Eliminar documento?',
        text: `También se eliminará el archivo ${document.original_name}.`,
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e7515a',
    })
    if (!result.isConfirmed) return

    try {
        await api.delete(`${endpoint.value}/${document.id}`)
        await loadDocuments()
        await window.Swal.fire({ icon: 'success', title: 'Documento eliminado', timer: 1600, showConfirmButton: false })
    } catch (error) {
        await window.Swal.fire({ icon: 'error', title: 'No fue posible eliminar', text: error.response?.data?.message })
    }
}

onMounted(async () => {
    await nextTick()
    modal.value = new window.bootstrap.Modal(modalRef.value)
    await loadDocuments()
})
onBeforeUnmount(() => modal.value?.dispose())
watch(() => route.meta.documentScope, loadDocuments)
</script>
