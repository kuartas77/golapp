import cloneDeep from "lodash.clonedeep"

import { getCurrentInstance, ref, toRaw, onMounted, watch } from "vue";
import useSettings from "@/composables/settingsComposable";
import { usePageTitle } from "@/composables/use-meta";
import * as yup from 'yup';
import api from "@/utils/axios";


export default function useMonthlyPayments() {
    const currentDate = new Date()
    const { settings } = useSettings()
    const groups = settings.groups
    const categories = settings.categories
    const type_payments = settings.type_payments
    const selected_group = ref(null)
    const groupPayments = ref([])
    const globalError = ref(null)
    const export_excel = ref(null)
    const export_pdf = ref(null)
    const modelGroup = ref(null)
    const modelCategory = ref(null)
    const { proxy } = getCurrentInstance()
    const schema = yup.object().shape({})
    const formData = ref({
        training_group: null,
        category: null
    })
    const loading = ref(false)
    const editingCell = ref(null)
    const backupCell = ref(null)
    const typesNoEditables = ['6', '14']
    const annuity_amount = ref(0)
    const enrollment_amount = ref(0)
    const monthly_amount = ref(0)
    const player_count = ref(0)
    const paymentFields = {
        0: 'enrollment',
        1: 'january',
        2: 'february',
        3: 'march',
        4: 'april',
        5: 'may',
        6: 'june',
        7: 'july',
        8: 'august',
        9: 'september',
        10: 'october',
        11: 'november',
        12: 'december',
    }

    const moneyFormat = (amount) => {
        const locale = 'es-CO'; // Colombian Spanish locale
        const options = {
            style: 'currency',
            currency: 'COP', // Colombian Peso currency code
            minimumFractionDigits: 0, // Ensure two decimal places for cents
            maximumFractionDigits: 0, // Ensure two decimal places for cents
        };
        const formatter = new Intl.NumberFormat(locale, options).format(amount);
        return formatter;
    }

    const showMessage = (msg = "", type = "success") => {
        const toast = window.Swal.mixin({ toast: true, position: "top", showConfirmButton: false, timer: 3000 });
        toast.fire({ icon: type, title: msg, padding: "10px 20px" });
    };

    const handleSearch = async (values, actions) => {
        try {
            loading.value = true
            const params = {
                // unique_code
                category: values.category?.category,
                year: currentDate.getFullYear(),
                training_group_id: values.training_group?.id,
                dataRaw: true
            }
            const response = await api.get(`/api/v2/payments/`, { params: params })
            if (response?.data) {
                const data = response.data
                if (data.rows.length) {
                    groupPayments.value = data.rows
                    export_excel.value = data.url_export_excel
                    export_pdf.value = data.url_export_pdf
                    annuity_amount.value = data.annuity
                    enrollment_amount.value = data.inscription_amount
                    monthly_amount.value = data.monthly_payment
                    player_count.value = data.count
                } else {
                    groupPayments.value = []
                    export_excel.value = null
                    export_pdf.value = null
                }

                selected_group.value = (values?.training_group?.id) ? groups.find((group) => group.id === values.training_group.id) : null
                loading.value = false
            } else {
                groupPayments.value = []
                export_excel.value = null
                export_pdf.value = null
                loading.value = false
            }
        } catch (error) {
            groupPayments.value = []
            export_excel.value = null
            export_pdf.value = null
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
            loading.value = false
        }
    }


    const editRow = (payPlayer, field) => {
        editingCell.value = { payPlayer, field }
        backupCell.value = cloneDeep(toRaw(payPlayer));
    };

    const cancelEdition = (field) => {
        let changed = groupPayments.value.find((payPlayer) => payPlayer.id === backupCell.value.id)
        if (backupCell.value && changed) {
            const clon = cloneDeep(toRaw(backupCell.value))
            changed[field] = clon[field];
            changed[`${field}_amount`] = clon[`${field}_amount`]
        }
        editingCell.value = null;
        backupCell.value = null;
    }

    const handleSelectChange = (payPlayer, field) => {
        const type = payPlayer[field]
        const inputAmount = payPlayer[`${field}_amount`]
        if (inputAmount == 0 && ['1', '9', '10'].includes(type)) {
            if (field === 'enrollment') {
                payPlayer[`${field}_amount`] = enrollment_amount.value
            } else {
                payPlayer[`${field}_amount`] = monthly_amount.value
            }
        } else if (inputAmount !== annuity_amount.value && ['11', '12'].includes(type)) {
            payPlayer[`${field}_amount`] = annuity_amount.value
        } else if (inputAmount === annuity_amount.value && ['11', '12'].includes(type)) {
            payPlayer[`${field}_amount`] = annuity_amount.value
        } else if (inputAmount !== 0 && ['0'].includes(type)) {
            payPlayer[`${field}_amount`] = 0
        } else if (['13'].includes(type)) {
            payPlayer[`${field}_amount`] = inputAmount
        } else if (['8'].includes(type)) {
            payPlayer[`${field}_amount`] = 0
        }
    }

    const saveField = async () => {
        const { payPlayer, field } = editingCell.value
        const paymentId = payPlayer.id
        const type = payPlayer[field]
        const inputAmount = payPlayer[`${field}_amount`]
        let amount = 0
        let allFields = false
        let changed = groupPayments.value.find((payPlayer) => payPlayer.id === paymentId)

        if (inputAmount == 0 && ['1', '9', '10'].includes(type)) {
            if (field === 'enrollment') {
                changed[`${field}_amount`] = enrollment_amount.value
            } else {
                changed[`${field}_amount`] = monthly_amount.value
            }
        } else if (inputAmount !== annuity_amount.value && ['11', '12'].includes(type)) {
            changed[`${field}_amount`] = annuity_amount.value
            amount = annuity_amount.value
            allFields = true
        } else if (inputAmount === annuity_amount.value && ['11', '12'].includes(type)) {
            changed[`${field}_amount`] = annuity_amount.value
            amount = annuity_amount.value
            allFields = true
        } else if (inputAmount !== 0 && ['0'].includes(type)) {
            changed[`${field}_amount`] = 0
        } else if (['13'].includes(type)) {
            changed[`${field}_amount`] = annuity_amount.value
        } else if (['6'].includes(type)) {
            allFields = true
        } else if (['8'].includes(type)) {
            changed[`${field}_amount`] = 0
            amount = 0
            allFields = true
        }

        if (allFields && changed) {
            for (const [key, value] of Object.entries(paymentFields)) {
                changed[value] = type
                if (changed[`${value}_amount`] === 0) {
                    changed[`${value}_amount`] = amount
                }
            }
        }

        const data = cloneDeep(toRaw(changed))
        data._method = 'PUT'
        delete data.player
        loading.value = true
        const response = await api.post(`/api/v2/payments/${data.id}`, data)
        if (response.data.data) {
            showMessage("Se guardó correctamente")
            editingCell.value = null
            loading.value = false
        } else {
            loading.value = false
            showMessage("Algo salió mal", 'error')
        }
    }

    onMounted(() => {
        usePageTitle('Mensualidades')
    })

    const totalsFooter = ref({
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
        december: 0
    })

    const totalByType = ref({
        cash: 0,
        consignment: 0,
        others: 0,
        debts: 0
    })

    watch(groupPayments, async (newValue) => {
        totalByType.value.cash = 0
        totalByType.value.consignment = 0
        totalByType.value.others = 0
        for (const field in  paymentFields) {
            totalsFooter.value[`${paymentFields[field]}`] = newValue.reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.cash += newValue.filter(pay => ['9','12'].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.consignment += newValue.filter(pay => ['10', '11'].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.others += newValue.filter(pay => !['2','9','12','10', '11'].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.debts += newValue.filter(pay => ['2'].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
        }
    }, { deep: true })

    return {
        moneyFormat,
        handleSearch,
        editRow,
        cancelEdition,
        handleSelectChange,
        saveField,
        loading,
        player_count,
        selected_group,
        export_excel,
        export_pdf,
        modelGroup,
        modelCategory,
        groupPayments,
        schema,
        formData,
        editingCell,
        groups,
        categories,
        type_payments,
        typesNoEditables,
        paymentFields,
        totalsFooter,
        totalByType,
    }
}
