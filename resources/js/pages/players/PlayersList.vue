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
            <div
                v-if="activeImport"
                class="alert d-flex align-items-start gap-2"
                :class="importStatusClass"
                role="status"
            >
                <span
                    v-if="['pending', 'processing'].includes(activeImport.status)"
                    class="spinner-border spinner-border-sm mt-1"
                    aria-hidden="true"
                ></span>
                <div class="flex-grow-1">
                    <strong>{{ importStatusTitle }}</strong>
                    <div>{{ importStatusMessage }}</div>
                </div>
                <button
                    v-if="['completed', 'failed'].includes(activeImport.status)"
                    type="button"
                    class="btn-close"
                    aria-label="Cerrar estado de importación"
                    @click="activeImport = null"
                ></button>
            </div>
            <ContentState
                v-if="globalError"
                type="error"
                title="No fue posible cargar los deportistas"
                :message="globalError"
                action-label="Reintentar"
                class="mb-3"
                @action="reloadTable"
            />
            <div v-show="!globalError" data-tour="players-list-table">
                <DatatableTemplate :options="options" :id="'players_table'" ref="table">
                    <template #actions="props">
                        <div class="d-inline-flex gap-1">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                :title="isReadOnly ? 'Ver deportista' : 'Editar deportista'"
                                @click.stop="editPlayer(props.rowData.unique_code)"
                            >
                                <i :class="isReadOnly ? 'fa fa-eye' : 'fa fa-edit'"></i>
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
                        <div class="form-text">
                            El acudiente es opcional. Si lo incluyes, completa tanto
                            <strong>nombres_y_apellidos</strong> como <strong>numero_de_telefono</strong>;
                            <strong>identification_card</strong> es su documento y es opcional.
                            De lo contrario, deja los datos del acudiente vacíos y podrás agregarlo después.
                        </div>
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
import ContentState from '@/components/general/ContentState.vue'
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
const activeImport = ref(null)
const schoolOptions = ref([])
const importForm = reactive({
    school_id: '',
})

let importModal = null
let importPollTimer = null

const isSuperAdmin = computed(() => auth.hasRole('super-admin'))
const canImportPlayers = computed(() => auth.hasAnyRole(['super-admin', 'school']))
const isAssistant = computed(() => auth.hasRole('assistant'))
const isReadOnly = computed(() => isAssistant.value || auth.hasRole('viewer'))
const canSubmitImport = computed(() => {
    return !importing.value
        && Boolean(importFile.value)
        && (!isSuperAdmin.value || Boolean(importForm.school_id))
})
const importStatusClass = computed(() => ({
    'alert-info': ['pending', 'processing'].includes(activeImport.value?.status),
    'alert-success': activeImport.value?.status === 'completed',
    'alert-danger': activeImport.value?.status === 'failed',
}))
const importStatusTitle = computed(() => {
    const titles = {
        pending: 'Importación en cola',
        processing: 'Importando deportistas',
        completed: 'Importación completada',
        failed: 'La importación no pudo completarse',
    }

    return titles[activeImport.value?.status] || 'Estado de importación'
})
const importStatusMessage = computed(() => {
    if (activeImport.value?.status === 'completed') {
        const summary = activeImport.value.summary ?? {}
        return `${summary.created_players ?? 0} creados, ${summary.updated_players ?? 0} actualizados y ${summary.created_inscriptions ?? 0} inscripciones creadas.`
    }

    if (activeImport.value?.status === 'failed') {
        return activeImport.value.error_message || 'Consulta los logs para conocer el error.'
    }

    return `Archivo: ${activeImport.value?.filename || 'deportistas'}. Puedes continuar usando la plataforma.`
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
        activeImport.value = data.import
        closeImportModal()
        await window.Swal?.fire({
            icon: 'info',
            title: data.message || 'La importación quedó en cola.',
            text: 'Puedes continuar usando la plataforma mientras termina el proceso.',
            confirmButtonText: 'Entendido',
        })
        scheduleImportPoll()
    } catch (error) {
        if (error.response?.status === 409 && error.response?.data?.import) {
            activeImport.value = error.response.data.import
            scheduleImportPoll()
        }
        importError.value = error.response?.data?.message || 'No fue posible importar los deportistas.'
    } finally {
        importing.value = false
    }
}

const stopImportPoll = () => {
    if (importPollTimer !== null) {
        window.clearTimeout(importPollTimer)
        importPollTimer = null
    }
}

const scheduleImportPoll = () => {
    stopImportPoll()

    if (!activeImport.value?.id || !['pending', 'processing'].includes(activeImport.value.status)) {
        return
    }

    importPollTimer = window.setTimeout(pollImportStatus, 3000)
}

const pollImportStatus = async () => {
    if (!activeImport.value?.id) {
        return
    }

    try {
        const { data } = await api.get(`/api/v2/import/players/${activeImport.value.id}`, {
            skipGlobalLoader: true,
        })
        activeImport.value = data.import

        if (activeImport.value.status === 'completed') {
            reloadTable()
            await window.Swal?.fire({
                icon: 'success',
                title: 'Importación completada',
                text: importStatusMessage.value,
                confirmButtonText: 'Entendido',
            })
            return
        }

        if (activeImport.value.status === 'failed') {
            await window.Swal?.fire({
                icon: 'error',
                title: 'La importación no pudo completarse',
                text: importStatusMessage.value,
                confirmButtonText: 'Entendido',
            })
            return
        }
    } catch {
        // Un fallo temporal de consulta no cancela el proceso que sigue ejecutándose en el servidor.
    }

    scheduleImportPoll()
}

const loadActiveImport = async () => {
    try {
        const { data } = await api.get('/api/v2/import/players/latest', {
            skipGlobalLoader: true,
        })
        activeImport.value = data.import
        scheduleImportPoll()
    } catch {
        // El listado principal puede seguir funcionando aunque no se recupere este estado auxiliar.
    }
}

onMounted(() => {
    if (importModalElement.value) {
        importModal = new window.bootstrap.Modal(importModalElement.value, {
            backdrop: 'static',
            keyboard: false,
        })
    }

    loadActiveImport()
})

onBeforeUnmount(() => {
    stopImportPoll()
    importModal?.dispose()
})

</script>
