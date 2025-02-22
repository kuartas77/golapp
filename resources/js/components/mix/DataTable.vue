<template>
<div>
    <table class="display compact dataTable">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column+'_head'" :id="column.name">{{ column.name }}</th>
            </tr>
        </thead>
        <tbody>

            <tr v-for="row in rows" :key="row.id">
                <td v-for="column in columns" :key="`cell-${row.id}`">

                    <slot v-if="getType(column, row) == 'payments-select'" name="payment" v-bind="{ column, row }">
                        <paymentSelect :row="row" @change=""/>
                    </slot>
                    <slot v-else name="cell" v-bind="{ column, row }">
                        {{column.value(row)}}
                    </slot>
                </td>
            </tr>

            <tr v-if="rows.length == 0">
                <td :colspan="columns.length" class="text-center">Ningún dato disponible en esta tabla</td>
            </tr>

        </tbody>
        <tfoot>
            <tr>
                <th v-for="column in columns" :key="column+'_foot'" :id="column.name"></th>
            </tr>
        </tfoot>
    </table>

</div>
</template>
<script>
import paymentSelect from '@/components/mix/PaymentSelect'
export default {
    name: 'data-table',
    components: {
        paymentSelect
    },
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
        getType(column, row) {
            return typeof column.type == 'function' ? column.type(row) : column.type
        },
        getClass(column, row) {
            return column?.class !== undefined ? (typeof column.class == 'function' ? column.class(row) : column.class) : ''
        },
        getName(dot, row){
            return typeof dot.name == 'function' ? dot.name(row) : dot.name
        }
    }
}
</script>