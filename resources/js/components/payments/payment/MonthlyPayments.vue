<template>
    <div>

        <Form @search="search"></Form>

        <hr>

        <nav>
            <ul class="nav nav-tabs customtab" id="tab_inscriptions">
                <li class="nav-item">
                    <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                        aria-controls="enabled" aria-expanded="false">Resultado</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" id="total-tab" data-toggle="tab" role="tab" aria-controls="disabled"
                        aria-expanded="false">Total: $ {{ formatNumber(totalPayment) }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" id="cash-tab" data-toggle="tab" role="tab" aria-controls="disabled"
                        aria-expanded="false">Efectivo: $ {{ formatNumber(totalCash) }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" id="consignment-tab" data-toggle="tab" role="tab"
                        aria-controls="disabled" aria-expanded="false">Consignación: $ {{ formatNumber(totalConsignment) }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" id="other-tab" data-toggle="tab" role="tab" aria-controls="disabled"
                        aria-expanded="false">Otros: $ {{ formatNumber(totalOthers) }}</a>
                </li>
                <li class=" nav-item ml-auto">
                    <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                        :class="export_excel == '' ? 'disabled' : ''" :href="export_excel" id="export-excel"
                        target="_blank">
                        <i class="fa fa-print" aria-hidden="true"></i> Exportar Pagos En Excel
                    </a>
                    <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                        :class="export_pdf == '' ? 'disabled' : ''" :href="export_pdf" id="export-pdf" target="_blank">
                        <i class="fa fa-print" aria-hidden="true"></i> Exportar Pagos En PDF
                    </a>
                </li>
            </ul>
        </nav>
        <data-table-payments :columns="columns" :rows="pays" :foot="perMonth" :select_type="'monthly'" @change="sendPayment" />

        <!-- <pagination :pagination="paginationMeta" @paginate="fetchRows" :offset="offset"/> -->

    </div>
</template>
<script>
import Form from '@components/payments/payment/Form.vue'
import useMonthlyPayments from '@/composables/monthly_payments'
import { onMounted } from 'vue';
export default {
    name: 'monthly-payments',
    components: {
        Form
    },
    setup() {
        const {
            pays,
            export_excel,
            export_pdf,
            getPays,
            sendPay,
        } = useMonthlyPayments()

        return {
            pays,
            export_excel,
            export_pdf,
            getPays,
            sendPay
        }
    },
    data() {
        return {
            columns: [
                { name: 'Nombres', value: (row) => `${row.unique_code} <br> ${row.player.full_names}`, type: 'link' },
                { name: 'Categoria', value: (row) => row.category },
                { name: 'Matricula', type: 'payments-select', value: (row) => this.formatValue(row, 'enrollment') },
                { name: 'Enero', type: 'payments-select', value: (row) => this.formatValue(row, 'january') },
                { name: 'Febrero', type: 'payments-select', value: (row) => this.formatValue(row, 'february') },
                { name: 'Marzo', type: 'payments-select', value: (row) => this.formatValue(row, 'march') },
                { name: 'Abril', type: 'payments-select', value: (row) => this.formatValue(row, 'april') },
                { name: 'Mayo', type: 'payments-select', value: (row) => this.formatValue(row, 'may') },
                { name: 'Junio', type: 'payments-select', value: (row) => this.formatValue(row, 'june') },
                { name: 'Julio', type: 'payments-select', value: (row) => this.formatValue(row, 'july') },
                { name: 'Agosto', type: 'payments-select', value: (row) => this.formatValue(row, 'august') },
                { name: 'Septiembre', type: 'payments-select', value: (row) => this.formatValue(row, 'september') },
                { name: 'Octubre', type: 'payments-select', value: (row) => this.formatValue(row, 'october') },
                { name: 'Noviembre', type: 'payments-select', value: (row) => this.formatValue(row, 'november') },
                { name: 'Diciembre', type: 'payments-select', value: (row) => this.formatValue(row, 'december') },
            ],
            totalPayment: 0,
            totalCash: 0,
            totalConsignment: 0,
            totalOthers: 0,
            perMonth: {
                enrollment: 0,
                january: 0,
                february: 0,
                march: 0,
                april: 0,
                may: 0,
                june: 0,
                july: 0,
                august: 0,
                september: 0,
                october: 0,
                november: 0,
                december: 0,
            },
            payloadSearch: null
        }
    },
    methods: {
        search(payload) {
            this.resetTotals()
            this.payloadSearch = payload
            this.getPays(payload)
        },
        async sendPayment(payload) {
            await this.sendPay(payload)
            await this.getPays(this.payloadSearch)
        },
        formatValue(row, column) {
            let value = row[`${column}_amount`]
            let object = {
                id: row.id,
                value: value,
                status: row[`${column}`],
                column: column,
                player: {
                    id: row.unique_code
                }
            }
            return object
        },
        resetTotals() {
            this.perMonth.enrollment = 0
            this.perMonth.january = 0
            this.perMonth.february = 0
            this.perMonth.march = 0
            this.perMonth.april = 0
            this.perMonth.may = 0
            this.perMonth.june = 0
            this.perMonth.july = 0
            this.perMonth.august = 0
            this.perMonth.september = 0
            this.perMonth.october = 0
            this.perMonth.november = 0
            this.perMonth.december = 0
        },
        formatNumber(value) {
            return new Intl.NumberFormat('es-CO').format(Number(value))
        }
    },
    watch: {
        pays() {
            if (this.pays.length > 0) {
                this.perMonth.enrollment = this.pays.reduce((accumulator, pay) => accumulator + pay.enrollment_amount, 0)
                this.perMonth.january = this.pays.reduce((accumulator, pay) => accumulator + pay.january_amount, 0)
                this.perMonth.february = this.pays.reduce((accumulator, pay) => accumulator + pay.february_amount, 0)
                this.perMonth.march = this.pays.reduce((accumulator, pay) => accumulator + pay.march_amount, 0)
                this.perMonth.april = this.pays.reduce((accumulator, pay) => accumulator + pay.april_amount, 0)
                this.perMonth.may = this.pays.reduce((accumulator, pay) => accumulator + pay.may_amount, 0)
                this.perMonth.june = this.pays.reduce((accumulator, pay) => accumulator + pay.june_amount, 0)
                this.perMonth.july = this.pays.reduce((accumulator, pay) => accumulator + pay.july_amount, 0)
                this.perMonth.august = this.pays.reduce((accumulator, pay) => accumulator + pay.august_amount, 0)
                this.perMonth.september = this.pays.reduce((accumulator, pay) => accumulator + pay.september_amount, 0)
                this.perMonth.october = this.pays.reduce((accumulator, pay) => accumulator + pay.october_amount, 0)
                this.perMonth.november = this.pays.reduce((accumulator, pay) => accumulator + pay.november_amount, 0)
                this.perMonth.december = this.pays.reduce((accumulator, pay) => accumulator + pay.december_amount, 0)

                this.totalCash = this.pays.filter(pay => ['9','12'].includes(pay.enrollment)).reduce((accumulator, pay) => accumulator + pay.enrollment_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.january)).reduce((accumulator, pay) => accumulator + pay.january_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.february)).reduce((accumulator, pay) => accumulator + pay.february_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.march)).reduce((accumulator, pay) => accumulator + pay.march_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.april)).reduce((accumulator, pay) => accumulator + pay.april_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.may)).reduce((accumulator, pay) => accumulator + pay.may_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.june)).reduce((accumulator, pay) => accumulator + pay.june_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.july)).reduce((accumulator, pay) => accumulator + pay.july_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.august)).reduce((accumulator, pay) => accumulator + pay.august_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.september)).reduce((accumulator, pay) => accumulator + pay.september_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.october)).reduce((accumulator, pay) => accumulator + pay.october_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.november)).reduce((accumulator, pay) => accumulator + pay.november_amount, 0)
                this.totalCash += this.pays.filter(pay => ['9','12'].includes(pay.december)).reduce((accumulator, pay) => accumulator + pay.december_amount, 0)

                this.totalConsignment = this.pays.filter(pay => ['10', '11'].includes(pay.enrollment)).reduce((accumulator, pay) => accumulator + pay.enrollment_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.january)).reduce((accumulator, pay) => accumulator + pay.january_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.february)).reduce((accumulator, pay) => accumulator + pay.february_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.march)).reduce((accumulator, pay) => accumulator + pay.march_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.april)).reduce((accumulator, pay) => accumulator + pay.april_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.may)).reduce((accumulator, pay) => accumulator + pay.may_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.june)).reduce((accumulator, pay) => accumulator + pay.june_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.july)).reduce((accumulator, pay) => accumulator + pay.july_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.august)).reduce((accumulator, pay) => accumulator + pay.august_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.september)).reduce((accumulator, pay) => accumulator + pay.september_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.october)).reduce((accumulator, pay) => accumulator + pay.october_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.november)).reduce((accumulator, pay) => accumulator + pay.november_amount, 0)
                this.totalConsignment += this.pays.filter(pay => ['10', '11'].includes(pay.december)).reduce((accumulator, pay) => accumulator + pay.december_amount, 0)

                this.totalOthers = this.pays.filter(pay => !['9','12','10', '11'].includes(pay.enrollment)).reduce((accumulator, pay) => accumulator + pay.enrollment_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.january)).reduce((accumulator, pay) => accumulator + pay.january_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.february)).reduce((accumulator, pay) => accumulator + pay.february_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.march)).reduce((accumulator, pay) => accumulator + pay.march_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.april)).reduce((accumulator, pay) => accumulator + pay.april_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.may)).reduce((accumulator, pay) => accumulator + pay.may_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.june)).reduce((accumulator, pay) => accumulator + pay.june_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.july)).reduce((accumulator, pay) => accumulator + pay.july_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.august)).reduce((accumulator, pay) => accumulator + pay.august_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.september)).reduce((accumulator, pay) => accumulator + pay.september_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.october)).reduce((accumulator, pay) => accumulator + pay.october_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.november)).reduce((accumulator, pay) => accumulator + pay.november_amount, 0)
                this.totalOthers += this.pays.filter(pay => !['9','12','10', '11'].includes(pay.december)).reduce((accumulator, pay) => accumulator + pay.december_amount, 0)
                // console.log(cash)
                //     // }
                // ||pay.january
                    // ||pay.february
                    // ||pay.march
                    // ||pay.april
                    // ||pay.may
                    // ||pay.june
                    // ||pay.july
                    // ||pay.august
                    // ||pay.september
                    // ||pay.october
                    // ||pay.november
                    // ||pay.december){
                        // return
                    // }

                // })


                // this.totalCash = this.pays.reduce((accumulator, pay)
                // totalConsignment
                // totalOthers
                // '2','10', '13'



                this.totalPayment = Object.values(this.perMonth).reduce((accumulator, currentValue) => {
                    console.log(currentValue)
                    if (typeof currentValue === 'number') {
                        return accumulator + currentValue;
                    }
                    return accumulator; // If not a number, return the accumulator unchanged
                }, 0);
            }

        }
    }
};
</script>