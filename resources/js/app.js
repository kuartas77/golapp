require('./bootstrap');

require('alpinejs');

require('./InputMaskExtend');

import { createApp } from 'vue'

// import router from './router';
import DataTable from '@/components/mix/DataTable'
import Pagination from '@/components/mix/Pagination'
import Payouts from '@/components/tournaments/Payout/Payouts'

const app = createApp({})

// Directives
app.directive("mask", (el, binding) => Inputmask(binding.value).mask(el))

// Components
app.component('data-table', DataTable)
app.component('pagination', Pagination)
app.component('tournament-payouts', Payouts)

app.mount('#app')

// app.component('data-table', DataTable);
// app.component('pagination', Pagination);
// app.component('tournament-payouts', Payouts);
