import '@/bootstrap';
import { createApp } from 'vue';
import { createHead } from "@vueuse/head";
import App from "@/App.vue";
import store from "@/store";
import i18n from "@/i18n";
import router from "@/router";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
// datatable
import 'datatables.net-responsive-bs5';

DataTable.use(DataTablesCore);
// import '@/InputMaskExtend';

// bootstrap
import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

// modals
import "./assets/sass/components/custom-modal.scss";

// perfect scrollbar
import PerfectScrollbar from "vue3-perfect-scrollbar";
import "vue3-perfect-scrollbar/dist/vue3-perfect-scrollbar.css";

// set default settings
import appSetting from "@/app-setting";
window.$appSetting = appSetting;
window.$appSetting.init();



// import DataTable from '@components/general/DataTable.vue'
// import DataTablePayments from '@components/general/DataTablePayments.vue'
// import Pagination from '@components/general/Pagination.vue'
// import Payouts from '@components/payments/tournaments/Payout/Payouts.vue'
// import MonthlyPayments from '@components/payments/payment/MonthlyPayments.vue';
// import Attendances from './components/attendances/Attendances.vue';
const head = createHead();
const app = createApp(App);
app.use(head)
app.use(i18n)
app.use(store)
app.use(router)
app.use(PerfectScrollbar)
app.component('DataTable', DataTable)
// Directives
// app.directive("mask", (el, binding) => Inputmask(binding.value).mask(el))

// Components
// app.component('init', App)
// app.component('data-table', DataTable)
// app.component('data-table-payments', DataTablePayments)
// app.component('pagination', Pagination)
// app.component('tournament-payouts', Payouts)
// app.component('monthly-payments', MonthlyPayments)
// app.component('attendances', Attendances)

app.mount('#app')
