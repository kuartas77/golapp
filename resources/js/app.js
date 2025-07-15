import '@/bootstrap';
import '@/InputMaskExtend';

import { createApp } from 'vue/dist/vue.esm-bundler';
// import App from '@components/App.vue'





// import router from './router';
import DataTable from '@components/general/DataTable.vue'
import DataTablePayments from '@components/general/DataTablePayments.vue'
import Pagination from '@components/general/Pagination.vue'
import Payouts from '@components/payments/tournaments/Payout/Payouts.vue'
import MonthlyPayments from '@components/payments/payment/MonthlyPayments.vue';
import Attendances from './components/attendances/Attendances.vue';

const app = createApp();

// Directives
app.directive("mask", (el, binding) => Inputmask(binding.value).mask(el))

// Components
// app.component('init', App)
app.component('data-table', DataTable)
app.component('data-table-payments', DataTablePayments)
app.component('pagination', Pagination)
app.component('tournament-payouts', Payouts)
app.component('monthly-payments', MonthlyPayments)
app.component('attendances', Attendances)

app.mount('#app')
