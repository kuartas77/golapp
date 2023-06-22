<template>
    <div>
        <Form @search="searchGroup" @create="createPayments"/>

        <data-table :columns="columns" :rows="rows"/>

        <!-- <pagination :pagination="paginationMeta" @paginate="fetchRows" :offset="offset"/> -->
        
    </div>
</template>

<script>
import Form from './Form';
export default {
    name: 'tournament-payouts',
    components:{
        Form
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
                {name: 'Estado', attribute: 'status', select: true},
            ]
        }
    },
    methods: {
        searchGroup(){
            console.log("search")
        },
        createPayments(){
            console.log("createTournamentPay")
        },
        paginationMeta(){

        },
        fetchRows(){}

    },
    mounted(){
        
    }
};
</script>
