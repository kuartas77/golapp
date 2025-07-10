import '@/bootstrap';
import '@/InputMaskExtend';

import { createApp } from 'vue/dist/vue.esm-bundler';
// import App from '@components/App.vue'





// import router from './router';
import DataTable from '@components/mix/DataTable.vue'
import DataTablePayments from '@components/mix/DataTablePayments.vue'
import Pagination from '@components/mix/Pagination.vue'
import Payouts from '@components/tournaments/Payout/Payouts.vue'

const app = createApp();

// Directives
app.directive("mask", (el, binding) => Inputmask(binding.value).mask(el))

// Components
// app.component('init', App)
app.component('data-table', DataTable)
app.component('data-table-payments', DataTablePayments)
app.component('pagination', Pagination)
app.component('tournament-payouts', Payouts)

app.mount('#app')

// app.component('data-table', DataTable);
// app.component('pagination', Pagination);
// app.component('tournament-payouts', Payouts);
