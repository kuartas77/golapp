<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Contratos</h3>
                    <p class="text-muted mb-0">
                        Administra las plantillas de contratos de la escuela activa y reutilizadas por el portal publico.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" :disabled="isLoading" @click="loadPage">
                        Recargar
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="isLoading || isSaving || !selectedType"
                        @click="submitForm"
                    >
                        {{ isSaving ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative contracts-admin-page">
                <Loader :is-loading="isLoading" loading-text="Cargando contratos..." />

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="loadPage">
                        Reintentar
                    </button>
                </div>

                <template v-if="!isLoading && school && selectedType">
                    <div class="row g-4">
                        <div class="col-12 col-xl-4 col-xxl-3">
                            <div class="surface-card card mb-4">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Escuela activa</div>
                                    <h5 class="mb-1">{{ school.name }}</h5>
                                    <p class="text-muted mb-0">
                                        El super-admin edita sobre la escuela seleccionada actualmente.
                                    </p>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="d-flex flex-column gap-2">
                                        <span class="theme-chip" :class="school.create_contract ? 'is-success' : 'is-warning'">
                                            {{ school.create_contract ? 'Flujo contractual activo' : 'Flujo contractual inactivo' }}
                                        </span>
                                        <span class="theme-chip" :class="school.sign_player ? 'is-info' : 'is-muted'">
                                            {{ school.sign_player ? 'Firma de deportista habilitada' : 'Solo firma del acudiente' }}
                                        </span>
                                    </div>

                                    <div v-if="!school.create_contract" class="alert alert-warning mt-3 mb-0">
                                        La escuela puede preparar las plantillas desde ahora, pero el portal solo las mostrara cuando `create_contract` este activo.
                                    </div>
                                </div>
                            </div>

                            <div class="surface-card card">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Tipos</div>
                                    <h5 class="mb-1">Plantillas disponibles</h5>
                                    <p class="text-muted mb-0">
                                        El catalogo lo define backend segun los tipos soportados.
                                    </p>
                                </div>
                                <div class="surface-card-body card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <button
                                            v-for="type in types"
                                            :key="type.code"
                                            type="button"
                                            class="list-group-item list-group-item-action text-start"
                                            :class="{ active: selectedType?.code === type.code }"
                                            @click="selectType(type.code)"
                                        >
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ type.label }}</div>
                                                    <div class="small text-muted mt-1">{{ type.description }}</div>
                                                </div>
                                                <span class="badge" :class="type.configured ? 'bg-success' : 'bg-secondary'">
                                                    {{ type.configured ? 'Configurado' : 'Pendiente' }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8 col-xxl-9">
                            <div class="surface-card card mb-4">
                                <div class="surface-card-header card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <div class="section-label mb-2">Editor</div>
                                        <h5 class="mb-1">{{ selectedType.label }}</h5>
                                        <p class="text-muted mb-0">
                                            {{ selectedType.description }}
                                        </p>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 align-self-start">
                                        <a
                                            v-if="selectedType.preview_url"
                                            :href="selectedType.preview_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-outline-secondary btn-sm"
                                        >
                                            Ver PDF de ejemplo
                                        </a>
                                        <span class="theme-chip" :class="selectedType.configured ? 'is-success' : 'is-warning'">
                                            {{ selectedType.configured ? 'Plantilla lista' : 'Falta completar contenido' }}
                                        </span>
                                        <span class="theme-chip" :class="dirty ? 'is-info' : 'is-muted'">
                                            {{ dirty ? 'Cambios sin guardar' : 'Sin cambios locales' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="surface-card-body card-body">
                                    <p v-if="selectedType.preview_url" class="text-muted small mb-4">
                                        La vista previa usa datos de ejemplo y refleja la ultima version guardada.
                                    </p>

                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-lg-7">
                                            <label class="form-label">Nombre</label>
                                            <input
                                                v-model.trim="form.name"
                                                type="text"
                                                class="form-control"
                                                :class="{ 'is-invalid': Boolean(validationErrors.name) }"
                                                placeholder="Nombre interno de la plantilla"
                                            >
                                            <div v-if="validationErrors.name" class="invalid-feedback d-block">
                                                {{ validationErrors.name }}
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Reglas de portal</label>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                <span class="theme-chip" :class="selectedType.portal.requires_acceptance ? 'is-primary' : 'is-muted'">
                                                    {{ selectedType.portal.requires_acceptance ? 'Requiere aceptacion' : 'Sin aceptacion obligatoria' }}
                                                </span>
                                                <span class="theme-chip" :class="selectedType.portal.requires_tutor_signature ? 'is-primary' : 'is-muted'">
                                                    {{ selectedType.portal.requires_tutor_signature ? 'Firma acudiente' : 'Sin firma acudiente' }}
                                                </span>
                                                <span class="theme-chip" :class="selectedType.portal.requires_player_signature ? 'is-primary' : 'is-muted'">
                                                    {{ selectedType.portal.requires_player_signature ? 'Firma deportista' : 'Sin firma deportista' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Encabezado</label>
                                        <TinyMceEditor
                                            v-model="form.header"
                                            :allow-page-break="false"
                                            :min-height="220"
                                        />
                                        <div v-if="validationErrors.header" class="invalid-feedback d-block">
                                            {{ validationErrors.header }}
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Cuerpo</label>
                                        <TinyMceEditor
                                            v-model="form.body"
                                            :allow-page-break="true"
                                            :min-height="360"
                                        />
                                        <div v-if="validationErrors.body" class="invalid-feedback d-block">
                                            {{ validationErrors.body }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">Pie de pagina</label>
                                        <TinyMceEditor
                                            v-model="form.footer"
                                            :allow-page-break="false"
                                            :min-height="220"
                                        />
                                        <div v-if="validationErrors.footer" class="invalid-feedback d-block">
                                            {{ validationErrors.footer }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="surface-card card">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Ayudas</div>
                                    <h5 class="mb-1">Placeholders disponibles</h5>
                                    <p class="text-muted mb-0">
                                        Usa los tokens entre corchetes exactamente como aparecen. El backend recalcula los parametros usados al guardar.
                                    </p>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-xxl-8">
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Token</th>
                                                            <th>Descripcion</th>
                                                            <th>Ejemplo</th>
                                                            <th class="text-end">Accion</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="placeholder in placeholders" :key="placeholder.token">
                                                            <td class="fw-semibold">{{ placeholder.token }}</td>
                                                            <td>{{ placeholder.description }}</td>
                                                            <td><code>{{ placeholder.example }}</code></td>
                                                            <td class="text-end">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-outline-primary btn-sm"
                                                                    @click="copyPlaceholder(placeholder.token)"
                                                                >
                                                                    Copiar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-12 col-xxl-4">
                                            <div class="contracts-admin-page__summary card h-100">
                                                <div class="card-body">
                                                    <h6 class="mb-3">Parametros detectados</h6>
                                                    <div v-if="selectedType.template.used_parameters.length" class="d-flex flex-wrap gap-2">
                                                        <span
                                                            v-for="parameter in selectedType.template.used_parameters"
                                                            :key="parameter"
                                                            class="theme-chip is-muted"
                                                        >
                                                            {{ parameter }}
                                                        </span>
                                                    </div>
                                                    <p v-else class="text-muted mb-0">
                                                        Aun no hay placeholders guardados para esta plantilla.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Administración'" :current="'Contratos'" />
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TinyMceEditor from '@/components/general/TinyMceEditor.vue'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import { usePageTitle } from '@/composables/use-meta'

usePageTitle('Contratos')

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const isSaving = ref(false)
const globalError = ref('')
const school = ref(null)
const types = ref([])
const activeTypeCode = ref('')
const snapshot = ref({
    name: '',
    header: '',
    body: '',
    footer: '',
})
const form = reactive({
    name: '',
    header: '',
    body: '',
    footer: '',
})
const validationErrors = reactive({
    name: '',
    header: '',
    body: '',
    footer: '',
})

const selectedType = computed(() => types.value.find((type) => type.code === activeTypeCode.value) ?? null)
const placeholders = computed(() => selectedType.value?.help?.placeholders ?? [])
const dirty = computed(() => (
    form.name !== snapshot.value.name
    || form.header !== snapshot.value.header
    || form.body !== snapshot.value.body
    || form.footer !== snapshot.value.footer
))

const resetValidationErrors = () => {
    validationErrors.name = ''
    validationErrors.header = ''
    validationErrors.body = ''
    validationErrors.footer = ''
}

const hydrateForm = (type) => {
    const template = type?.template ?? {}

    form.name = template.name ?? ''
    form.header = template.header ?? ''
    form.body = template.body ?? ''
    form.footer = template.footer ?? ''

    snapshot.value = {
        name: form.name,
        header: form.header,
        body: form.body,
        footer: form.footer,
    }

    resetValidationErrors()
}

const persistActiveTypeInQuery = async (code) => {
    const currentType = typeof route.query.type === 'string' ? route.query.type : ''

    if (currentType === code) {
        return
    }

    await router.replace({
        query: {
            ...route.query,
            type: code,
        },
    })
}

const selectType = async (code) => {
    const type = types.value.find((item) => item.code === code)

    if (!type) {
        return
    }

    activeTypeCode.value = code
    hydrateForm(type)
    await persistActiveTypeInQuery(code)
}

const loadPage = async () => {
    isLoading.value = true
    globalError.value = ''

    try {
        const { data } = await api.get('/api/v2/admin/contracts')

        school.value = data.school ?? null
        types.value = data.types ?? []

        const queryType = typeof route.query.type === 'string' ? route.query.type : ''
        const initialType = types.value.find((type) => type.code === queryType) ?? types.value[0] ?? null

        if (!initialType) {
            activeTypeCode.value = ''
            return
        }

        activeTypeCode.value = initialType.code
        hydrateForm(initialType)
        await persistActiveTypeInQuery(initialType.code)
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible cargar las plantillas de contratos.'
    } finally {
        isLoading.value = false
    }
}

const validateForm = () => {
    resetValidationErrors()

    if (!form.name.trim()) {
        validationErrors.name = 'Ingresa un nombre para la plantilla.'
    }

    if (!form.header.trim()) {
        validationErrors.header = 'Ingresa el encabezado de la plantilla.'
    }

    if (!form.body.trim()) {
        validationErrors.body = 'Ingresa el cuerpo de la plantilla.'
    }

    if (!form.footer.trim()) {
        validationErrors.footer = 'Ingresa el pie de pagina de la plantilla.'
    }

    return !Object.values(validationErrors).some(Boolean)
}

const replaceType = (updatedType) => {
    types.value = types.value.map((type) => (
        type.code === updatedType.code
            ? updatedType
            : type
    ))
}

const submitForm = async () => {
    if (!selectedType.value || !validateForm()) {
        return
    }

    isSaving.value = true
    globalError.value = ''

    try {
        const payload = {
            name: form.name.trim(),
            header: form.header,
            body: form.body,
            footer: form.footer,
        }

        const { data } = await api.put(`/api/v2/admin/contracts/${selectedType.value.code}`, payload)
        const updatedType = data.data

        replaceType(updatedType)
        hydrateForm(updatedType)
        showMessage(data.message || 'Contrato guardado correctamente.')
    } catch (error) {
        const errors = error.response?.data?.errors ?? {}

        validationErrors.name = Array.isArray(errors.name) ? errors.name[0] : validationErrors.name
        validationErrors.header = Array.isArray(errors.header) ? errors.header[0] : validationErrors.header
        validationErrors.body = Array.isArray(errors.body) ? errors.body[0] : validationErrors.body
        validationErrors.footer = Array.isArray(errors.footer) ? errors.footer[0] : validationErrors.footer
        globalError.value = error.response?.data?.message || 'No fue posible guardar la plantilla seleccionada.'
    } finally {
        isSaving.value = false
    }
}

const copyPlaceholder = async (token) => {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(token)
        } else {
            const textarea = document.createElement('textarea')
            textarea.value = token
            textarea.setAttribute('readonly', 'readonly')
            textarea.style.position = 'fixed'
            textarea.style.left = '-9999px'
            document.body.appendChild(textarea)
            textarea.select()
            document.execCommand('copy')
            document.body.removeChild(textarea)
        }

        showMessage(`Token copiado: ${token}`)
    } catch (error) {
        showMessage('No fue posible copiar el token.', 'error')
    }
}

loadPage()
</script>

<style scoped>
.contracts-admin-page :deep(.list-group-item.active) {
    background: rgba(13, 110, 253, 0.1);
    color: inherit;
    border-color: rgba(13, 110, 253, 0.25);
}

.theme-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.35rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1px solid transparent;
}

.theme-chip.is-success {
    background: rgba(25, 135, 84, 0.12);
    color: #198754;
}

.theme-chip.is-warning {
    background: rgba(255, 193, 7, 0.16);
    color: #997404;
}

.theme-chip.is-info {
    background: rgba(13, 202, 240, 0.14);
    color: #087990;
}

.theme-chip.is-muted {
    background: rgba(108, 117, 125, 0.12);
    color: #6c757d;
}

.theme-chip.is-primary {
    background: rgba(13, 110, 253, 0.12);
    color: #0d6efd;
}

.contracts-admin-page__summary {
    border: 1px dashed rgba(108, 117, 125, 0.28);
}

.section-label {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6c757d;
}
</style>
