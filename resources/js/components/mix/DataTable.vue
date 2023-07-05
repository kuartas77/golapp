<template>
<div>
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column" :id="column.name">{{ column.name }}</th>
            </tr>
        </thead>
        <tbody>
           
            <tr v-for="data in rows" :key="data.id">
                <td v-for="column in columns" :key="`cell-${data.id}-${column.attribute}`">
                    
                    <slot v-if="column.type && column.type == 'select-payments'" name="cell" v-bind="{ column, data }">
                        {{ data[column.attribute] }} select
                    </slot>
                    <slot v-else name="cell" v-bind="{ column, data }">
                        {{ data[column.attribute] }}
                    </slot>
                </td>
            </tr>
            <tr v-if="rows.length == 0">
                <td :colspan="columns.length" class="text-center">Ningún dato disponible en esta tabla</td>
            </tr>

        </tbody>
    </table>
    
</div>
</template>
<script>
export default {
    name: 'DataTable',
    props: {
        columns: {
            type: Array,
            required: true
        },
        rows: {
            type: Object,
            required: true
        }
    },
    methods: {

    }
}
</script>