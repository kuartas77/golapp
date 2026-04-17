import '@/bootstrap';
import '@/utils/yup-locale';

import { createHead } from '@vueuse/head';
import * as bootstrap from 'bootstrap';
import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import DataTable from 'datatables.net-vue3';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import { VueReCaptcha } from 'vue-recaptcha-v3';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import TypeAhead from 'vue3-bootstrap-typeahead';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import 'vue3-perfect-scrollbar/style.css';

import App from '@/App.vue';
import appSetting from '@/app-setting';
import Checkbox from '@/components/form/Checkbox.vue';
import fileInputImage from '@/components/form/FileInputImage.vue';
import input from '@/components/form/Input.vue';
import CustomMultiSelect from '@/components/form/MultiSelect.vue';
import CustomSelect2 from '@/components/form/CustomSelect2.vue';
import Can from '@/components/general/Can.vue';
import DatatableTemplate from '@/components/general/DatatableTemplate.vue';
import breadcrumb from '@/components/layout/breadcrumb.vue';
import panel from '@/components/layout/panel.vue';
import tooltipDirective from '@/directives/tooltip';
import i18n from '@/i18n';
import errorHandler from '@/plugins/errorHandler';
import VueWizardSteps from '@/plugins/wizard';
import router from '@/router';

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

const COP_CURRENCY_FORMATTER = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
});

const GLOBAL_DIRECTIVES = {
    tooltip: tooltipDirective
};

const GLOBAL_COMPONENTS = {
    DataTable,
    DatatableTemplate,
    breadcrumb,
    panel,
    inputField: input,
    inputFileImage: fileInputImage,
    CustomMultiSelect,
    checkbox: Checkbox,
    CustomSelect2,
    TypeAhead,
    Can
};

function modalHidden() {
    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('hide.bs.modal', () => {
            document.activeElement?.blur();
        });
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
    return COP_CURRENCY_FORMATTER.format(amount);
}

function registerPlugins(app, pinia, head, appConfig) {
    app.use(head);
    app.use(i18n);
    app.use(pinia);
    app.use(router);
    app.use(PerfectScrollbarPlugin);
    app.use(VueSweetalert2, SWEET_ALERT_OPTIONS);
    app.use(errorHandler);
    app.use(VueWizardSteps);

    if (appConfig.recaptchaSiteKey) {
        app.use(VueReCaptcha, {
            siteKey: import.meta.env.VITE_RECAPTCHAV3_SITEKEY,
            loaderOptions: {
                autoHideBadge: true
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

DataTable.use(DataTablesCore);

const app = createApp(App);
const pinia = createPinia();
const head = createHead();
const appConfig = window.__APP_CONFIG__ ?? {};

pinia.use(piniaPluginPersistedstate);

registerPlugins(app, pinia, head, appConfig);
registerDirectives(app);
registerComponents(app);
registerGlobals(app, appConfig);

app.mount('#app');
