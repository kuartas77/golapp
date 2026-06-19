<template>
    <section class="position-relative user-profile-page">
        <Loader :is-loading="loading" loading-text="Cargando perfil..." />

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div>
                                <p class="text-primary text-uppercase fw-semibold small mb-2">Mi perfil</p>
                                <h1 class="h3 mb-1">Información personal y profesional</h1>
                                <p class="text-muted mb-0">
                                    Estos datos ayudan a la escuela a mantener tu información actualizada.
                                </p>
                            </div>
                            <div v-if="profileData?.user" class="text-md-end">
                                <strong class="d-block">{{ profileData.user.name }}</strong>
                                <span class="text-muted profile-email">{{ profileData.user.email }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="alert alert-success" role="alert">
                    {{ successMessage }}
                </div>

                <div v-if="errorMessage" class="alert alert-danger" role="alert">
                    {{ errorMessage }}
                </div>

                <form class="profile-form" @submit.prevent="submitProfile">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Datos básicos</h2>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="profile-document" class="form-label">Documento</label>
                                    <input
                                        id="profile-document"
                                        v-model.trim="form.identification_document"
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': fieldErrors.identification_document }"
                                    >
                                    <div v-if="fieldErrors.identification_document" class="invalid-feedback d-block">
                                        {{ fieldErrors.identification_document }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="profile-birth" class="form-label">Fecha de nacimiento</label>
                                    <input
                                        id="profile-birth"
                                        v-model="form.date_birth"
                                        type="date"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': fieldErrors.date_birth }"
                                    >
                                    <div v-if="fieldErrors.date_birth" class="invalid-feedback d-block">
                                        {{ fieldErrors.date_birth }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="profile-gender" class="form-label">Genero</label>
                                    <select
                                        id="profile-gender"
                                        v-model="form.gender"
                                        class="form-select form-select-sm"
                                        :class="{ 'is-invalid': fieldErrors.gender }"
                                    >
                                        <option value="">Selecciona...</option>
                                        <option
                                            v-for="option in genderOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div v-if="fieldErrors.gender" class="invalid-feedback d-block">
                                        {{ fieldErrors.gender }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="profile-position" class="form-label">Cargo</label>
                                    <select
                                        id="profile-position"
                                        v-model="form.position"
                                        class="form-select form-select-sm"
                                        :class="{ 'is-invalid': fieldErrors.position }"
                                    >
                                        <option value="">Selecciona...</option>
                                        <option
                                            v-for="option in positionOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div v-if="fieldErrors.position" class="invalid-feedback d-block">
                                        {{ fieldErrors.position }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="profile-address" class="form-label">Dirección</label>
                                    <input
                                        id="profile-address"
                                        v-model.trim="form.address"
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': fieldErrors.address }"
                                    >
                                    <div v-if="fieldErrors.address" class="invalid-feedback d-block">
                                        {{ fieldErrors.address }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="profile-phone" class="form-label">Teléfono</label>
                                    <input
                                        id="profile-phone"
                                        v-model.trim="form.phone"
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': fieldErrors.phone }"
                                    >
                                    <div v-if="fieldErrors.phone" class="invalid-feedback d-block">
                                        {{ fieldErrors.phone }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="profile-mobile" class="form-label">Celular</label>
                                    <input
                                        id="profile-mobile"
                                        v-model.trim="form.mobile"
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': fieldErrors.mobile }"
                                    >
                                    <div v-if="fieldErrors.mobile" class="invalid-feedback d-block">
                                        {{ fieldErrors.mobile }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Trayectoria</h2>
                            <div class="row g-3">
                                <div
                                    v-for="field in longFields"
                                    :key="field.name"
                                    class="col-12 col-lg-6"
                                >
                                    <label :for="`profile-${field.name}`" class="form-label">{{ field.label }}</label>
                                    <textarea
                                        :id="`profile-${field.name}`"
                                        v-model.trim="form[field.name]"
                                        class="form-control form-control-sm"
                                        rows="5"
                                        :class="{ 'is-invalid': fieldErrors[field.name] }"
                                    />
                                    <div v-if="fieldErrors[field.name]" class="invalid-feedback d-block">
                                        {{ fieldErrors[field.name] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-outline-primary" :disabled="saving" @click="loadProfile">
                            Restaurar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Guardando...' : 'Guardar perfil' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <breadcrumb :parent="'Cuenta'" :current="'Mi perfil'" />
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import { usePageTitle } from '@/composables/use-meta'

const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = ref({})
const profileData = ref(null)
const genderOptions = ref([])
const positionOptions = ref([])

const form = reactive({
    date_birth: '',
    identification_document: '',
    gender: '',
    address: '',
    phone: '',
    mobile: '',
    studies: '',
    references: '',
    contacts: '',
    experience: '',
    position: '',
    aptitude: '',
})

const longFields = [
    { name: 'studies', label: 'Estudios' },
    { name: 'references', label: 'Referencias' },
    { name: 'contacts', label: 'Contactos' },
    { name: 'experience', label: 'Experiencia' },
    { name: 'aptitude', label: 'Aptitudes' },
]

usePageTitle('Mi perfil')

const applyProfile = (payload) => {
    profileData.value = payload
    genderOptions.value = payload?.gender_options || []
    positionOptions.value = payload?.position_options || []

    const profile = payload?.profile || {}
    Object.keys(form).forEach((field) => {
        form[field] = profile[field] ?? ''
    })
}

const normalizeErrors = (error) => {
    const errors = error.response?.data?.errors ?? {}
    fieldErrors.value = Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
    )

    return error.response?.data?.message
        || Object.values(fieldErrors.value)[0]
        || 'No fue posible guardar el perfil.'
}

const loadProfile = async () => {
    loading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    fieldErrors.value = {}

    try {
        const response = await api.get('/api/v2/profile')
        applyProfile(response.data.data)
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar el perfil.'
    } finally {
        loading.value = false
    }
}

const submitProfile = async () => {
    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''
    fieldErrors.value = {}

    try {
        const response = await api.put('/api/v2/profile', { ...form })
        applyProfile(response.data.data)
        successMessage.value = response.data.message || 'Perfil actualizado correctamente.'
    } catch (error) {
        errorMessage.value = normalizeErrors(error)
    } finally {
        saving.value = false
    }
}

onMounted(loadProfile)
</script>

<style scoped>
.profile-email {
    overflow-wrap: anywhere;
}

.profile-form {
    display: grid;
    gap: 1rem;
}
</style>
