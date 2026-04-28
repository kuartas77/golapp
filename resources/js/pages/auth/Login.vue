<template>
    <div class="form auth-boxed">
        <div class="form-container outer">
            <div class="form-form">
                <div class="form-form-wrap">
                    <div class="form-container">
                        <div class="form-content">
                            <div class="auth-brand" aria-label="Logo GOLAPP">
                                <img src="/img/logo-light.svg" alt="Logo GOLAPP" class="auth-brand-logo logo-light-mode" />
                                <img src="/img/logo-dark.svg" alt="Logo GOLAPP" class="auth-brand-logo logo-dark-mode" />
                            </div>

                            <Form ref="form" :validation-schema="schema" @submit="handleLogin"
                                :initial-values="formData" class="text-start">
                                <div class="form">
                                    <div v-if="successMessage" class="alert alert-success" role="alert">
                                        {{ successMessage }}
                                    </div>

                                    <div id="username-field" class="field-wrapper input">
                                        <label for="email">Correo</label>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                            <path d="M4 4h16v16H4z"></path>
                                            <path d="m22 6-10 7L2 6"></path>
                                        </svg>
                                        <inputField type="text" name="email" />
                                    </div>

                                    <div id="password-field" class="field-wrapper input mb-2">
                                        <div class="d-flex justify-content-between">
                                            <label for="password">Contraseña</label>
                                            <router-link :to="{ name: 'forgot-password' }" class="forgot-pass-link">Recuperar contraseña</router-link>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <inputField :type="pwd_type" name="password" />
                                        <svg @click="set_pwd_type()" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            id="toggle-password" class="feather feather-eye">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div class="d-sm-flex justify-content-between">
                                        <span class="text-danger">{{ globalError }}</span>
                                    </div>
                                    <div class="d-sm-flex justify-content-between">
                                        <div class="field-wrapper">
                                            <button type="submit" class="btn btn-primary">Ingresar</button>
                                        </div>
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
import useFormLogin from '@/composables/auth/formLogin'
import { Form } from 'vee-validate'
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute()
const { form, formData, schema, handleLogin, pwd_type, globalError } = useFormLogin()
const successMessage = computed(() => route.query.reset === '1'
    ? 'La contraseña fue actualizada correctamente. Ya puedes ingresar.'
    : ''
)

const set_pwd_type = () => {
    if (pwd_type.value === "password") {
        pwd_type.value = "text";
    } else {
        pwd_type.value = "password";
    }
};

</script>
