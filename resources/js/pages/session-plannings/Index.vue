<template>
    <panel>
        <template #header><div class="row g-3 align-items-center"><div class="col-md-auto"><button class="btn btn-primary" @click="openCreate">Nueva planificación</button></div><div class="col"><p class="mb-0">Planifica sesiones por fases con diagramas de cancha y registra la asistencia.</p></div></div></template>
        <template #body>
            <DatatableTemplate ref="table" id="session-plannings-table" :options="options">
                <template #actions="props"><div class="d-flex justify-content-center gap-1">
                    <a :href="props.rowData.export_pdf_url" target="_blank" class="btn btn-info btn-sm" title="Exportar PDF"><i class="fa-solid fa-file-pdf"></i></a>
                    <button class="btn btn-warning btn-sm" :disabled="props.rowData.period_locked" @click="openEdit(props.rowData.id)"><i class="fa fa-edit"></i></button>
                    <button v-if="canDelete" class="btn btn-danger btn-sm" :disabled="props.rowData.period_locked" @click="confirmDelete(props.rowData)"><i class="fa fa-trash"></i></button>
                </div></template>
            </DatatableTemplate>
        </template>
    </panel>
    <SessionPlanningModal :show="isModalOpen" :session-id="selectedId" @updated="reloadTable" @cancel="closeModal" />
    <breadcrumb :parent="'Plataforma'" :current="'Planificación de sesiones'" />
</template>
<script setup>
import { computed, ref, useTemplateRef } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import SessionPlanningModal from './SessionPlanningModal.vue'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import { useAuthUser } from '@/store/auth-user'
import { usePageTitle } from '@/composables/use-meta'
usePageTitle('Planificación de sesiones')
const auth = useAuthUser(), table = useTemplateRef('table')
const selectedId = ref(null), isModalOpen = ref(false)
const canDelete = computed(() => auth.hasAnyRole(['super-admin', 'school']))
const columns = [
    { data: 'creator_name', title: 'Creado por' }, { data: 'training_group_name', title: 'Grupo' },
    { data: 'period', title: 'Periodo' }, { data: 'session', title: 'Sesión' }, { data: 'date', title: 'Fecha' },
    { data: 'phases_count', title: 'N° Fases', searchable: false }, { data: 'created_at', title: 'Creado' },
    { data: 'id', title: 'Opciones', searchable: false, orderable: false, render: '#actions' },
]
const options = { ...configLanguaje, serverSide: true, processing: true, pipeline: { pages: 5 }, order: [[6, 'desc']], columns,
    ajax: async (data, callback) => { try { const response = await api.get('/api/v2/datatables/session_plannings', { params: data }); callback(response.data) } catch { callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 }) } },
    columnDefs: [{ targets: '_all', className: 'dt-head-center dt-body-center' }],
}
const openCreate = () => { selectedId.value = null; isModalOpen.value = true }
const openEdit = id => { selectedId.value = id; isModalOpen.value = true }
const closeModal = () => { isModalOpen.value = false; selectedId.value = null }
const reloadTable = () => { closeModal(); table.value?.table?.dt?.clearPipeline(); table.value?.table?.dt?.ajax.reload(null, false) }
async function confirmDelete(row) { const result = await window.Swal.fire({ title: `Eliminar planificación #${row.id}`, text: 'Esta acción no se puede deshacer.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' }); if (result.isConfirmed) { await api.delete(`/api/v2/session-plannings/${row.id}`); reloadTable() } }
</script>
