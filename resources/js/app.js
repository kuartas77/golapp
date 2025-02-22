import '@/bootstrap';
import '@/InputMaskExtend';
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
import { createApp } from 'vue';

// import router from './router';
import DataTable from '@components/mix/DataTable'
import Pagination from '@components/mix/Pagination'
import Payouts from '@components/tournaments/Payout/Payouts'
import Attendances from '@components/attendances/Attendances.vue'

const app = createApp();


// Directives
app.directive("mask", (el, binding) => Inputmask(binding.value).mask(el))

// Components
app.component('v-select', vSelect)
app.component('data-table', DataTable)
app.component('pagination', Pagination)
app.component('tournament-payouts', Payouts)
app.component('attendances', Attendances)

app.mount('#app')

// app.component('data-table', DataTable);
// app.component('pagination', Pagination);
// app.component('tournament-payouts', Payouts);
