<template>
    <section class="guardian-auth row justify-content-center">
        <div class="col-12 col-lg-7 col-xl-6">
            <div class="card border-0 shadow-sm guardian-auth__card">
                <div class="card-body p-4 p-md-5">
                    <p class="guardian-auth__eyebrow mb-2 text-muted">Portal de acudientes</p>
                    <h1 class="h2 mb-3">
                        {{ mode === 'login' ? 'Ingresa para ver tus jugadores' : 'Recupera tu acceso' }}
                    </h1>
                    <p class="text-muted mb-4">
                        {{ mode === 'login'
                            ? 'Usa el correo del acudiente principal y tu contraseña personal.'
                            : 'Te enviaremos un enlace para definir o restablecer tu contraseña.' }}
                    </p>

                    <!-- <div class="guardian-auth__switch mb-4">
                        <button
                            type="button"
                            class="btn"
                            :class="mode === 'login' ? 'btn-primary' : 'btn-primary'"
                            @click="setMode('login')"
                        >
                            Ingresar
                        </button>
                        <button
                            type="button"
                            class="btn"
                            :class="mode === 'recover' ? 'btn-primary' : 'btn-primary'"
                            @click="setMode('recover')"
                        >
                            Recuperar acceso
                        </button>
                    </div> -->

                    <div v-if="notice" class="alert alert-success" role="alert">
                        {{ notice }}
                    </div>

                    <div v-if="errorMessage" class="alert alert-danger" role="alert">
                        {{ errorMessage }}
                    </div>

                    <form v-if="mode === 'login'" class="row g-3" @submit.prevent="submitLogin">
                        <div class="col-12">
                            <label for="guardian-email" class="form-label">Correo electrónico</label>
                            <input
                                id="guardian-email"
                                v-model.trim="loginForm.email"
                                type="email"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldErrors.email }"
                                autocomplete="username"
                                required
                            >
                            <div v-if="fieldErrors.email" class="invalid-feedback d-block">{{ fieldErrors.email }}</div>
                        </div>

                        <div class="col-12">
                            <label for="guardian-password" class="form-label">Contraseña</label>
                            <input
                                id="guardian-password"
                                v-model="loginForm.password"
                                type="password"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldErrors.password }"
                                autocomplete="current-password"
                                required
                            >
                            <div v-if="fieldErrors.password" class="invalid-feedback d-block">{{ fieldErrors.password }}</div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? 'Ingresando...' : 'Ingresar' }}
                            </button>
                            <button type="button" class="btn btn-link px-0" @click="setMode('recover')">
                                ¿Olvidaste tu contraseña?
                            </button>
                        </div>
                    </form>

                    <form v-else class="row g-3" @submit.prevent="submitForgotPassword">
                        <div class="col-12">
                            <label for="guardian-recover-email" class="form-label">Correo electrónico</label>
                            <input
                                id="guardian-recover-email"
                                v-model.trim="recoverForm.email"
                                type="email"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldErrors.email }"
                                autocomplete="email"
                                required
                            >
                            <div v-if="fieldErrors.email" class="invalid-feedback d-block">{{ fieldErrors.email }}</div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? 'Enviando...' : 'Enviar instrucciones' }}
                            </button>
                            <button type="button" class="btn btn-primary" @click="setMode('login')">
                                Volver al ingreso
                            </button>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePageTitle } from '@/composables/use-meta';
import { useGuardianAuth } from '@/store/guardian-auth';

const route = useRoute();
const router = useRouter();
const guardianStore = useGuardianAuth();

const mode = ref('login');
const loading = ref(false);
const errorMessage = ref('');
const notice = ref(route.query.reset === '1'
    ? 'La contraseña fue actualizada correctamente. Ya puedes ingresar.'
    : ''
);
const fieldErrors = ref({});

const loginForm = reactive({
    email: typeof route.query.email === 'string' ? route.query.email : '',
    password: '',
});

const recoverForm = reactive({
    email: typeof route.query.email === 'string' ? route.query.email : '',
});

usePageTitle(computed(() => mode.value === 'recover' ? 'Recuperar acceso' : 'Ingreso acudientes'));

const firstErrorMessage = (error) => {
    const errors = error.response?.data?.errors ?? {};
    const normalizedErrors = Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
    );

    fieldErrors.value = normalizedErrors;

    return error.response?.data?.message
        ?? Object.values(normalizedErrors)[0]
        ?? 'No fue posible procesar la solicitud en este momento.';
};

const setMode = (nextMode) => {
    mode.value = nextMode;
    errorMessage.value = '';
    fieldErrors.value = {};
};

const submitLogin = async () => {
    loading.value = true;
    errorMessage.value = '';
    notice.value = '';
    fieldErrors.value = {};

    try {
        await guardianStore.login({
            email: loginForm.email,
            password: loginForm.password,
        });

        const redirect = typeof route.query.redirect === 'string'
            ? route.query.redirect
            : { name: 'guardian-dashboard' };

        await router.push(redirect);
    } catch (error) {
        errorMessage.value = firstErrorMessage(error);
    } finally {
        loading.value = false;
    }
};

const submitForgotPassword = async () => {
    loading.value = true;
    errorMessage.value = '';
    notice.value = '';
    fieldErrors.value = {};

    try {
        await guardianStore.forgotPassword(recoverForm.email);
        notice.value = 'Si existe una cuenta válida, enviaremos un enlace al correo registrado.';
        mode.value = 'login';
        loginForm.email = recoverForm.email;
    } catch (error) {
        errorMessage.value = firstErrorMessage(error);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.guardian-auth {
    padding-top: 1.5rem;
}

.guardian-auth__card {
    overflow: hidden;
    border-radius: 1.5rem;
}

.guardian-auth__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.78rem;
    font-weight: 700;
    color: #0f1c46;
}

.guardian-auth__switch {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.guardian-auth__footer {
    border-top: 1px solid rgba(15, 28, 70, 0.08);
}
</style>
