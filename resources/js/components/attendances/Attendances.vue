<template>
    <div>

        <Form :groups="groups" :months="months"></Form>

        <hr>

        <data-table :columns="columns" :rows="[]"/>

        <!-- <pagination :pagination="paginationMeta" @paginate="fetchRows" :offset="offset"/> -->

    </div>
</template>
<script>
import Form from '@components/attendances/Form'
import { onMounted, computed } from 'vue'
export default {
    name: "form-attendances",
    props: ['groups', 'months'],
    components:{
        Form
    },
    data() {
        return {
            offset: 0,
            columns:[
                {name: 'Deportista', value: (row) => `${row.unique_code} ${row.player.full_names}`},
                {name: 'Clases',value:(row) => ''},
            ]
        }
    },
}

</script>