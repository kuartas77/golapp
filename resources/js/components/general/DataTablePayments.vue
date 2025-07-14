<template>
<div>
    <div style="position: relative; overflow: auto; width: 100%;">
    <table class="display compact dataTable">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name+'_head'" class="text-center">{{ column.name }}</th>
            </tr>
        </thead>
        <tbody>

            <tr v-for="row in rows" :key="row.id" class="text-center">
                <td v-for="column in columns" :key="`cell-${row.id}`" >

                    <slot v-if="getType(column, row) == 'payments-select'" name="cell" v-bind="{ column, row }">
                        <template v-if="select_type == 'tournament'">
                            <paymentSelect :row="row" @change="change" :select_type="select_type"/>
                        </template>
                        <template v-else>
                            <paymentSelect :row="column.value(row)" @change="change" :select_type="select_type"/>
                        </template>
                    </slot>
                    <slot v-else-if="getType(column, row) == 'link'" name="cell" v-bind="{ column, row }">
                        <a :href="'/players/'+row.player.unique_code" target="_blank">
                            <small v-html="column.value(row)"></small>
                        </a>
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
        <tfoot v-if="foot">
            <tr>
                <th></th>
                <th></th>
                <th v-for="(column, index) in foot" :key="index+'_foot'" class="text-center">${{ formatNumber(column) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
</div>
</template>
<script>
import paymentSelect from '@/components/general/PaymentSelect'
export default {
    name: 'data-table-payments',
    emits: ['change'],
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
        },
        select_type: {
            type: String,
            required: true
        },
        foot: {
            type: Object
        }
    },
    computed: {
        'widthColumn'() {
            if (this.select_type != 'tournament') {
                return 'width: 90px;'
            }
            return ''
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
        },
        change(payment) {
            this.$emit('change', payment)
        },
        formatNumber(value) {
            return new Intl.NumberFormat('es-CO').format(Number(value))
        }
    }
}
</script>