import cloneDeep from "lodash.clonedeep";

import { useSetting } from '@/store/settings-store';
import { useAuthUser } from '@/store/auth-user'
import { SCHOOL_PERMISSION_KEYS } from '@/config/school-permissions'
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { computed, getCurrentInstance, onMounted, ref, toRaw, watch } from "vue";
import * as yup from 'yup';


export default function useMonthlyPayments() {
    const currentDate = new Date()
    const settings = useSetting()
    const auth = useAuthUser()
    const paymentGroups = settings.normal_training_groups.length ? settings.normal_training_groups : settings.groups
    const groups = ref(paymentGroups.filter((group) => group.name !== 'Provisional').map((group) => ({ value: group.id, label: group.full_group })));
    const categories = ref(settings.categories.map((i) => ({ value: i.category, label: i.category })));
    const years = settings.inscription_years
    const defaultYear = years.find((year) => Number(year.value) === currentDate.getFullYear())?.value
        ?? years[years.length - 1]?.value
        ?? currentDate.getFullYear()
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
    const defaultMonthField = paymentFields[currentDate.getMonth() + 1] ?? 'january'
    const type_payments = computed(() => settings.paymentTypeOptions.filter((option) => (
        option.value !== '15' || auth.hasSchoolPermission(SCHOOL_PERMISSION_KEYS.playerCredits)
    )))
    const paymentTypeLabels = computed(() => settings.paymentTypeLabels)
    const statusCatalog = ref({
        statuses: [],
        groups: {
            paid: [1, 9, 10, 11, 12, 15],
            debt: [2],
            player_credit: [15],
        },
        months: [],
    })
    const monthOptions = computed(() => [
        { value: '', label: 'Todos los meses' },
        ...(statusCatalog.value.months?.length ? statusCatalog.value.months : [
            { value: 'january', label: 'Enero' },
            { value: 'february', label: 'Febrero' },
            { value: 'march', label: 'Marzo' },
            { value: 'april', label: 'Abril' },
            { value: 'may', label: 'Mayo' },
            { value: 'june', label: 'Junio' },
            { value: 'july', label: 'Julio' },
            { value: 'august', label: 'Agosto' },
            { value: 'september', label: 'Septiembre' },
            { value: 'october', label: 'Octubre' },
            { value: 'november', label: 'Noviembre' },
            { value: 'december', label: 'Diciembre' },
        ]),
    ])
    const statusOptions = computed(() => [
        { value: '', label: 'Todos los estados' },
        ...((statusCatalog.value.statuses?.length ? statusCatalog.value.statuses : type_payments.value)
            .map((status) => ({ value: String(status.value), label: status.label }))),
    ])
    const selected_group = ref(null)
    const groupPayments = ref([])
    const playerSearchTerm = ref('')
    const globalError = ref(null)
    const export_excel = ref(null)
    const export_pdf = ref(null)
    const modelGroup = ref(null)
    const modelCategory = ref(null)
    const { proxy } = getCurrentInstance()
    const schema = yup.object().shape({
        year: yup.mixed().required(),
        category: yup.string().nullable().optional(),
        training_group_id: yup.string().nullable().test(
            'current-year-filter-required',
            'Para el año actual selecciona un grupo o una categoría.',
            function (value) {
                const selectedYear = Number(this.parent.year)
                const category = this.parent.category

                return selectedYear !== currentDate.getFullYear() || Boolean(value || category)
            }
        ),
        month: yup.string().nullable().optional(),
        status: yup.string().nullable().optional(),
        player_name: yup.string().nullable().optional(),
        unique_code: yup.string().nullable().optional(),
    })
    const formData = ref({
        year: defaultYear,
        training_group_id: null,
        category: null,
        month: defaultMonthField,
        status: '',
        player_name: '',
        unique_code: '',
    })
    const viewMode = ref('annual')
    const bulkStatus = ref('')
    const bulkAmount = ref(0)
    const isBulkUpdating = ref(false)
    const isLoading = ref(false)
    const editingCell = ref(null)
    const backupCell = ref(null)
    const annuity_amount = ref(0)
    const enrollment_amount = ref(0)
    const monthly_amount = ref(0)
    const player_count = ref(0)
    const paymentFieldNames = Object.values(paymentFields)
    const normalizePlayerName = (value) => String(value ?? '')
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLocaleLowerCase()

    const filteredGroupPayments = computed(() => {
        const searchTerm = normalizePlayerName(playerSearchTerm.value.trim())

        if (!searchTerm) {
            return groupPayments.value
        }

        return groupPayments.value.filter((payPlayer) => normalizePlayerName(
            payPlayer?.player?.full_names
        ).includes(searchTerm))
    })
    const visiblePlayerCount = computed(() => filteredGroupPayments.value.length)
    const retiredRowsCount = computed(() => filteredGroupPayments.value.filter(
        (payPlayer) => Boolean(payPlayer.inscription_deleted)
    ).length)

    const handleSearch = async (values, actions) => {
        try {
            groupPayments.value = []
            playerSearchTerm.value = ''
            isLoading.value = true
            const selectedMonth = values.month ?? formData.value.month ?? defaultMonthField
            formData.value = {
                ...formData.value,
                ...values,
                month: selectedMonth || '',
                status: values.status || '',
                player_name: values.player_name || '',
                unique_code: values.unique_code || '',
            }
            const params = {
                category: values.category,
                year: values.year ?? defaultYear,
                training_group_id: values.training_group_id,
                month: selectedMonth || null,
                status: values.status || null,
                player_name: values.player_name || null,
                unique_code: values.unique_code || null,
                dataRaw: true
            }
            const response = await api.get(`/api/v2/payments`, { params: params })
            if (response?.data) {
                const data = response.data
                categories.value = data.filter_options?.categories ?? categories.value
                groups.value = data.filter_options?.groups ?? groups.value
                if (data.rows.length) {
                    groupPayments.value = data.rows
                    if (!data.filter_options?.categories && !values.category) {
                        categories.value = [...new Set(data.rows.map((row) => row.category).filter(Boolean))]
                            .sort((left, right) => left.localeCompare(right, 'es', { numeric: true }))
                            .map((category) => ({ value: category, label: category }))
                    }
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

                selected_group.value = groups.value.find((group) => String(group.value) === String(values.training_group_id)) ?? null
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
        if (payPlayer.inscription_deleted) {
            showMessage('La inscripción está retirada; reactívala antes de modificar pagos.', 'warning')
            return
        }

        editingCell.value = { payPlayer, field }
        backupCell.value = cloneDeep(toRaw(payPlayer));
    };

    const canEditPaymentRow = (payPlayer) => {
        return !payPlayer.inscription_deleted
    }

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
        const exportStatusOptions = statusOptions.value.reduce((options, option) => {
            options[option.value || 'all'] = option.label
            return options
        }, {})

        Swal.fire({
            title: 'Exportar pagos',
            icon: 'info',
            input: 'select',
            inputLabel: 'Estado de la mensualidad',
            inputOptions: exportStatusOptions,
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
        api.get('/api/v2/payments/status-catalog')
            .then((response) => {
                statusCatalog.value = response.data ?? statusCatalog.value
            })
            .catch(() => {
                statusCatalog.value.statuses = type_payments.value
            })
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
        playerCredit: 0,
        debts: 0,
        total: 0
    })

    const statusPay = computed(() => statusCatalog.value.groups?.paid ?? [1, 9, 10, 11, 12, 15])
    const statusPayCash = [9, 12]
    const statusPayConsignment = [10, 11]
    const statusPayPlayerCredit = computed(() => statusCatalog.value.groups?.player_credit ?? [15])
    const statsDeb = computed(() => statusCatalog.value.groups?.debt ?? [2])

    watch(filteredGroupPayments, async (newValue) => {
        totalByType.value.cash = 0
        totalByType.value.consignment = 0
        totalByType.value.playerCredit = 0
        totalByType.value.pay = 0
        totalByType.value.debts = 0
        totalByType.value.total = 0
        for (const field in paymentFields) {
            totalsFooter.value[`${paymentFields[field]}`] = newValue.filter(pay => statusPay.value.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.cash += newValue.filter(pay => statusPayCash.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.pay += newValue.filter(pay => statusPay.value.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.consignment += newValue.filter(pay => statusPayConsignment.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.playerCredit += newValue.filter(pay => statusPayPlayerCredit.value.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.debts += newValue.filter(pay => statsDeb.value.includes(pay[`${paymentFields[field]}`]))
                .reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)

            totalByType.value.total += newValue.reduce((accumulator, pay) => accumulator + pay[`${paymentFields[field]}_amount`], 0)
        }
    }, { deep: true })

    const selectedMonthField = computed(() => formData.value.month || monthOptions.value.find((month) => month.value)?.value || 'january')
    const selectedMonthLabel = computed(() => monthOptions.value.find((month) => month.value === selectedMonthField.value)?.label || 'Mes')
    const monthlyRows = computed(() => filteredGroupPayments.value.map((payPlayer) => {
        const field = selectedMonthField.value

        return {
            payPlayer,
            field,
            status: payPlayer[field],
            statusLabel: paymentTypeLabels.value[String(payPlayer[field])] || payPlayer[field],
            amount: payPlayer[`${field}_amount`],
        }
    }))
    const debtorCount = computed(() => filteredGroupPayments.value.filter((payPlayer) => Object.values(paymentFields)
        .some((field) => statsDeb.value.includes(payPlayer[field]))).length)
    const receiptableCount = computed(() => filteredGroupPayments.value.reduce((count, payPlayer) => count + Object.values(paymentFields)
        .filter((field) => statusPay.value.includes(payPlayer[field])).length, 0))
    const bulkEligibleRows = computed(() => filteredGroupPayments.value.filter((payPlayer) => canEditPaymentRow(payPlayer, selectedMonthField.value)))
    const bulkStatusOptions = computed(() => statusOptions.value.filter((option) => option.value !== ''))

    const applyBulkPaymentStatus = async () => {
        const field = selectedMonthField.value
        const status = Number(bulkStatus.value)

        if (!field) {
            showMessage('Selecciona un mes antes de aplicar una acción masiva.', 'warning')
            return
        }

        if (!isKnownBulkStatus(status)) {
            showMessage('Selecciona un estado válido para la acción masiva.', 'warning')
            return
        }

        const rows = bulkEligibleRows.value

        if (!rows.length) {
            showMessage('No hay mensualidades activas para actualizar.', 'warning')
            return
        }

        const amountField = `${field}_amount`
        const previousValues = new Map(rows.map((row) => [row.id, {
            status: row[field],
            amount: row[amountField],
        }]))
        const amount = normalizeAmount(bulkAmount.value)
        const statusLabel = statusOptions.value.find((option) => Number(option.value) === status)?.label || 'estado seleccionado'
        const confirmation = await Swal.fire({
            title: 'Aplicar acción masiva',
            text: `Se actualizará ${selectedMonthLabel.value} a "${statusLabel}" para ${rows.length} deportista(s) activos visibles.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Aplicar',
            cancelButtonText: 'Cancelar',
        })

        if (!confirmation.isConfirmed) {
            return
        }

        isBulkUpdating.value = true

        try {
            rows.forEach((row) => {
                row[field] = status
                row[amountField] = amount
                syncPaymentField(row, field)
            })

            const response = await api.post('/api/v2/payments/bulk-update', {
                payment_ids: rows.map((row) => row.id),
                year: formData.value.year ?? defaultYear,
                month: field,
                status,
                amount,
            })

            const result = response?.data?.data
            const updatedIds = new Set((result?.updated_ids ?? []).map((id) => Number(id)))
            const updatedRows = Number(result?.updated_count ?? 0)
            const failedRows = rows.length - updatedRows

            rows.forEach((row) => {
                if (!updatedIds.has(Number(row.id))) {
                    const previous = previousValues.get(row.id)
                    row[field] = previous.status
                    row[amountField] = previous.amount
                }
            })

            if (failedRows > 0 && updatedRows > 0) {
                showMessage(`Se actualizaron ${updatedRows} mensualidad(es). ${failedRows} registro(s) no se pudieron guardar.`, 'warning')
                return
            }

            if (failedRows > 0) {
                showMessage('No fue posible aplicar la acción masiva.', 'error')
                return
            }

            showMessage(`Mensualidades actualizadas para ${updatedRows} deportista(s).`)
        } catch (error) {
            rows.forEach((row) => {
                const previous = previousValues.get(row.id)
                row[field] = previous.status
                row[amountField] = previous.amount
            })
            showMessage(getSaveErrorMessage(error), 'error')
        } finally {
            isBulkUpdating.value = false
        }
    }

    const isKnownBulkStatus = (status) => statusOptions.value
        .some((option) => option.value !== '' && Number(option.value) === status)

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
        playerSearchTerm,
        filteredGroupPayments,
        visiblePlayerCount,
        schema,
        formData,
        editingCell,
        groups,
        years,
        categories,
        type_payments,
        paymentTypeLabels,
        statusCatalog,
        monthOptions,
        statusOptions,
        canEditPaymentRow,
        paymentFields,
        viewMode,
        bulkStatus,
        bulkAmount,
        bulkStatusOptions,
        bulkEligibleRows,
        isBulkUpdating,
        applyBulkPaymentStatus,
        monthlyRows,
        selectedMonthField,
        selectedMonthLabel,
        debtorCount,
        receiptableCount,
        retiredRowsCount,
        totalsFooter,
        totalByType,
    }
}
