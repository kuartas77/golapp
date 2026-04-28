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
                                class="text-start" @submit="submitForgotPassword">
                                <div class="form">
                                    <h4 class="text-center mt-2 mb-2">Recuperar contraseña</h4>
                                    <p class="text-muted mb-4">
                                        Ingresa el correo de tu cuenta y te enviaremos un enlace para restablecer la
                                        contraseña.
                                    </p>

                                    <div v-if="notice" class="alert alert-success" role="alert">
                                        {{ notice }}
                                    </div>

                                    <div v-if="errorMessage" class="alert alert-danger" role="alert">
                                        {{ errorMessage }}
                                    </div>

                                    <div id="recover-email-field" class="field-wrapper input mb-3">
                                        <label for="recover-email">Correo electrónico</label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                            <path d="M4 4h16v16H4z"></path>
                                            <path d="m22 6-10 7L2 6"></path>
                                        </svg>
                                        <Field name="email" v-slot="{ field, errorMessage: fieldError, meta }">
                                            <input id="recover-email" v-bind="field" type="email"
                                                class="form-control form-control-sm"
                                                :class="{ 'is-invalid': !meta.valid && fieldError }"
                                                autocomplete="email" />
                                            <div :class="fieldError ? 'invalid-feedback d-block' : ''">
                                                {{ fieldError }}
                                            </div>
                                        </Field>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary" :disabled="loading">
                                            {{ loading ? 'Enviando...' : 'Enviar instrucciones' }}
                                        </button>
                                        <router-link :to="{ name: 'login', query: { email: values.email || undefined } }"
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
import { useRoute } from 'vue-router';
import { useMeta } from '@/composables/use-meta';
import { useAuthUser } from '@/store/auth-user';
import api from '@/utils/axios';
import { Field, Form } from 'vee-validate';
import * as yup from 'yup';

const route = useRoute();
const authStore = useAuthUser();
const { proxy } = getCurrentInstance();

const loading = ref(false);
const errorMessage = ref('');
const notice = ref('');
const formData = ref({
    email: typeof route.query.email === 'string' ? route.query.email : '',
});

useMeta({ title: 'Recuperar contraseña' });

const schema = yup.object({
    email: yup.string().email().required(),
});

const submitForgotPassword = async (values, actions) => {
    loading.value = true;
    errorMessage.value = '';
    notice.value = '';

    try {
        await authStore.forgotPassword(values.email);
        notice.value = 'Si existe una cuenta válida, recibirás instrucciones en tu correo.';
    } catch (error) {
        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (message) => (errorMessage.value = message || 'No fue posible enviar las instrucciones de recuperación.')
        );
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await api.get('/sanctum/csrf-cookie');
});
</script>
