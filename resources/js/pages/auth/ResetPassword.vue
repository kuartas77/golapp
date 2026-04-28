<template>
    <div class="form auth-boxed">
        <div class="form-container outer">
            <div class="form-form">
                <div class="form-form-wrap">
                    <div class="form-container">
                        <div class="form-content">
                            <div class="auth-brand" aria-label="Logo GOLAPP">
                                <img src="/img/logo-light.svg" alt="Logo GOLAPP"
                                    class="auth-brand-logo logo-light-mode" />
                                <img src="/img/logo-dark.svg" alt="Logo GOLAPP"
                                    class="auth-brand-logo logo-dark-mode" />
                            </div>

                            <Form :validation-schema="schema" :initial-values="formData" v-slot="{ values }"
                                class="text-start" @submit="submitReset">
                                <div class="form">
                                    <h4 class="text-center mt-2 mb-2">Restablecer contraseña</h4>
                                    <p class="text-muted mb-4">
                                        Define una nueva contraseña para volver a ingresar a GOLAPP.
                                    </p>

                                    <div v-if="errorMessage" class="alert alert-danger" role="alert">
                                        {{ errorMessage }}
                                    </div>

                                    <div v-if="!values.token" class="alert alert-warning" role="alert">
                                        El enlace no es válido o está incompleto. Revisa el correo e intenta nuevamente.
                                    </div>

                                    <div id="reset-email-field" class="field-wrapper input">
                                        <label for="reset-email">Correo electrónico</label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                            <path d="M4 4h16v16H4z"></path>
                                            <path d="m22 6-10 7L2 6"></path>
                                        </svg>
                                        <Field name="email" v-slot="{ field, errorMessage: fieldError, meta }">
                                            <input id="reset-email" v-bind="field" type="email"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': !meta.valid && fieldError }"
                                                autocomplete="email">
                                            <div :class="fieldError ? 'invalid-feedback d-block' : ''">{{ fieldError }}
                                            </div>
                                        </Field>
                                    </div>

                                    <div id="reset-password-field" class="field-wrapper input">
                                        <label for="reset-password">Nueva contraseña</label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <Field name="password" v-slot="{ field, errorMessage: fieldError, meta }">
                                            <input id="reset-password" v-bind="field" type="password"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': !meta.valid && fieldError }"
                                                autocomplete="new-password">
                                            <div :class="fieldError ? 'invalid-feedback d-block' : ''">{{ fieldError }}
                                            </div>
                                        </Field>
                                    </div>

                                    <div id="reset-password-confirmation-field" class="field-wrapper input">
                                        <label for="reset-password-confirmation">Confirmar contraseña</label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <Field name="password_confirmation"
                                            v-slot="{ field, errorMessage: fieldError, meta }">
                                            <input id="reset-password-confirmation" v-bind="field" type="password"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': !meta.valid && fieldError }"
                                                autocomplete="new-password">
                                            <div :class="fieldError ? 'invalid-feedback d-block' : ''">{{ fieldError }}
                                            </div>
                                        </Field>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary"
                                            :disabled="loading || !values.token">
                                            {{ loading ? 'Guardando...' : 'Guardar contraseña' }}
                                        </button>
                                        <router-link
                                            :to="{ name: 'login', query: { email: values.email || undefined } }"
                                            class="btn btn-outline-primary">
                                            Volver al ingreso
                                        </router-link>
                                    </div>
                                </div>
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import "@/assets/sass/authentication/auth-boxed.scss";
import { getCurrentInstance, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMeta } from '@/composables/use-meta';
import { useAuthUser } from '@/store/auth-user';
import api from '@/utils/axios';
import { Field, Form } from 'vee-validate';
import * as yup from 'yup';

const route = useRoute();
const router = useRouter();
const authStore = useAuthUser();
const { proxy } = getCurrentInstance();

const loading = ref(false);
const errorMessage = ref('');
const formData = ref({
    token: typeof route.query.token === 'string' ? route.query.token : '',
    email: typeof route.query.email === 'string' ? route.query.email : '',
    password: '',
    password_confirmation: '',
});

useMeta({ title: 'Restablecer contraseña' });

const schema = yup.object({
    email: yup.string().email().required(),
    password: yup.string()
        .required()
        .min(8)
        .matches(/[a-z]/, 'Debe incluir al menos una letra minúscula.')
        .matches(/[A-Z]/, 'Debe incluir al menos una letra mayúscula.')
        .matches(/[0-9]/, 'Debe incluir al menos un número.'),
    password_confirmation: yup.string()
        .required()
        .oneOf([yup.ref('password')], 'La confirmación no coincide con la contraseña.'),
    token: yup.string().nullable(),
});

const submitReset = async (values, actions) => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await authStore.resetPassword({ ...values });

        await window.Swal.fire({
            icon: 'success',
            title: 'Acceso actualizado',
            text: 'La contraseña fue actualizada correctamente.',
        });

        await router.push({
            name: 'login',
            query: {
                email: values.email,
                reset: '1',
            },
        });
    } catch (error) {
        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (message) => (errorMessage.value = message || 'No fue posible actualizar la contraseña.')
        );
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await api.get('/sanctum/csrf-cookie');
});
</script>