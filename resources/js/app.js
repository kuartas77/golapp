require('./bootstrap');

require('alpinejs');

import { createApp } from 'vue';
// import router from './router';
import DataTable from '@/components/mix/DataTable'
import Pagination from '@/components/mix/Pagination'
import Payouts from '@/components/tournaments/Payout/Payouts'

const app = createApp({})
.component('data-table', DataTable)
.component('pagination', Pagination)
.component('tournament-payouts', Payouts)
.mount('#app')

// app.component('data-table', DataTable);
// app.component('pagination', Pagination);
// app.component('tournament-payouts', Payouts);
