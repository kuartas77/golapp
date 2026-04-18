<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Escuelas</h3>
                    <p class="text-muted mb-0">
                        Administra el listado general y ajusta qué módulos del backoffice están disponibles por escuela.
                    </p>
                </div>
                <div class="d-flex flex-column align-items-lg-end gap-2">
                    <div class="small text-muted">
                        Los permisos de escuela restringen acceso; no reemplazan los roles de usuario.
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="tutorial.start()">
                        Guia
                    </button>
                </div>
            </div>
        </template>
        <template #body>
            <div data-tour="admin-schools-table">
                <DatatableTemplate :options="options" :id="'schools_table'" ref="table">
                <template #actions="props">
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        data-tour="admin-schools-permissions"
                        @click="openPermissions(props.rowData)"
                    >
                        Permisos
                    </button>
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

    <breadcrumb :parent="'Adminstración'" :current="'Escuelas'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import { computed, onMounted, ref } from 'vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import useSchoolList from '@/composables/admin/school/schoolList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from "@/composables/use-meta";
import { schoolsListTutorial } from '@/tutorials/admin'

usePageTitle('Escuelas')

const { table, options } = useSchoolList()
const tutorial = usePageTutorial(schoolsListTutorial)

const modalError = ref('')
const selectedSchool = ref(null)
const permissionCatalog = ref([])
const permissionForm = ref({})
const isModalLoading = ref(false)
const isSavingPermissions = ref(false)
const permissionsModal = ref(null)

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

onMounted(() => {
    const element = document.getElementById('schoolPermissionsModal')
    permissionsModal.value = new window.bootstrap.Modal(element, {
        backdrop: 'static',
        keyboard: false,
        focus: false,
    })

    element?.addEventListener('hidden.bs.modal', resetModalState)
})
</script>
