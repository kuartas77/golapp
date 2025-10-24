import cloneDeep from "lodash.clonedeep";

import { useSetting } from '@/store/settings-store';
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { getCurrentInstance, onMounted, ref, toRaw, watch } from "vue";
import * as yup from 'yup';


export default function useMonthlyPayments() {
    const currentDate = new Date()
    const settings = useSetting()
    const groups = settings.groups.filter((group) => group.name !== 'Provisional').map((group) => ({ value: group.id, label: group.full_group }));
    const categories = settings.categories.map((i) => ({ value: i.category, label: i.category }));
    const type_payments = settings.type_payments
    const selected_group = ref(null)
    const groupPayments = ref([])
    const globalError = ref(null)
    const export_excel = ref(null)
    const export_pdf = ref(null)
    const modelGroup = ref(null)
    const modelCategory = ref(null)
    const { proxy } = getCurrentInstance()
    const schema = yup.object().shape({
        category: yup.string().nullable().optional(),
        training_group_id: yup.string().when('category', {
            is: (categoryValue) => !categoryValue || categoryValue === null , // Check if category is empty
            then: (schema) => schema.required(), // If empty, training_group_id is required
            otherwise: (schema) => schema.notRequired(), // Otherwise, training_group_id is not required
        }),
    })
    const formData = ref({
        training_group_id: null,
        category: null
    })
    const isLoading = ref(false)
    const editingCell = ref(null)
    const backupCell = ref(null)
    const typesNoEditables = [6, 14]
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

    const handleSearch = async (values, actions) => {
        try {
            groupPayments.value = []
            isLoading.value = true
            const params = {
                category: values.category,
                year: currentDate.getFullYear(),
                training_group_id: values.training_group_id,
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
                isLoading.value = false
            } else {
                groupPayments.value = []
                export_excel.value = null
                export_pdf.value = null
                isLoading.value = false
            }
        } catch (error) {
            groupPayments.value = []
            export_excel.value = null
            export_pdf.value = null
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
            isLoading.value = false
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
        if (inputAmount == 0 && [1, 9, 10].includes(type)) {
            if (field === 'enrollment') {
                payPlayer[`${field}_amount`] = enrollment_amount.value
            } else {
                payPlayer[`${field}_amount`] = monthly_amount.value
            }
        } else if (inputAmount !== annuity_amount.value && [11, 12].includes(type)) {
            payPlayer[`${field}_amount`] = annuity_amount.value
        } else if (inputAmount === annuity_amount.value && [11, 12].includes(type)) {
            payPlayer[`${field}_amount`] = annuity_amount.value
        } else if (inputAmount !== 0 && [0].includes(type)) {
            payPlayer[`${field}_amount`] = 0
        } else if ([13].includes(type)) {
            payPlayer[`${field}_amount`] = inputAmount
        } else if ([8].includes(type)) {
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

        if (inputAmount == 0 && [1, 9, 10].includes(type)) {
            if (field === 'enrollment') {
                changed[`${field}_amount`] = enrollment_amount.value
            } else {
                changed[`${field}_amount`] = monthly_amount.value
            }
        } else if (inputAmount !== annuity_amount.value && [11, 12].includes(type)) {
            changed[`${field}_amount`] = annuity_amount.value
            amount = annuity_amount.value
            allFields = true
        } else if (inputAmount === annuity_amount.value && [11, 12].includes(type)) {
            changed[`${field}_amount`] = annuity_amount.value
            amount = annuity_amount.value
            allFields = true
        } else if (inputAmount !== 0 && [0].includes(type)) {
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
        isLoading.value = true
        const response = await api.post(`/api/v2/payments/${data.id}`, data)
        if (response.data.data) {
            showMessage("Se guardó correctamente")
            editingCell.value = null
            isLoading.value = false
        } else {
            isLoading.value = false
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
        totalByType.value.debts = 0
        for (const field in paymentFields) {
            totalsFooter.value[`${paymentFields[field]}`] = newValue.reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.cash += newValue.filter(pay => [9, 12].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.consignment += newValue.filter(pay => [10, 11].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.others += newValue.filter(pay => ![2, 9, 10, 11, 12].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
            totalByType.value.debts += newValue.filter(pay => [2].includes(pay[`${paymentFields[field]}`])).reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
        }
    }, { deep: true })

    return {
        handleSearch,
        editRow,
        cancelEdition,
        handleSelectChange,
        saveField,
        isLoading,
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
