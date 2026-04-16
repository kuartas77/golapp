<template>
    <section class="position-relative">
        <Loader :is-loading="loading" loading-text="Cargando perfil..." />

        <div class="row justify-content-center">
            <div class="col-12 col-xl-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <p class="text-primary text-uppercase fw-semibold small mb-2">Perfil del acudiente</p>
                                <h1 class="h2 mb-1">Actualiza tu información</h1>
                                <p class="text-muted mb-0">Estos datos se usarán para mantener tus notificaciones y contacto al día.</p>
                            </div>
                        </div>

                        <div v-if="successMessage" class="alert alert-success" role="alert">
                            {{ successMessage }}
                        </div>

                        <div v-if="errorMessage" class="alert alert-danger" role="alert">
                            {{ errorMessage }}
                        </div>

                        <form class="row g-3" @submit.prevent="submitProfile">
                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-name" class="form-label">Nombres completos</label>
                                <input
                                    id="guardian-profile-name"
                                    v-model.trim="form.names"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.names }"
                                    required
                                >
                                <div v-if="fieldErrors.names" class="invalid-feedback d-block">{{ fieldErrors.names }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-id" class="form-label">Documento</label>
                                <input
                                    id="guardian-profile-id"
                                    :value="form.identification_card"
                                    type="text"
                                    class="form-control form-control-sm"
                                    readonly
                                >
                                <small class="text-muted">Este dato se mantiene solo lectura para conservar la relación de acceso.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-email" class="form-label">Correo electrónico</label>
                                <input
                                    id="guardian-profile-email"
                                    v-model.trim="form.email"
                                    type="email"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.email }"
                                    required
                                >
                                <div v-if="fieldErrors.email" class="invalid-feedback d-block">{{ fieldErrors.email }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-mobile" class="form-label">Celular</label>
                                <input
                                    id="guardian-profile-mobile"
                                    v-model.trim="form.mobile"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.mobile }"
                                >
                                <div v-if="fieldErrors.mobile" class="invalid-feedback d-block">{{ fieldErrors.mobile }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-phone" class="form-label">Teléfono</label>
                                <input
                                    id="guardian-profile-phone"
                                    v-model.trim="form.phone"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.phone }"
                                >
                                <div v-if="fieldErrors.phone" class="invalid-feedback d-block">{{ fieldErrors.phone }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-profession" class="form-label">Profesión</label>
                                <input
                                    id="guardian-profile-profession"
                                    v-model.trim="form.profession"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.profession }"
                                >
                                <div v-if="fieldErrors.profession" class="invalid-feedback d-block">{{ fieldErrors.profession }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-business" class="form-label">Empresa</label>
                                <input
                                    id="guardian-profile-business"
                                    v-model.trim="form.business"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.business }"
                                >
                                <div v-if="fieldErrors.business" class="invalid-feedback d-block">{{ fieldErrors.business }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="guardian-profile-position" class="form-label">Cargo</label>
                                <input
                                    id="guardian-profile-position"
                                    v-model.trim="form.position"
                                    type="text"
                                    class="form-control form-control-sm"
                                    :class="{ 'is-invalid': fieldErrors.position }"
                                >
                                <div v-if="fieldErrors.position" class="invalid-feedback d-block">{{ fieldErrors.position }}</div>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="btn btn-primary" :disabled="saving">
                                    {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                                </button>
                                <router-link :to="{ name: 'guardian-dashboard' }" class="btn btn-outline-primary">
                                    Volver a mis jugadores
                                </router-link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import Loader from '@/components/general/Loader.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';
import { useGuardianAuth } from '@/store/guardian-auth';

const guardianStore = useGuardianAuth();
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const fieldErrors = ref({});
const form = reactive({
    names: '',
    email: '',
    phone: '',
    mobile: '',
    profession: '',
    business: '',
    position: '',
    identification_card: '',
});

usePageTitle('Mi perfil');

const applyUser = (user) => {
    form.names = user?.names ?? '';
    form.email = user?.email ?? '';
    form.phone = user?.phone ?? '';
    form.mobile = user?.mobile ?? '';
    form.profession = user?.profession ?? '';
    form.business = user?.business ?? '';
    form.position = user?.position ?? '';
    form.identification_card = user?.identification_card ?? '';
};

const normalizeErrors = (error) => {
    const errors = error.response?.data?.errors ?? {};
    fieldErrors.value = Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
    );

    return error.response?.data?.message
        ?? Object.values(fieldErrors.value)[0]
        ?? 'No fue posible actualizar el perfil.';
};

const loadProfile = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const user = guardianStore.user ?? await guardianStore.getUser();
        applyUser(user);
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar el perfil.';
    } finally {
        loading.value = false;
    }
};

const submitProfile = async () => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    fieldErrors.value = {};

    try {
        const response = await api.put('/api/v2/portal/acudientes/profile', {
            names: form.names,
            email: form.email,
            phone: form.phone,
            mobile: form.mobile,
            profession: form.profession,
            business: form.business,
            position: form.position,
        });

        guardianStore.setUser(response.data?.data ?? response.data);
        applyUser(guardianStore.user);
        successMessage.value = response.data?.message || 'Perfil actualizado correctamente.';
    } catch (error) {
        errorMessage.value = normalizeErrors(error);
    } finally {
        saving.value = false;
    }
};

onMounted(loadProfile);
</script>
