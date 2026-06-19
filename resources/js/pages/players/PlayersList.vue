<template>
    <panel>
        <template #lateral />
        <template #header>
            <div class="row">
                <div class="col-md-auto">
                    <p data-tour="players-list-intro">En este listado se incluyen todos los deportistas que han sido parte de la escuela en el transcurso de los años.</p>
                </div>
                <div class="col-md-auto ms-md-auto">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <button
                            v-if="canImportPlayers"
                            type="button"
                            class="btn btn-outline-info btn-sm"
                            @click="openImportModal"
                        >
                            <i class="fa fa-upload me-1"></i>
                            Importar
                        </button>
                        <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                            Guia
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <template #body>
            <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <span>{{ globalError }}</span>
                <button type="button" class="btn btn-sm btn-danger" @click="reloadTable">
                    Reintentar
                </button>
            </div>
            <div data-tour="players-list-table">
                <DatatableTemplate :options="options" :id="'players_table'" ref="table">
                    <template #actions="props">
                        <div class="d-inline-flex gap-1">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                title="Editar deportista"
                                @click.stop="editPlayer(props.rowData.unique_code)"
                            >
                                <i class="fa fa-edit"></i>
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-info btn-sm"
                                title="Ver resumen"
                                :disabled="!props.rowData.current_inscription_id"
                                @click.stop="showSummary(props.rowData.current_inscription_id)"
                            >
                                <i class="fa-regular fa-address-card"></i>
                            </button>
                        </div>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
    <PageTutorialOverlay :tutorial="tutorial" />

    <div ref="importModalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" @submit.prevent="submitImport">
                <div class="modal-header">
                    <h5 class="modal-title">Importar deportistas</h5>
                    <button type="button" class="btn-close" :disabled="importing" @click="closeImportModal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="importError" class="alert alert-danger py-2">{{ importError }}</div>

                    <div v-if="isSuperAdmin" class="mb-3">
                        <label class="form-label" for="import-school-id">Escuela</label>
                        <select
                            id="import-school-id"
                            v-model="importForm.school_id"
                            class="form-select"
                            :disabled="importing || loadingSchools"
                            required
                        >
                            <option value="">Selecciona...</option>
                            <option v-for="school in schoolOptions" :key="school.value" :value="school.value">
                                {{ school.label }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label" for="import-players-file">Archivo</label>
                        <input
                            id="import-players-file"
                            ref="importFileInput"
                            type="file"
                            class="form-control"
                            accept=".xlsx,.xls,.csv"
                            :disabled="importing"
                            required
                            @change="onImportFileChange"
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" :disabled="importing" @click="closeImportModal">
                        Cerrar
                    </button>
                    <button type="submit" class="btn btn-info" :disabled="!canSubmitImport">
                        <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import usePlayerList from '@/composables/player/playersList'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { playersListTutorial } from '@/tutorials/players'
import api from '@/utils/axios'
import { useAuthUser } from '@/store/auth-user'
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'

const { options, table, editPlayer, showSummary, reloadTable, globalError } = usePlayerList()
const tutorial = usePageTutorial(playersListTutorial)
const auth = useAuthUser()

const importModalElement = ref(null)
const importFileInput = ref(null)
const importFile = ref(null)
const importing = ref(false)
const loadingSchools = ref(false)
const importError = ref('')
const schoolOptions = ref([])
const importForm = reactive({
    school_id: '',
})

let importModal = null

const isSuperAdmin = computed(() => auth.hasRole('super-admin'))
const canImportPlayers = computed(() => auth.hasAnyRole(['super-admin', 'school']))
const canSubmitImport = computed(() => {
    return !importing.value
        && Boolean(importFile.value)
        && (!isSuperAdmin.value || Boolean(importForm.school_id))
})

const openImportModal = async () => {
    importError.value = ''
    importFile.value = null

    if (importFileInput.value) {
        importFileInput.value.value = ''
    }

    if (isSuperAdmin.value && schoolOptions.value.length === 0) {
        await loadSchoolOptions()
    }

    importModal?.show()
}

const closeImportModal = () => {
    importModal?.hide()
}

const loadSchoolOptions = async () => {
    loadingSchools.value = true

    try {
        const { data } = await api.get('/api/v2/admin/schools/options', {
            skipGlobalLoader: true,
        })
        schoolOptions.value = data.schools ?? []
    } catch (error) {
        importError.value = error.response?.data?.message || 'No fue posible cargar las escuelas.'
    } finally {
        loadingSchools.value = false
    }
}

const onImportFileChange = (event) => {
    importFile.value = event.target.files?.[0] ?? null
}

const submitImport = async () => {
    if (!canSubmitImport.value) {
        return
    }

    importing.value = true
    importError.value = ''

    const payload = new FormData()
    payload.append('file', importFile.value, importFile.value.name)

    if (isSuperAdmin.value) {
        payload.append('school_id', String(importForm.school_id))
    }

    try {
        const { data } = await api.post('/api/v2/import/players', payload)
        closeImportModal()
        reloadTable()
        await window.Swal?.fire({
            icon: 'success',
            title: data.message || 'Deportistas importados correctamente.',
            confirmButtonText: 'Entendido',
        })
    } catch (error) {
        importError.value = error.response?.data?.message || 'No fue posible importar los deportistas.'
    } finally {
        importing.value = false
    }
}

onMounted(() => {
    if (importModalElement.value) {
        importModal = new window.bootstrap.Modal(importModalElement.value, {
            backdrop: 'static',
            keyboard: false,
        })
    }
})

onBeforeUnmount(() => {
    importModal?.dispose()
})

</script>
