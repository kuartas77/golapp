import '@/bootstrap';
import '@/bones/registry';
import '@/utils/yup-locale';

import { createHead } from '@vueuse/head';
import * as bootstrap from 'bootstrap';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';

import App from '@/App.vue';
import appSetting from '@/app-setting';
import Checkbox from '@/components/form/Checkbox.vue';
import fileInputImage from '@/components/form/FileInputImage.vue';
import input from '@/components/form/Input.vue';
import CustomMultiSelect from '@/components/form/MultiSelect.vue';
import CustomSelect2 from '@/components/form/CustomSelect2.vue';
import Can from '@/components/general/Can.vue';
import breadcrumb from '@/components/layout/breadcrumb.vue';
import panel from '@/components/layout/panel.vue';
import tooltipDirective from '@/directives/tooltip';
import i18n from '@/i18n';
import errorHandler from '@/plugins/errorHandler';
import VueWizardSteps from '@/plugins/wizard';
import router from '@/router';
import { formatAppMoney } from '@/utils/appFormatters';

import '@/assets/sass/components/custom-modal.scss';
import '@/assets/sass/font-icons/fontawesome/css/fontawesome.css';
import '@/assets/sass/font-icons/fontawesome/css/regular.css';

window.bootstrap = bootstrap;

const SWEET_ALERT_OPTIONS = {
    confirmButtonColor: '#4361ee',
    cancelButtonColor: '#ff7674',
    cancelButtonText: 'Cancelar',
    didOpen: () => {
        const content = window.Swal?.getInput();
        if (content) {
            content.style.display = '';
        }
    }
};

const GLOBAL_DIRECTIVES = {
    tooltip: tooltipDirective
};

const GLOBAL_COMPONENTS = {
    breadcrumb,
    panel,
    inputField: input,
    inputFileImage: fileInputImage,
    CustomMultiSelect,
    checkbox: Checkbox,
    CustomSelect2,
    Can
};

const modalFocusOrigins = new WeakMap();
let modalAccessibilityInstalled = false;
let modalTitleSequence = 0;

function modalHidden() {
    if (modalAccessibilityInstalled) {
        return;
    }

    modalAccessibilityInstalled = true;

    document.addEventListener('show.bs.modal', (event) => {
        const modal = event.target;

        if (!(modal instanceof HTMLElement) || !modal.classList.contains('modal')) {
            return;
        }

        const activeElement = document.activeElement;
        if (activeElement instanceof HTMLElement && activeElement !== document.body) {
            modalFocusOrigins.set(modal, activeElement);
        }

        modal.setAttribute('role', modal.getAttribute('role') || 'dialog');
        modal.setAttribute('aria-modal', 'true');

        if (!modal.hasAttribute('aria-label') && !modal.hasAttribute('aria-labelledby')) {
            const title = modal.querySelector('.modal-title');
            if (title) {
                if (!title.id) {
                    modalTitleSequence += 1;
                    title.id = `app-modal-title-${modalTitleSequence}`;
                }
                modal.setAttribute('aria-labelledby', title.id);
            }
        }

        modal.querySelectorAll('.btn-close:not([aria-label])').forEach((button) => {
            button.setAttribute('aria-label', 'Cerrar');
        });
    });

    document.addEventListener('shown.bs.modal', (event) => {
        const modal = event.target;
        const activeElement = document.activeElement;

        if (
            !(modal instanceof HTMLElement)
            || (activeElement instanceof HTMLElement && activeElement !== modal && modal.contains(activeElement))
        ) {
            return;
        }

        const focusTarget = modal.querySelector(
            '[autofocus], .btn-close, button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [href]'
        );

        if (focusTarget instanceof HTMLElement) {
            focusTarget.focus({ preventScroll: true });
        }
    });

    document.addEventListener('hide.bs.modal', (event) => {
        const modal = event.target;

        if (modal instanceof HTMLElement && modal.contains(document.activeElement)) {
            document.activeElement?.blur();
        }
    });

    document.addEventListener('hidden.bs.modal', (event) => {
        const modal = event.target;

        if (!(modal instanceof HTMLElement)) {
            return;
        }

        const focusOrigin = modalFocusOrigins.get(modal);
        modalFocusOrigins.delete(modal);

        if (focusOrigin?.isConnected) {
            focusOrigin.focus({ preventScroll: true });
        }
    });
}

function showMessage(msg = '', type = 'success') {
    const toast = window.Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 5000
    });

    toast.fire({
        icon: type,
        title: msg,
        padding: '10px 20px'
    });
}

function moneyFormat(amount) {
    return formatAppMoney(amount);
}

async function registerPlugins(app, pinia, head, appConfig) {
    app.use(head);
    app.use(i18n);
    app.use(pinia);
    app.use(router);
    app.use(PerfectScrollbarPlugin);
    app.use(VueSweetalert2, SWEET_ALERT_OPTIONS);
    app.use(errorHandler);
    app.use(VueWizardSteps);

    if (appConfig.recaptchaSiteKey) {
        const { VueReCaptcha } = await import('vue-recaptcha-v3');

        app.use(VueReCaptcha, {
            siteKey: appConfig.recaptchaSiteKey,
            loaderOptions: {
                autoHideBadge: false
            }
        });
    }
}

function registerDirectives(app) {
    Object.entries(GLOBAL_DIRECTIVES).forEach(([name, directive]) => {
        app.directive(name, directive);
    });
}

function registerComponents(app) {
    Object.entries(GLOBAL_COMPONENTS).forEach(([name, component]) => {
        app.component(name, component);
    });
}

function registerGlobals(app, appConfig) {
    window.$appSetting = appSetting;
    window.$appSetting.init();
    window.__APP_CONFIG__ = appConfig;
    window.Swal = app.config.globalProperties.$swal;

    Object.assign(app.config.globalProperties, {
        modalHidden,
        showMessage,
        moneyFormat
    });

    Object.assign(window, {
        modalHidden,
        showMessage,
        moneyFormat
    });
}

async function bootstrapApp() {
    const app = createApp(App);
    const pinia = createPinia();
    const head = createHead();
    const appConfig = window.__APP_CONFIG__ ?? {};

    pinia.use(piniaPluginPersistedstate);

    await registerPlugins(app, pinia, head, appConfig);
    registerDirectives(app);
    registerComponents(app);
    registerGlobals(app, appConfig);

    app.mount('#app');
    modalHidden();
}

bootstrapApp();
