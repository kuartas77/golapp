import '@/bootstrap';
import { createApp } from 'vue';
import { createHead } from "@vueuse/head";
import App from "@/App.vue";
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
// custom errorHandler
import errorHandler from "@/plugins/errorHandler";
import i18n from "@/i18n";
import router from "@/router";
// datatable
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
// perfect scrollbar
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
//Sweetalert
import VueSweetalert2 from 'vue-sweetalert2';
// set default settings
import appSetting from "@/app-setting";
import '@/utils/yup-locale'
// Directives
import vHasRol from '@/directives/check-rol'
// custom components
import breadcrumb from "@/components/layout/breadcrumb.vue";
import panel from '@/components/layout/panel.vue';
import input from '@/components/form/Input.vue';
import fileInputImage from '@/components/form/FileInputImage.vue';
import Checkbox from '@/components/form/Checkbox.vue';
import DatatableTemplate from "@/components/general/DatatableTemplate.vue";
import CustomMultiSelect from "@/components/form/MultiSelect.vue";
import CustomSelect2 from "@/components/form/CustomSelect2.vue";

// bootstrap
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;
// modals
import "@/assets/sass/components/custom-modal.scss";
import 'datatables.net-responsive-bs5';
import 'vue3-perfect-scrollbar/style.css';
import 'sweetalert2/dist/sweetalert2.min.css';
import '@/assets/sass/font-icons/fontawesome/css/fontawesome.css';
import '@/assets/sass/font-icons/fontawesome/css/regular.css';

// options VueSweetalert2
const options = {
    confirmButtonColor: '#4361ee',
    cancelButtonColor: '#ff7674',
    cancelButtonText: 'Cancelar',
};

DataTable.use(DataTablesCore);

const app = createApp(App);
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
const head = createHead();

app.use(head)
app.use(i18n)
app.use(pinia)
app.use(router)
app.use(PerfectScrollbarPlugin)
app.use(VueSweetalert2, options)
app.use(errorHandler)

app.component('DataTable', DataTable)
app.component('DatatableTemplate', DatatableTemplate)
app.component('breadcrumb', breadcrumb)
app.component('panel', panel)
app.component('inputField', input)
app.component('inputFileImage', fileInputImage)
app.component('CustomMultiSelect', CustomMultiSelect)
app.component('checkbox', Checkbox)
app.component('CustomSelect2', CustomSelect2)

app.directive('has-role', vHasRol)

app.mount('#app')

const modalHidden = () => {
    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('hide.bs.modal', () => {
            document.activeElement.blur();
        });
    });
}
const showMessage = (msg = "", type = "success") => {
    const toast = window.Swal.mixin({ toast: true, position: "top", showConfirmButton: false, timer: 5000 });
    toast.fire({ icon: type, title: msg, padding: "10px 20px" });
}
const moneyFormat = (amount) => {
    const locale = 'es-CO'; // Colombian Spanish locale
    const options = {
        style: 'currency',
        currency: 'COP', // Colombian Peso currency code
        minimumFractionDigits: 0, // Ensure two decimal places for cents
        maximumFractionDigits: 0, // Ensure two decimal places for cents
    };
    const formatter = new Intl.NumberFormat(locale, options).format(amount);
    return formatter;
}

window.$appSetting = appSetting
window.$appSetting.init()
window.Swal = app.config.globalProperties.$swal
app.config.globalProperties.modalHidden = modalHidden
app.config.globalProperties.showMessage = showMessage
app.config.globalProperties.moneyFormat = moneyFormat
