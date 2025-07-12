<template>

    <CurrencyInput
        v-model.lazy="payment.value"
        :options="{ currency: 'COP', valueRange: { min:0 }, hideCurrencySymbolOnFocus: false }"
        class="form-control form-control-sm"
        style="width: 20%; text-align: right;"
        @blur="handleBlur"
     />
    /
    <select
        v-model="payment.selected"
        class="form-control form-control-sm"
        :class="colorClass"
        style="width: 20%;"
        @change="handleBlur"
    >
        <option value="">Selecciona...</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.text }}
        </option>
    </select>
</template>

<script>
import CurrencyInput from '@components/general/CurrencyInput'
export default {
    name: "payment-select",
    emits: ['change'],
    components: {
        CurrencyInput
    },
    props: {
        row: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            payment: {
                id: null,
                value: 0,
                selected: "",
                column: null,
                user_id: null
            },
            colorClass: "",
            options: [
                { value: 0, text: "Pendiente", color: '' },
                { value: 1, text: "Pagó", color: 'color-success' },
                { value: 9, text: "Pagó - Efectivo", color: 'color-warning' },
                { value: 10, text: "Pagó - Consignación", color: 'color-info' },
                { value: 11, text: "Pago Anualidad Consignación", color: 'color-purple' },
                { value: 12, text: "Pago Anualidad Efectivo", color: 'color-brown' },
                { value: 13, text: "Acuerdo de Pago", color: '' },
                { value: 14, text: "No Aplica", color: '' },
                { value: 2, text: "Debe", color: 'color-error' },
                { value: 3, text: "Abonó", color: 'color-agua' },
                { value: 4, text: "Incapacidad", color: 'color-incapacidad' },
                { value: 5, text: "Retiro Temporal", color: 'color-orange' },
                { value: 6, text: "Retiro Definitivo", color: 'color-grey' },
                { value: 7, text: "Otro", color: '' },
                { value: 8, text: "Becado", color: 'color-becado' },
            ]
        };
    },
    methods: {
        loadValues() {
            this.payment.id = this.row.id;
            this.payment.value = this.row.value;
            this.payment.selected = this.row.status;
            this.payment.user_id = this.row.player.id;
            this.payment.column = this.row.column ?? null;
            this.changeColors(this.payment.selected)
        },
        handleBlur() {
            this.changeColors(this.payment.selected)
            if (this.payment.selected != '0' && this.payment.value != 0) {
                this.$emit('change', this.payment)
            }
        },
        changeColors(valueSelected) {
            const optionSelected = this.options.find((option) => option.value == valueSelected)
            this.colorClass = optionSelected.color
        }
    },
    mounted() {
        this.loadValues()
    },
};
</script>
