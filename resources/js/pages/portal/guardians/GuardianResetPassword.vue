<template>
    <section class="row justify-content-center">
        <div class="col-12 col-lg-7 col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <p class="text-primary text-uppercase fw-semibold small mb-2">Portal de acudientes</p>
                    <h1 class="h2 mb-3">Define tu contraseña</h1>
                    <p class="text-muted mb-4">
                        Completa este formulario para activar o restablecer tu acceso.
                    </p>

                    <div v-if="errorMessage" class="alert alert-danger" role="alert">
                        {{ errorMessage }}
                    </div>

                    <div v-if="!form.token" class="alert alert-warning" role="alert">
                        El enlace no es válido o está incompleto. Revisa el correo de invitación e intenta nuevamente.
                    </div>

                    <form class="row g-3" @submit.prevent="submitReset">
                        <div class="col-12">
                            <label for="reset-email" class="form-label">Correo electrónico</label>
                            <input
                                id="reset-email"
                                v-model.trim="form.email"
                                type="email"
                                class="form-control"
                                :class="{ 'is-invalid': fieldErrors.email }"
                                autocomplete="email"
                                required
                            >
                            <div v-if="fieldErrors.email" class="invalid-feedback d-block">{{ fieldErrors.email }}</div>
                        </div>

                        <div class="col-12">
                            <label for="reset-password" class="form-label">Nueva contraseña</label>
                            <input
                                id="reset-password"
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                :class="{ 'is-invalid': fieldErrors.password }"
                                autocomplete="new-password"
                                required
                            >
                            <div v-if="fieldErrors.password" class="invalid-feedback d-block">{{ fieldErrors.password }}</div>
                        </div>

                        <div class="col-12">
                            <label for="reset-password-confirmation" class="form-label">Confirmar contraseña</label>
                            <input
                                id="reset-password-confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="form-control"
                                :class="{ 'is-invalid': fieldErrors.password_confirmation }"
                                autocomplete="new-password"
                                required
                            >
                            <div v-if="fieldErrors.password_confirmation" class="invalid-feedback d-block">{{ fieldErrors.password_confirmation }}</div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="loading || !form.token">
                                {{ loading ? 'Guardando...' : 'Guardar contraseña' }}
                            </button>
                            <router-link :to="{ name: 'guardian-login' }" class="btn btn-outline-primary">
                                Volver al ingreso
                            </router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePageTitle } from '@/composables/use-meta';
import { useGuardianAuth } from '@/store/guardian-auth';

const route = useRoute();
const router = useRouter();
const guardianStore = useGuardianAuth();

const loading = ref(false);
const errorMessage = ref('');
const fieldErrors = ref({});
const form = reactive({
    token: typeof route.query.token === 'string' ? route.query.token : '',
    email: typeof route.query.email === 'string' ? route.query.email : '',
    password: '',
    password_confirmation: '',
});

usePageTitle('Restablecer acceso');

const normalizeErrors = (error) => {
    const errors = error.response?.data?.errors ?? {};
    fieldErrors.value = Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
    );

    return error.response?.data?.message
        ?? Object.values(fieldErrors.value)[0]
        ?? 'No fue posible actualizar la contraseña.';
};

const submitReset = async () => {
    loading.value = true;
    errorMessage.value = '';
    fieldErrors.value = {};

    try {
        await guardianStore.resetPassword({ ...form });

        await window.Swal.fire({
            icon: 'success',
            title: 'Portal de acudientes',
            text: 'La contraseña fue actualizada correctamente.',
        });

        await router.push({
            name: 'guardian-login',
            query: {
                email: form.email,
                reset: '1',
            },
        });
    } catch (error) {
        errorMessage.value = normalizeErrors(error);
    } finally {
        loading.value = false;
    }
};
</script>
