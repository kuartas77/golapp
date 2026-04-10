import configLanguaje from '@/utils/datatableUtils'
import { useSetting } from '@/store/settings-store'
import { usePageTitle } from "@/composables/use-meta"
import api from "@/utils/axios"
import { getCurrentInstance, onMounted, ref } from "vue"
import * as yup from 'yup'

export default function useAttendances() {
    const composeModalObservation = ref(null)
    const isLoading = ref(false)
    const settings = useSetting()

    const groups = settings.groups
        .filter((group) => group.name !== 'Provisional')
        .map((group) => ({ value: group.id, label: group.full_group }))

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
    const takeAttendance = ref(null)

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
                data: 'inscription',
                title: 'Deportista',
                render: '#player-photo',
                searchable: false,
                with: '30%'
            },
            {
                data: 'inscription.player.full_names',
                title: 'Asistencia',
                render: '#attendance-select',
                searchable: false,
                with: '30%'
            },
            {
                data: 'inscription.player.full_names',
                title: 'Observación',
                render: '#observations',
                searchable: false,
                with: '40%'
            },
        ]
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

    const handleSearchClassdays = async (values, actions) => {
        try {
            classDaySelected.value = null
            isLoading.value = true
            attendancesGroup.value = []

            const response = await api.get(`/api/v2/training_group/classdays`, {
                params: { ...values },
            })

            if (response?.data) {
                classDays.value = response.data
            }
        } catch (error) {
            classDays.value = []
            proxy.$handleBackendErrors(
                error,
                actions.setErrors,
                (msg) => (globalError.value = msg)
            )
        } finally {
            isLoading.value = false
        }
    }

    const clickClassDay = async (classDay) => {
        try {
            isLoading.value = true
            attendancesGroup.value = []
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
            showMessage("Algo salió mal", 'error')
        }
    }

    const onClickOpenModalObservation = async (row) => {
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
                takeAttendance.value = response.data
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
            showMessage("Algo salió mal", 'error')
        } finally {
            takeAttendance.value = null
            modalHidden()
            composeModalObservation.value.hide()
        }
    }

    onMounted(() => {
        initModals()
        usePageTitle('Asistencias')
    })

    return {
        isLoading,
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
        takeAttendance,
        optionsMonths,
        attendanceTypes,
        options,
        handleSearchClassdays,
        clickClassDay,
        onChangeAttendance,
        onClickOpenModalObservation,
        onCancelModalObservation,
        onSaveModalObservation
    }
}