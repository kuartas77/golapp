<template>
    <div>

        <Form @search="searchGroup" @create="createPayments"></Form>

        <hr>

        <data-table-payments :columns="columns" :rows="pays" @change="onChangePayment"/>

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
        const {pays, getPays} = usePayouts()

        return {
            pays,
            getPays,
        }
    },
    data() {
        return {
            offset: 0,
            columns:[
                {name: 'Nombres', value: (row) => `${row.unique_code} ${row.player.full_names}`},
                {name: 'Pago / Estado', type: 'payments-select'},
            ]
        }
    },
    methods: {
        searchGroup(payload){
            this.pays = []
            this.getPays(payload)
        },
        createPayments(payload){
            console.log("createTournamentPay", payload)
        },
        paginationMeta(){
            return {}
        },
        fetchRows(){},
        onChangePayment(payment) {
            console.log(payment)
        }

    },
    mounted(){

    }
};
</script>
