<template>
    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-0">
                <div class="card-body">
                    <data-table :columns="columns" :rows="rows" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { onMounted, computed } from '@vue/runtime-core'
// import usePayouts from '@/composables/tournament_payouts'
export default {
    name: 'list-training-groups',
    data() {
        return {
            columns:[
                {name: 'Nombre', value: (row) => `${row.unique_code} ${row.player.full_names}`, type: 'link'},
                {name: 'Año de actividad', value: (row) => '' },
                {name: 'Formador(es)', value: (row) => '' },
            ],
            rows: []
        }
    },
}
</script>