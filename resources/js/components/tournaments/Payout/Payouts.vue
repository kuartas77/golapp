<template>
    <div>

        <Form @search="getPays" @create="createPaymentsControl"></Form>

        <hr>

        <data-table-payments :columns="columns" :rows="pays" @change="sendPay"/>

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
        const {pays, getPays, sendPay, createPayments} = usePayouts()

        return {
            pays,
            getPays,
            sendPay,
            createPayments
        }
    },
    data() {
        return {
            offset: 0,
            columns:[
                {name: 'Nombres', value: (row) => `${row.unique_code} ${row.player.full_names}`, type: 'link'},
                {name: 'Pago / Estado', type: 'payments-select'},
            ]
        }
    },
    methods: {
        createPaymentsControl(payload){
            this.createPayments(payload)
            this.getPays(payload)
            console.log("createTournamentPay", payload)
        },
        paginationMeta(){
            return {}
        }
    }
};
</script>
