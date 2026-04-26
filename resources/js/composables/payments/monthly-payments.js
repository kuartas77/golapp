import cloneDeep from "lodash.clonedeep";

import { useSetting } from '@/store/settings-store';
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { computed, getCurrentInstance, onMounted, ref, toRaw, watch } from "vue";
import * as yup from 'yup';


export default function useMonthlyPayments() {
    const currentDate = new Date()
    const settings = useSetting()
    const groups = settings.groups.filter((group) => group.name !== 'Provisional').map((group) => ({ value: group.id, label: group.full_group }));
    const categories = settings.categories.map((i) => ({ value: i.category, label: i.category }));
    const years = settings.inscription_years
    const defaultYear = years.find((year) => Number(year.value) === currentDate.getFullYear())?.value
        ?? years[years.length - 1]?.value
        ?? currentDate.getFullYear()
    const type_payments = computed(() => settings.paymentTypeOptions)
    const paymentTypeLabels = computed(() => settings.paymentTypeLabels)
    const selected_group = ref(null)
    const groupPayments = ref([])
    const globalError = ref(null)
    const export_excel = ref(null)
    const export_pdf = ref(null)
    const modelGroup = ref(null)
    const modelCategory = ref(null)
    const { proxy } = getCurrentInstance()
    const schema = yup.object().shape({
        year: yup.mixed().required(),
        category: yup.string().nullable().optional(),
        training_group_id: yup.string().when('category', {
            is: (categoryValue) => !categoryValue || categoryValue === null , // Check if category is empty
            then: (schema) => schema.required(), // If empty, training_group_id is required
            otherwise: (schema) => schema.notRequired(), // Otherwise, training_group_id is not required
        }),
    })
    const formData = ref({
        year: defaultYear,
        training_group_id: null,
        category: null
    })
    const isLoading = ref(false)
    const editingCell = ref(null)
    const backupCell = ref(null)
    const typesNoEditables = [14]
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
    const paymentFieldNames = Object.values(paymentFields)

    const handleSearch = async (values, actions) => {
        try {
            groupPayments.value = []
            isLoading.value = true
            const params = {
                category: values.category,
                year: values.year ?? defaultYear,
                training_group_id: values.training_group_id,
                dataRaw: true
            }
            const response = await api.get(`/api/v2/payments`, { params: params })
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
                    player_count.value = 0
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

    const cancelEdition = () => {
        if (!backupCell.value) {
            editingCell.value = null;
            return
        }

        let changed = groupPayments.value.find((payPlayer) => payPlayer.id === backupCell.value.id)
        if (backupCell.value && changed) {
            const clon = cloneDeep(toRaw(backupCell.value))
            Object.assign(changed, clon)
        }
        editingCell.value = null;
        backupCell.value = null;
    }

    const normalizeAmount = (amount) => Number(amount) || 0

    const applyStatusToFollowingFields = (payPlayer, field, type, valueIfZero = 0, forceAmount = false) => {
        const currentFieldIndex = paymentFieldNames.indexOf(field)
        const startIndex = currentFieldIndex <= 0 ? 0 : currentFieldIndex

        paymentFieldNames.forEach((fieldName, index) => {
            if (index < startIndex) {
                return
            }

            if (field !== 'enrollment' && fieldName === 'enrollment') {
                return
            }

            payPlayer[fieldName] = type

            const amountKey = `${fieldName}_amount`
            if (forceAmount) {
                payPlayer[amountKey] = valueIfZero
                return
            }

            if (normalizeAmount(payPlayer[amountKey]) === 0) {
                payPlayer[amountKey] = valueIfZero
            }
        })
    }

    const getDefaultAmountByField = (field, payPlayer = null) => {
        if (field === 'enrollment') {
            return enrollment_amount.value
        }

        return Number(payPlayer?.default_monthly_amount) || monthly_amount.value
    }

    const paymentRuleHandlers = {
        setDefaultAmount: ({ payPlayer, field, amountKey, inputAmount }) => {
            if (inputAmount === 0) {
                payPlayer[amountKey] = getDefaultAmountByField(field, payPlayer)
            }
        },
        setMonthlyAmount: ({ payPlayer, amountKey, inputAmount }) => {
            if (inputAmount === 0) {
                payPlayer[amountKey] = getDefaultAmountByField('january', payPlayer)
            }
        },
        setAnnuityAmount: ({ payPlayer, amountKey }) => {
            payPlayer[amountKey] = annuity_amount.value
        },
        setFollowingFieldsWithAnnuity: ({ payPlayer, field, type }) => {
            applyStatusToFollowingFields(payPlayer, field, type, annuity_amount.value)
        },
        clearFollowingFields: ({ payPlayer, field, type }) => {
            applyStatusToFollowingFields(payPlayer, field, type, 0, true)
        }
    }

    const paymentRulesByType = {
        1: paymentRuleHandlers.setDefaultAmount,
        2: paymentRuleHandlers.setMonthlyAmount,
        6: paymentRuleHandlers.clearFollowingFields,
        9: paymentRuleHandlers.setDefaultAmount,
        10: paymentRuleHandlers.setDefaultAmount,
        11: paymentRuleHandlers.setFollowingFieldsWithAnnuity,
        12: paymentRuleHandlers.setFollowingFieldsWithAnnuity,
        13: paymentRuleHandlers.setAnnuityAmount
    }

    const syncPaymentField = (payPlayer, field) => {
        const type = Number(payPlayer[field])
        const amountKey = `${field}_amount`

        payPlayer[field] = type
        paymentRulesByType[type]?.({
            payPlayer,
            field,
            type,
            amountKey,
            inputAmount: normalizeAmount(payPlayer[amountKey])
        })
    }

    const handleSelectChange = (payPlayer, field) => {
        syncPaymentField(payPlayer, field)
    }

    const getSaveErrorMessage = (error) => {
        const backendErrors = error.response?.data?.errors
        if (backendErrors) {
            const firstError = Object.values(backendErrors).flat()[0]
            if (firstError) {
                return firstError
            }
        }

        return error.response?.data?.message || "No fue posible guardar la mensualidad"
    }

    const saveField = async () => {
        if (!editingCell.value) {
            return
        }

        const { payPlayer, field } = editingCell.value
        const paymentId = payPlayer.id
        const amountField = `${field}_amount`
        let changed = groupPayments.value.find((payPlayer) => payPlayer.id === paymentId)

        if (!changed) {
            return
        }

        syncPaymentField(changed, field)

        try {
            const data = {
                _method: 'PUT',
                column: field,
                [field]: changed[field],
                [amountField]: changed[amountField],
            }
            isLoading.value = true

            const response = await api.post(`/api/v2/payments/${paymentId}`, data)

            if (response.data.data) {
                Object.assign(changed, response.data.data)
                showMessage("Se guardó correctamente")
                editingCell.value = null
                backupCell.value = null
            } else {
                showMessage("Algo salió mal", 'error')
            }
        } catch (error) {
            globalError.value = getSaveErrorMessage(error)
            showMessage(globalError.value, 'error')
        } finally {
            isLoading.value = false
        }
    }

    const exportFile = async (event) => {
        event.preventDefault()
        const link = event.currentTarget
        // console.log(link.href)
        Swal.fire({
            title: 'Exportar pagos',
            icon: 'info',
            input: 'select',
            inputLabel: 'Estado de la mensualidad',
            inputOptions: {
                all: 'Todos',
                '2': 'Debe',
            },
            inputValidator: function (value) {
                return new Promise(function (resolve) {
                    if (value !== '') {
                        resolve()
                    } else {
                        resolve('Necesitas Seleccionar Uno.')
                    }
                })
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const exportUrl = result.value === 'all'
                    ? link.href
                    : `${link.href}&status=${result.value}`

                window.open(exportUrl, '_blank')
            }
        })
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
        pay: 0,
        cash: 0,
        consignment: 0,
        debts: 0,
        total: 0
    })

    const statusPay = [1, 9, 10, 11, 12]
    const statusPayCash = [9, 12]
    const statusPayConsignment = [10, 11]
    const statsDeb = [2]

    watch(groupPayments, async (newValue) => {
        totalByType.value.cash = 0
        totalByType.value.consignment = 0
        totalByType.value.pay = 0
        totalByType.value.debts = 0
        totalByType.value.total = 0
        for (const field in paymentFields) {
            totalsFooter.value[`${paymentFields[field]}`] = newValue.filter(pay => statusPay.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.cash += newValue.filter(pay => statusPayCash.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.pay += newValue.filter(pay => statusPay.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.consignment += newValue.filter(pay => statusPayConsignment.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.debts += newValue.filter(pay => statsDeb.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.total += newValue.reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
        }
    }, { deep: true })

    return {
        handleSearch,
        editRow,
        cancelEdition,
        handleSelectChange,
        saveField,
        exportFile,
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
        years,
        categories,
        type_payments,
        paymentTypeLabels,
        typesNoEditables,
        paymentFields,
        totalsFooter,
        totalByType,
    }
}
