<template>
    <panel>
        <template #body>
            <p>Podrás encontrar todas las notificaciones generadas para la App GOLAPPLINK.</p>
            <span class="text-muted d-block mb-3">
                Las notificaciones se mostrarán en GOLAPPLINK sólo hasta después de 8 días de haber sido creadas.
            </span>

            <div class="d-flex justify-content-end mb-3 gap-2" data-tour="topic-notifications-actions">
                <button type="button" class="btn btn-info" @click="openCreateModal">
                    <i class="fa fa-plus me-1" aria-hidden="true"></i>
                    Crear Notificación
                </button>
                <button type="button" class="btn btn-info" @click="tutorial.start()">
                    <i class="fa-regular fa-circle-question me-2"></i>
                    Guia
                </button>
            </div>

            <div class="table-responsive-md" data-tour="topic-notifications-table">
                <DatatableTemplate :options="options" id="topic_notifications_table" ref="notificationsTable">
                    <template #thead>
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Topic</th>
                                <th>Título</th>
                                <th>Mensaje</th>
                                <th>Creada</th>
                                <th></th>
                            </tr>
                        </thead>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Notificaciones'" />
    <PageTutorialOverlay :tutorial="tutorial" />

    <div ref="modalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="submitForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><strong>Notificación</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <span class="text-muted d-block mb-4">
                            Las notificaciones se envían a tópicos a los cuales los usuarios de GOLAPPLINK se
                            suscriben al momento de ingresar a la App.
                        </span>

                        <div class="row">
                            <div class="col-md-12 col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label" for="notification_type">Notificación para</label>
                                    <select id="notification_type" v-model="form.notification_type"
                                        class="form-select">
                                        <option v-for="type in notificationTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <div v-if="getError('notification_type')" class="text-danger small mt-1">
                                        {{ getError('notification_type') }}
                                    </div>
                                </div>

                                <div v-if="form.notification_type === 'categories'" class="mb-3">
                                    <label class="form-label" for="categories">Categorías</label>
                                    <CustomSelect2
                                        id="categories"
                                        v-model="form.categories"
                                        :options="optionGroups.categories"
                                        multiple size="8"/>

                                    <div v-if="getError('categories')" class="text-danger small mt-1">
                                        {{ getError('categories') }}
                                    </div>
                                </div>

                                <div v-if="form.notification_type === 'training_groups'" class="mb-3">
                                    <label class="form-label" for="training_groups">Grupos de entrenamiento</label>
                                    <CustomSelect2
                                        id="training_groups"
                                        v-model="form.training_groups"
                                        :options="optionGroups.training_groups"
                                        multiple size="8"/>

                                    <div v-if="getError('training_groups')" class="text-danger small mt-1">
                                        {{ getError('training_groups') }}
                                    </div>
                                </div>

                                <div v-if="form.notification_type === 'competition_groups'" class="mb-3">
                                    <label class="form-label" for="competition_groups">Grupos de competencia</label>
                                    <CustomSelect2
                                        id="competition_groups"
                                        v-model="form.competition_groups"
                                        :options="optionGroups.competition_groups"
                                        multiple size="8"/>

                                    <div v-if="getError('competition_groups')" class="text-danger small mt-1">
                                        {{ getError('competition_groups') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-8">
                                <div class="mb-3">
                                    <label class="form-label" for="notification_title">Título</label>
                                    <input id="notification_title" v-model="form.notification_title" type="text"
                                        class="form-control" placeholder="Título" />
                                    <div v-if="getError('notification_title')" class="text-danger small mt-1">
                                        {{ getError('notification_title') }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="notification_body">Mensaje</label>
                                    <textarea id="notification_body" v-model="form.notification_body" rows="6"
                                        class="form-control" placeholder="Mensaje"></textarea>
                                    <div v-if="getError('notification_body')" class="text-danger small mt-1">
                                        {{ getError('notification_body') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-info" :disabled="submitting">
                            {{ submitting ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref, useTemplateRef, watch } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import { topicNotificationsTutorial } from '@/tutorials/notifications'

usePageTitle('Notificaciones')

const notificationsTable = useTemplateRef('notificationsTable')
const modalElement = ref(null)
const validationErrors = ref({})
const submitting = ref(false)
const tutorial = usePageTutorial(topicNotificationsTutorial)

let modalInstance = null

const notificationTypes = [
    { value: 'general', label: 'General "Todos los jugadores activos"' },
    { value: 'categories', label: 'Categorías' },
    { value: 'training_groups', label: 'Grupos de Entrenamiento' },
    { value: 'competition_groups', label: 'Grupos de Competencia' },
]

const optionGroups = reactive({
    categories: [],
    training_groups: [],
    competition_groups: [],
})

const createDefaultForm = () => ({
    notification_type: 'general',
    categories: [],
    training_groups: [],
    competition_groups: [],
    notification_title: '',
    notification_body: '',
})

const form = reactive(createDefaultForm())

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
    order: [[4, 'desc']],
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: async (data, callback) => {
        try {
            const response = await api.get('/notifications', { params: data })
            callback({
                data: response.data.data,
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            })
        } catch {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 })
        }
    },
    columnDefs: [
        { targets: [0], className: 'dt-body-center dt-head-center' },
    ],
    columns: [
        {
            data: 'id',
            searchable: false,
            orderable: true,
        },
        {
            data: 'topics',
            searchable: true,
            orderable: false,
        },
        {
            data: 'title',
            searchable: true,
            orderable: false,
        },
        {
            data: 'body',
            searchable: false,
            orderable: false,
        },
        {
            data: 'created_at',
            searchable: false,
            orderable: true,
            render: (data) => dayjs(data).format('DD-MM-YYYY hh:mm:ss a'),
        },
        {
            data: 'id',
            searchable: false,
            orderable: false,
            render: () => '',
        },
    ],
}

const resetForm = () => {
    Object.assign(form, createDefaultForm())
    validationErrors.value = {}
    submitting.value = false
}

const resetTopicSelections = () => {
    form.categories = []
    form.training_groups = []
    form.competition_groups = []
}

const reloadTable = () => {
    const dt = notificationsTable.value?.table?.dt

    if (dt) {
        dt.ajax.reload(null, false)
    }
}

const getError = (field) => validationErrors.value?.[field]?.[0] ?? ''

const loadOptions = async () => {
    const response = await api.get('/notifications/options')

    optionGroups.categories = response.data.categories ?? []
    optionGroups.training_groups = response.data.training_groups ?? []
    optionGroups.competition_groups = response.data.competition_groups ?? []
}

const openCreateModal = () => {
    resetForm()
    modalInstance?.show()
}

const submitForm = async () => {
    validationErrors.value = {}
    submitting.value = true

    try {
        await api.post('/notifications', {
            notification_type: form.notification_type,
            categories: form.categories,
            training_groups: form.training_groups,
            competition_groups: form.competition_groups,
            notification_title: form.notification_title,
            notification_body: form.notification_body,
        })

        modalInstance?.hide()
        await Swal.fire({
            title: window.__APP_CONFIG__?.appName ?? 'GOLAPP',
            text: 'Notificación creada correctamente',
            icon: 'success',
        })
        reloadTable()
    } catch (error) {
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors ?? {}
            return
        }

        await Swal.fire({
            title: window.__APP_CONFIG__?.appName ?? 'GOLAPP',
            text: 'No fue posible crear la notificación.',
            icon: 'error',
        })
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    if (modalElement.value) {
        modalInstance = new window.bootstrap.Modal(modalElement.value)
        modalElement.value.addEventListener('hidden.bs.modal', resetForm)
    }

    await loadOptions()
})

watch(() => form.notification_type, () => {
    resetTopicSelections()
    delete validationErrors.value.categories
    delete validationErrors.value.training_groups
    delete validationErrors.value.competition_groups
})
</script>
