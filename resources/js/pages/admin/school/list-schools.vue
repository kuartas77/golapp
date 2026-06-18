<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Escuelas</h3>
                    <p class="text-muted mb-0">
                        Administra el listado general, crea nuevas escuelas y ajusta qué módulos del backoffice están disponibles por escuela.
                    </p>
                </div>
                <div class="d-flex flex-column align-items-lg-end gap-2">
                    <div class="small text-muted">
                        Los permisos de escuela restringen acceso; no reemplazan los roles de usuario.
                    </div>
                    <div class="d-flex gap-2">
                        <router-link :to="{ name: 'schools.create' }" class="btn btn-primary btn-sm">
                            Crear escuela
                        </router-link>
                        <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                            <i class="fa-regular fa-circle-question me-2"></i>
                            Guia
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <template #body>
            <div data-tour="admin-schools-table">
                <DatatableTemplate :options="options" :id="'schools_table'" ref="table">
                <template #actions="props">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-center">
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            @click="goToEdit(props.rowData)"
                        >
                            Editar
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            data-tour="admin-schools-permissions"
                            @click="openPermissions(props.rowData)"
                        >
                            Permisos
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm"
                            @click="openDataExports(props.rowData)"
                        >
                            Exportar datos
                        </button>
                    </div>
                </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <div
        class="modal fade"
        id="schoolPermissionsModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="schoolPermissionsModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="schoolPermissionsModalLabel">
                            Permisos por escuela
                        </h5>
                        <small v-if="selectedSchool" class="text-muted">
                            {{ selectedSchool.name }}
                        </small>
                    </div>
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        :disabled="isSavingPermissions"
                        @click="closeModal"
                    ></button>
                </div>

                <div class="modal-body">
                    <div v-if="isModalLoading" class="py-5 text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted mb-0">Cargando permisos de la escuela...</p>
                    </div>

                    <template v-else-if="selectedSchool">
                        <div v-if="modalError" class="alert alert-danger">
                            {{ modalError }}
                        </div>

                        <div class="border rounded p-3 mb-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-8">
                                    <h6 class="mb-2">Regla de acceso efectiva</h6>
                                    <p class="text-muted mb-0">
                                        Un usuario necesita cumplir su rol y además que el módulo esté habilitado para la escuela actual.
                                    </p>
                                </div>
                                <div class="col-lg-4 text-lg-end">
                                    <div class="fw-semibold">{{ enabledPermissionsCount }} de {{ totalPermissionsCount }} permisos activos</div>
                                    <small class="text-muted">Incluye módulos y funciones adicionales.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-4">
                            <section v-for="group in groupedCatalog" :key="group.group">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">{{ group.group }}</h6>
                                    <small class="text-muted">
                                        {{ group.items.filter((permission) => permissionForm[permission.key]).length }} activos
                                    </small>
                                </div>

                                <div class="row g-3">
                                    <div
                                        v-for="permission in group.items"
                                        :key="permission.key"
                                        class="col-12 col-lg-6"
                                    >
                                        <div class="border rounded p-3 h-100">
                                            <div class="form-check form-switch">
                                                <input
                                                    :id="`permission-${permission.key}`"
                                                    v-model="permissionForm[permission.key]"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :disabled="isSavingPermissions"
                                                >
                                                <label class="form-check-label fw-semibold" :for="`permission-${permission.key}`">
                                                    {{ permission.label }}
                                                </label>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2">
                                                {{ permission.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </template>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" :disabled="isSavingPermissions" @click="closeModal">
                        Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" :disabled="isModalLoading || isSavingPermissions" @click="savePermissions">
                        {{ isSavingPermissions ? 'Guardando...' : 'Guardar permisos' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal fade"
        id="schoolDataExportsModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="schoolDataExportsModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="schoolDataExportsModalLabel">
                            Exportación masiva de datos
                        </h5>
                        <small v-if="selectedExportSchool" class="text-muted">
                            {{ selectedExportSchool.name }}
                        </small>
                    </div>
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        :disabled="isRequestingExport"
                        @click="closeDataExportsModal"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        Este paquete contiene información personal completa de la escuela. Descárgalo y compártelo solo por canales autorizados.
                    </div>

                    <div v-if="isLoadingExports" class="py-5 text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted mb-0">Cargando historial de exportaciones...</p>
                    </div>

                    <template v-else>
                        <div v-if="exportsError" class="alert alert-danger">
                            {{ exportsError }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Historial reciente</h6>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                :disabled="isRequestingExport || !selectedExportSchool"
                                @click="requestDataExport"
                            >
                                {{ isRequestingExport ? 'Solicitando...' : 'Nueva exportación' }}
                            </button>
                        </div>

                        <div v-if="dataExports.length === 0" class="text-center text-muted border rounded py-4">
                            No hay exportaciones solicitadas para esta escuela.
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Estado</th>
                                        <th>Solicitada</th>
                                        <th>Solicitante</th>
                                        <th>Tamaño</th>
                                        <th>Vence</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in dataExports" :key="item.id">
                                        <td>
                                            <span class="badge" :class="statusClass(item.status)">
                                                {{ statusLabel(item.status) }}
                                            </span>
                                            <div v-if="item.error_message" class="small text-danger mt-1">
                                                {{ item.error_message }}
                                            </div>
                                        </td>
                                        <td>{{ formatDateTime(item.created_at) }}</td>
                                        <td>{{ item.requested_by?.name || 'Sistema' }}</td>
                                        <td>{{ item.size_label || '-' }}</td>
                                        <td>{{ formatDateTime(item.expires_at) }}</td>
                                        <td class="text-end">
                                            <a
                                                v-if="item.download_url"
                                                :href="item.download_url"
                                                class="btn btn-success btn-sm"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                Descargar
                                            </a>
                                            <span v-else class="text-muted small">
                                                No disponible
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" :disabled="isRequestingExport" @click="closeDataExportsModal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <breadcrumb :parent="'Adminstración'" :current="'Escuelas'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import { computed, onMounted, ref } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import useSchoolList from '@/composables/admin/school/schoolList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from "@/composables/use-meta";
import { useRouter } from 'vue-router'
import { useAuthUser } from '@/store/auth-user'
import { schoolsListTutorial } from '@/tutorials/admin'

usePageTitle('Escuelas')

const { table, options } = useSchoolList()
const tutorial = usePageTutorial(schoolsListTutorial)
const router = useRouter()
const auth = useAuthUser()

const modalError = ref('')
const selectedSchool = ref(null)
const permissionCatalog = ref([])
const permissionForm = ref({})
const isModalLoading = ref(false)
const isSavingPermissions = ref(false)
const permissionsModal = ref(null)
const dataExportsModal = ref(null)
const selectedExportSchool = ref(null)
const dataExports = ref([])
const isLoadingExports = ref(false)
const isRequestingExport = ref(false)
const exportsError = ref('')

const groupedCatalog = computed(() => {
    const groups = {}

    permissionCatalog.value.forEach((permission) => {
        if (!groups[permission.group]) {
            groups[permission.group] = []
        }

        groups[permission.group].push(permission)
    })

    return Object.entries(groups).map(([group, items]) => ({ group, items }))
})

const enabledPermissionsCount = computed(() => (
    Object.values(permissionForm.value).filter(Boolean).length
))

const totalPermissionsCount = computed(() => permissionCatalog.value.length)

const reloadTable = () => {
    const dataTable = table.value?.table?.dt
    if (dataTable) {
        dataTable.clearPipeline()
        dataTable.ajax.reload(null, false)
    }
}

const resetModalState = () => {
    modalError.value = ''
    selectedSchool.value = null
    permissionCatalog.value = []
    permissionForm.value = {}
    isModalLoading.value = false
    isSavingPermissions.value = false
}

const closeModal = () => {
    permissionsModal.value?.hide()
}

const closeDataExportsModal = () => {
    dataExportsModal.value?.hide()
}

const goToEdit = (school) => {
    router.push({
        name: 'schools.edit',
        params: { slug: school.slug },
    })
}

const openPermissions = async (school) => {
    modalError.value = ''
    isModalLoading.value = true
    selectedSchool.value = {
        id: school.id,
        name: school.name,
        slug: school.slug,
    }

    permissionsModal.value?.show()

    try {
        const { data } = await api.get(`/api/v2/admin/schools/${school.slug}/permissions`)
        selectedSchool.value = data.school
        permissionCatalog.value = data.catalog || []
        permissionForm.value = { ...(data.permissions || {}) }
    } catch (error) {
        modalError.value = error.response?.data?.message || 'No fue posible cargar los permisos de la escuela.'
        showMessage(modalError.value, 'error')
    } finally {
        isModalLoading.value = false
    }
}

const savePermissions = async () => {
    if (!selectedSchool.value) {
        return
    }

    modalError.value = ''
    isSavingPermissions.value = true

    try {
        await api.put(`/api/v2/admin/schools/${selectedSchool.value.slug}/permissions`, {
            permissions: permissionForm.value,
        })

        await auth.init({ force: true, silent: true, preserveStateOnError: true })
        showMessage('Permisos de escuela actualizados correctamente.')
        reloadTable()
        closeModal()
    } catch (error) {
        modalError.value = error.response?.data?.message || 'No fue posible actualizar los permisos de la escuela.'
        showMessage(modalError.value, 'error')
    } finally {
        isSavingPermissions.value = false
    }
}

const resetDataExportsState = () => {
    selectedExportSchool.value = null
    dataExports.value = []
    exportsError.value = ''
    isLoadingExports.value = false
    isRequestingExport.value = false
}

const loadDataExports = async () => {
    if (!selectedExportSchool.value) {
        return
    }

    exportsError.value = ''
    isLoadingExports.value = true

    try {
        const { data } = await api.get(`/api/v2/admin/schools/${selectedExportSchool.value.slug}/data-exports`)
        dataExports.value = data.data || []
    } catch (error) {
        exportsError.value = error.response?.data?.message || 'No fue posible cargar el historial de exportaciones.'
        showMessage(exportsError.value, 'error')
    } finally {
        isLoadingExports.value = false
    }
}

const openDataExports = async (school) => {
    selectedExportSchool.value = {
        id: school.id,
        name: school.name,
        slug: school.slug,
    }
    dataExportsModal.value?.show()
    await loadDataExports()
}

const requestDataExport = async () => {
    if (!selectedExportSchool.value) {
        return
    }

    isRequestingExport.value = true
    exportsError.value = ''

    try {
        await api.post(`/api/v2/admin/schools/${selectedExportSchool.value.slug}/data-exports`)
        showMessage('La exportación fue solicitada. El archivo aparecerá cuando termine el procesamiento.')
        await loadDataExports()
    } catch (error) {
        exportsError.value = error.response?.data?.message || 'No fue posible solicitar la exportación.'
        showMessage(exportsError.value, 'error')
    } finally {
        isRequestingExport.value = false
    }
}

const statusLabel = (status) => ({
    pending: 'Pendiente',
    processing: 'Procesando',
    ready: 'Listo',
    failed: 'Fallido',
    expired: 'Expirado',
}[status] || status)

const statusClass = (status) => ({
    pending: 'bg-secondary',
    processing: 'bg-info',
    ready: 'bg-success',
    failed: 'bg-danger',
    expired: 'bg-dark',
}[status] || 'bg-secondary')

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}

onMounted(() => {
    const permissionsElement = document.getElementById('schoolPermissionsModal')
    permissionsModal.value = new window.bootstrap.Modal(permissionsElement, {
        backdrop: 'static',
        keyboard: false,
        focus: false,
    })

    permissionsElement?.addEventListener('hidden.bs.modal', resetModalState)

    const exportsElement = document.getElementById('schoolDataExportsModal')
    dataExportsModal.value = new window.bootstrap.Modal(exportsElement, {
        backdrop: 'static',
        keyboard: false,
        focus: false,
    })

    exportsElement?.addEventListener('hidden.bs.modal', resetDataExportsState)
})
</script>
