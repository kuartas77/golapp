import '@/bootstrap';
import { createApp } from 'vue';
import { createHead } from "@vueuse/head";
import App from "@/App.vue";
import { createPinia } from 'pinia'
import i18n from "@/i18n";
import router from "@/router";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
// datatable
import 'datatables.net-responsive-bs5';
DataTable.use(DataTablesCore);

import breadcrumb from "@/components/layout/breadcrumb.vue";
import panel from '@/components/layout/panel.vue';
import input from '@/components/form/Input.vue';
import inputFile from '@/components/form/InputFile.vue';
import Checkbox from '@/components/form/Checkbox.vue';


// bootstrap
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

// modals
import "./assets/sass/components/custom-modal.scss";

// perfect scrollbar
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import 'vue3-perfect-scrollbar/style.css';

//Sweetalert
// import Swal from "sweetalert2";
// window.Swal = Swal;
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
const options = {
    confirmButtonColor: '#4361ee',
    cancelButtonColor: '#ff7674',
    cancelButtonText: 'Cancelar',
};

// set default settings
import appSetting from "@/app-setting";

import '@/utils/yup-locale'

const pinia = createPinia()
const head = createHead();
const app = createApp(App);
app.use(head)
app.use(i18n)
// app.use(store)
app.use(pinia)
app.use(router)
app.use(PerfectScrollbarPlugin)
app.use(VueSweetalert2, options)

app.component('DataTable', DataTable)
app.component('breadcrumb', breadcrumb)
app.component('panel', panel)
app.component('inputField', input)
app.component('inputFile', inputFile)
app.component('checkbox', Checkbox)

app.mount('#app')
window.$appSetting = appSetting;
window.$appSetting.init();
window.Swal =  app.config.globalProperties.$swal;