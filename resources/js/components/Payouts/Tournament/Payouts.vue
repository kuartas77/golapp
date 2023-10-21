<template>
    <div>
        <Form @search="searchGroup" @create="createPayments"/>

        <data-table :columns="columns" :rows="rows"/>

        <!-- <pagination :pagination="paginationMeta" @paginate="fetchRows" :offset="offset"/> -->

    </div>
</template>

<script>
import Form from './Form.vue'
import usePayouts from '@/composables/tournament_payouts'
export default {
    name: 'tournament-payouts',
    components:{
        Form
    },
    setup(){
        const {fetchRowsPaginate} = usePayouts()
        return {
            fetchRowsPaginate
        }
    },
    data() {
        return {
            offset: 0,
            rows: [],
            columns:[
                {name: 'Nombres', attribute: 'names'},
                {name: 'Torneo', attribute: 'tournament'},
                {name: 'Grupo', attribute: 'group'},
                {name: 'Código Único', attribute: 'unique_code'},
                {name: 'Estado', attribute: 'status', type: 'select'},
            ]
        }
    },
    methods: {
        searchGroup(payload){
            console.log("search", payload)
            this.fetchRowsPaginate()
        },
        createPayments(payload){
            console.log("createTournamentPay", payload)
        },
        paginationMeta(){

        },
        fetchRows(){}

    },
    mounted(){

    }
};
</script>
