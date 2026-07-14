import configLanguaje from '@/utils/datatableUtils'
import { useSetting } from '@/store/settings-store'
import { usePageTitle } from "@/composables/use-meta"
import api from "@/utils/axios"
import { computed, getCurrentInstance, useTemplateRef, nextTick, onMounted, ref } from "vue"
import * as yup from 'yup'

export default function useAttendances() {
    usePageTitle('Asistencias')

    const composeModalObservation = ref(null)
    const isLoading = ref(false)
    const isBulkUpdating = ref(false)
    const settings = useSetting()
    const attendance_table = useTemplateRef('attendance_table')

    const attendanceGroups = computed(() => (
        settings.attendance_training_groups?.length
            ? settings.attendance_training_groups
            : settings.groups
    ))

    const groups = computed(() => attendanceGroups.value
        .filter((group) => group.name !== 'Provisional')
        .map((group) => ({ value: group.id, label: group.full_group })))

    const schema = yup.object().shape({
        training_group_id: yup.string().required(),
        month: yup.string().required(),
    })

    const formData = ref({
        training_group_id: null,
        month: new Date().getMonth() + 1,
    })

    const globalError = ref(null)
    const { proxy } = getCurrentInstance()

    const modelGroup = ref(null)
    const modelMonth = ref(null)
    const export_pdf = ref(null)
    const export_excel = ref(null)
    const classDays = ref([])
    const classDaySelected = ref(null)
    const attendancesGroup = ref([])
    const lastSearchValues = ref(null)
    const playerSearchTerm = ref('')
    const takeAttendance = ref(null)
    const retiredRowsCount = computed(() => attendancesGroup.value.filter((row) => Boolean(row.inscription_deleted)).length)
    const eligibleAttendanceRows = computed(() => attendancesGroup.value.filter((row) => ! attendanceRowReadOnly(row)))
    const eligibleAttendanceRowsCount = computed(() => eligibleAttendanceRows.value.length)
    const canBulkMarkAttendance = computed(() => Boolean(classDaySelected.value) && eligibleAttendanceRowsCount.value > 0)

    const optionsMonths = [
        { value: 1, label: "Enero" },
        { value: 2, label: "Febrero" },
        { value: 3, label: "Marzo" },
        { value: 4, label: "Abril" },
        { value: 5, label: "Mayo" },
        { value: 6, label: "Junio" },
        { value: 7, label: "Julio" },
        { value: 8, label: "Agosto" },
        { value: 9, label: "Septiembre" },
        { value: 10, label: "Octubre" },
        { value: 11, label: "Noviembre" },
        { value: 12, label: "Diciembre" },
    ]

    const attendanceTypes = {
        1: "Asistencia",
        2: "Falta",
        3: "Excusa",
        4: "Retiro",
        5: "Incapacidad",
    }

    const buildPlayerSearchText = (row) => [
        row?.inscription?.player?.full_names,
        row?.inscription?.player?.unique_code,
        row?.inscription?.player?.category,
    ].filter(Boolean).join(' ')
    const filteredAttendancesGroup = computed(() => {
        const searchTerm = playerSearchTerm.value.trim().toLocaleLowerCase()

        if (! searchTerm) {
            return attendancesGroup.value
        }

        return attendancesGroup.value.filter((row) => buildPlayerSearchText(row).toLocaleLowerCase().includes(searchTerm))
    })
    const playerSearchTitle = `
        <input
            type="search"
            class="form-control form-control-sm"
            placeholder="Buscar deportista"
            aria-label="Buscar deportista"
            data-attendance-player-search="true"
        />
    `

    const options = {
        ...configLanguaje,
        lengthMenu: [[8, 20, 30, 50], [8, 20, 30, 50]],
        columnDefs: [
            { responsivePriority: 1, targets: 1 },
            {
                targets: [0, 1],
                width: '30%'
            },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center',
            },
        ],
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        paging: true,
        ordering: false,
        serverSide: false,
        processing: true,
        order: [[1, 'desc']],
        ajax: null,
        columns: [
            {
                data: buildPlayerSearchText,
                title: playerSearchTitle,
                render: '#player-photo',
                searchable: true,
                with: '50%'
            },
            {
                data: 'id',
                title: 'Asistencia',
                render: '#attendance-select',
                searchable: false,
                with: '20%'
            },
            {
                data: 'id',
                title: 'Observación',
                render: '#observations',
                searchable: false,
                with: '40%'
            },
        ]
    }

    const isTruthyFlag = (value) => value === true || value === 1 || value === '1'

    const attendanceRowReadOnly = (row) => isTruthyFlag(row?.inscription_deleted) || isTruthyFlag(row?.period_locked)

    const attendanceRowReadOnlyMessage = (row) => {
        if (isTruthyFlag(row?.period_locked)) {
            return 'Este periodo ya está cerrado para instructores. Solicita a la escuela una corrección administrativa.'
        }

        return 'La inscripción está retirada; reactívala antes de modificar asistencias.'
    }

    const applyPlayerSearch = (searchValue) => {
        playerSearchTerm.value = searchValue?.target ? searchValue.target.value : (searchValue ?? '')
    }

    const bindPlayerSearchInput = () => {
        const searchInput = document.querySelector('#attendance_table thead input[data-attendance-player-search="true"]')

        if (! searchInput) {
            return
        }

        searchInput.oninput = (event) => applyPlayerSearch(event)
        searchInput.onclick = (event) => event.stopPropagation()
    }

    const buildAttendanceDate = () => {
        if (!classDaySelected.value) return null

        const year = classDaySelected.value.year
        const month = String(classDaySelected.value.month).padStart(2, '0')
        const day = String(classDaySelected.value.date).padStart(2, '0')

        return `${year}-${month}-${day}`
    }

    const normalizeAttendanceValue = (value) => {
        if (value === '' || value === null || value === undefined) return null
        return Number(value)
    }

    const getAssistErrorMessage = (error) => {
        const backendErrors = error.response?.data?.errors
        if (backendErrors) {
            const firstError = Object.values(backendErrors).flat()[0]
            if (firstError) {
                return firstError
            }
        }

        return error.response?.data?.message || 'No fue posible actualizar la asistencia.'
    }

    const handleSearchClassdays = async (values, actions = null) => {
        try {
            classDaySelected.value = null
            isLoading.value = true
            attendancesGroup.value = []
            playerSearchTerm.value = ''
            export_pdf.value = null
            export_excel.value = null
            lastSearchValues.value = { ...values }
            modelGroup.value = attendanceGroups.value.find((group) => String(group.id) === String(values.training_group_id)) ?? null
            modelMonth.value = optionsMonths.find((month) => Number(month.value) === Number(values.month)) ?? null

            const response = await api.get(`/api/v2/training_group/classdays`, {
                params: { ...values },
            })

            if (response?.data) {
                classDays.value = response.data
            }
        } catch (error) {
            classDays.value = []
            if (actions?.setErrors) {
                proxy.$handleBackendErrors(
                    error,
                    actions.setErrors,
                    (msg) => (globalError.value = msg)
                )
            } else {
                showMessage("No fue posible consultar los días de entrenamiento.", 'error')
            }
        } finally {
            isLoading.value = false
        }
    }

    const clickClassDay = async (classDay) => {
        try {
            isLoading.value = true
            attendancesGroup.value = []
            playerSearchTerm.value = ''
            classDaySelected.value = classDay

            const params = {
                month: classDay.month,
                training_group_id: classDay.group_id,
                column: classDay.column,
                dataRaw: true,
            }

            const response = await api.get(`/api/v2/assists`, { params })

            if (response?.data) {
                attendancesGroup.value = response.data.rows
                export_pdf.value = response.data.url_print
                export_excel.value = response.data.url_print_excel
                await nextTick()
                bindPlayerSearchInput()

                if (response.data.rows.length === 0) {
                    showMessage("No se encontraron resultados para el grupo en este mes.", 'warning')
                }
            }
        } catch (error) {
            attendancesGroup.value = []
            export_pdf.value = null
            export_excel.value = null
            showMessage("Algo salió mal", 'error')
        } finally {
            isLoading.value = false
        }
    }

    const createMissingAttendances = async () => {
        const values = lastSearchValues.value

        if (! values?.training_group_id || ! values?.month) {
            showMessage('Selecciona un grupo y mes antes de crear asistencias.', 'warning')
            return
        }

        try {
            isLoading.value = true

            await api.post('/api/v2/assists', {
                training_group_id: values.training_group_id,
                month: values.month,
            })

            if (classDaySelected.value) {
                await clickClassDay(classDaySelected.value)
            }

            showMessage('Asistencias creadas correctamente.')
        } catch (error) {
            showMessage(error.response?.data?.message || 'No fue posible crear las asistencias.', 'error')
        } finally {
            isLoading.value = false
        }
    }

    const initModals = () => {
        composeModalObservation.value = new window.bootstrap.Modal(
            document.getElementById("composeModalObservation"),
            {
                backdrop: 'static',
                keyboard: false,
                focus: false
            }
        )
    }

    const onChangeAttendance = async (row, selectedValue) => {
        if (attendanceRowReadOnly(row)) {
            showMessage(attendanceRowReadOnlyMessage(row), 'warning')
            return
        }

        const previousValue = row[classDaySelected.value.column]
        const normalizedValue = normalizeAttendanceValue(selectedValue)

        row[classDaySelected.value.column] = normalizedValue

        try {
            const data = {
                _method: 'PUT',
                id: row.id,
            }

            data[classDaySelected.value.column] = normalizedValue

            const response = await api.post(`/api/v2/assists/${row.id}`, data)

            if (response?.data) {
                showMessage('Guardado correctamente')
            } else {
                row[classDaySelected.value.column] = previousValue
                showMessage("Algo salió mal", 'error')
            }
        } catch (error) {
            row[classDaySelected.value.column] = previousValue
            showMessage(getAssistErrorMessage(error), 'error')
        }
    }

    const markAttendanceForAllLoaded = async () => {
        if (! classDaySelected.value) {
            showMessage('Selecciona una clase antes de marcar asistencia masiva.', 'warning')
            return
        }

        const rows = eligibleAttendanceRows.value

        if (! rows.length) {
            showMessage('No hay deportistas activos para marcar asistencia.', 'warning')
            return
        }

        const column = classDaySelected.value.column
        const previousValues = new Map(rows.map((row) => [row.id, row[column]]))
        let updatedRows = 0
        let failedRows = 0

        isBulkUpdating.value = true

        try {
            rows.forEach((row) => {
                row[column] = 1
            })

            const response = await api.post('/api/v2/assists/bulk-update', {
                assist_ids: rows.map((row) => row.id),
                training_group_id: classDaySelected.value.group_id,
                month: classDaySelected.value.month,
                year: classDaySelected.value.year,
                column,
                value: 1,
            })

            const result = response?.data?.data
            const updatedIds = new Set((result?.updated_ids ?? []).map((id) => Number(id)))

            updatedRows = Number(result?.updated_count ?? 0)
            failedRows = rows.length - updatedRows

            rows.forEach((row) => {
                if (! updatedIds.has(Number(row.id))) {
                    row[column] = previousValues.get(row.id)
                }
            })
        } catch (error) {
            failedRows = rows.length
            rows.forEach((row) => {
                row[column] = previousValues.get(row.id)
            })
        } finally {
            isBulkUpdating.value = false
        }

        if (failedRows > 0 && updatedRows > 0) {
            showMessage(`Se marcaron ${updatedRows} asistencia(s). ${failedRows} registro(s) no se pudieron guardar.`, 'warning')
            return
        }

        if (failedRows > 0) {
            showMessage('No fue posible marcar la asistencia masiva.', 'error')
            return
        }

        showMessage(`Asistencia marcada para ${updatedRows} deportista(s).`)
    }

    const onClickOpenModalObservation = async (row) => {
        if (attendanceRowReadOnly(row)) {
            showMessage(attendanceRowReadOnlyMessage(row), 'warning')
            return
        }

        try {
            isLoading.value = true
            takeAttendance.value = null

            const response = await api.get(`/api/v2/assists/${row.id}`, {
                params: {
                    column: classDaySelected.value.column,
                    date: buildAttendanceDate(),
                    action: 'assist'
                }
            })

            if (response?.data) {
                takeAttendance.value = {
                    ...response.data,
                    inscription_deleted: attendanceRowReadOnly(row),
                    period_locked: isTruthyFlag(row?.period_locked),
                }
                composeModalObservation.value.show()
            } else {
                showMessage("Algo salió mal", 'error')
            }
        } catch (error) {
            takeAttendance.value = null
            showMessage("Algo salió mal", 'error')
        } finally {
            isLoading.value = false
        }
    }

    const onCancelModalObservation = () => {
        takeAttendance.value = null
        modalHidden()
        composeModalObservation.value.hide()
    }

    const onSaveModalObservation = async () => {
        if (takeAttendance.value?.inscription_deleted || takeAttendance.value?.period_locked) {
            showMessage(attendanceRowReadOnlyMessage(takeAttendance.value), 'warning')
            return
        }

        try {
            const data = {
                _method: 'PUT',
                id: takeAttendance.value.id,
                observations: takeAttendance.value.observation,
                attendance_date: buildAttendanceDate(),
            }

            data[classDaySelected.value.column] = takeAttendance.value.value ?? null

            const response = await api.post(`/api/v2/assists/${data.id}`, data)

            if (response?.data) {
                showMessage('Guardado correctamente')
            } else {
                showMessage("Algo salió mal", 'error')
            }
        } catch (error) {
            showMessage(getAssistErrorMessage(error), 'error')
        } finally {
            takeAttendance.value = null
            modalHidden()
            composeModalObservation.value.hide()
        }
    }

    onMounted(async () => {
        await settings.getSettings()
        initModals()
        await nextTick()
        bindPlayerSearchInput()
    })

    return {
        attendance_table,
        isLoading,
        isBulkUpdating,
        groups,
        schema,
        formData,
        modelGroup,
        modelMonth,
        export_pdf,
        export_excel,
        classDays,
        classDaySelected,
        attendancesGroup,
        filteredAttendancesGroup,
        takeAttendance,
        retiredRowsCount,
        eligibleAttendanceRowsCount,
        canBulkMarkAttendance,
        optionsMonths,
        attendanceTypes,
        options,
        attendanceRowReadOnly,
        applyPlayerSearch,
        handleSearchClassdays,
        createMissingAttendances,
        clickClassDay,
        markAttendanceForAllLoaded,
        onChangeAttendance,
        onClickOpenModalObservation,
        onCancelModalObservation,
        onSaveModalObservation
    }
}
