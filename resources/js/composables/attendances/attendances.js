import cloneDeep from "lodash.clonedeep";

import configLanguaje from '@/utils/datatableUtils';
import { useSetting } from '@/store/settings-store';
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { getCurrentInstance, onMounted, ref, toRaw } from "vue";
import * as yup from 'yup';

export default function useAttendances() {
    const composeModalAttendance = ref(null);
    const composeModalObservations = ref(null);
    const isLoading = ref(false);
    const settings = useSetting();
    const groups = settings.groups.map((group) => {
        return { id: group.id, full_group: group.full_group };
    });

    const schema = yup.object().shape({
        training_group: yup
            .object({
                id: yup.string().required(),
                full_group: yup.string().required(),
            })
            .required(),
        month: yup
            .object({
                value: yup.string().required(),
                label: yup.string().required(),
            })
            .required(),
    });
    const formData = ref({
        training_group: null,
        month: null,
    });
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
    const backupCell = ref(null)

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
    ];
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
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
        ],
        // scrollY: 500,
        // scrollCollapse: true,
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
            { data: 'inscription', title: 'Deportista', render: '#player-photo', searchable: false, with: '30%' },
            { data: 'inscription.player.full_names', title: 'Asistencia', render: '#bagClick', searchable: false, with: '30%' },
            { data: 'inscription.player.full_names', title: 'Observaciones Mensuales', render: '#observations', searchable: false, with: '40%' },
        ]
    };

    const handleSearchClassdays = async (values, actions) => {
        try {
            classDaySelected.value = null;
            isLoading.value = true;
            attendancesGroup.value = [];
            const params = {
                month: values.month?.label,
                training_group_id: values.training_group?.id,
            };

            const response = await api.get(`/api/v2/training_group/classdays`, {
                params: params,
            });
            if (response?.data) {
                classDays.value = response.data;
            }
        } catch (error) {
            classDays.value = [];
            proxy.$handleBackendErrors(
                error,
                actions.setErrors,
                (msg) => (globalError.value = msg)
            );
        } finally {
            isLoading.value = false;
        }
    };

    const clickClassDay = async (classDay) => {
        try {
            isLoading.value = true;
            attendancesGroup.value = [];
            classDaySelected.value = classDay;
            const params = {
                month: classDay.month,
                training_group_id: classDay.group_id,
                column: classDay.column,
                dataRaw: true,
            };

            const response = await api.get(`/api/v2/assists`, { params: params });
            if (response?.data) {
                attendancesGroup.value = response.data.rows;
                export_pdf.value = response.data.url_print;
                export_excel.value = response.data.url_print_excel;
                if (response.data.rows.length === 0) {
                    showMessage("No se encontraron resultados para el grupo en este mes.", 'warning')
                }
            }
        } catch (error) {
            attendancesGroup.value = [];
            export_pdf.value = null;
            export_excel.value = null;
            showMessage("Algo salió mal", 'error')
        } finally {
            isLoading.value = false;
        }
    };

    const initModals = () => {
        composeModalAttendance.value = new window.bootstrap.Modal(document.getElementById("composeModalAttendance"), {
            backdrop: 'static', // Prevents closing the modal by clicking outside
            keyboard: false,    // Disables closing the modal with the escape key
            focus: false         // Focuses the modal when initialized (default is true)
        });
        composeModalObservations.value = new window.bootstrap.Modal(document.getElementById("composeModalObservations"));
    }

    const onClickOpenModalAttendance = async (row) => {
        takeAttendance.value = row
        backupCell.value = cloneDeep(toRaw(row))

        const response = await api.get(`/api/v2/assists/${row.id}`, {
            params: {
                column: classDaySelected.value.column,
                date: `${classDaySelected.value.year}-${classDaySelected.value.month}-${classDaySelected.value.date}`,
                action: 'assist'
            }
        })

        takeAttendance.value = response.data
        composeModalAttendance.value.show();
    }

    const onCancelModalAttendance = () => {
        let changed = attendancesGroup.value.find((attendance) => attendance.id === backupCell.value.id)
        if (backupCell.value && changed) {
            const clon = cloneDeep(toRaw(backupCell.value))
            changed[classDaySelected.value.column] = clon[classDaySelected.value.column]
            changed['observations'] = clon['observations']
        }

        backupCell.value = null
        takeAttendance.value = null
        composeModalAttendance.value.hide()
    }

    const onSaveModalAttendance = async () => {
        let data = {
            _method: 'PUT',
            id: takeAttendance.value.id,
            observations: takeAttendance.value.observation,
            attendance_date: `${classDaySelected.value.year}-${classDaySelected.value.month}-${classDaySelected.value.date}`,
        }
        data[classDaySelected.value.column] = takeAttendance.value.value

        const response = await api.post(`/api/v2/assists/${data.id}`, data)

        let changed = attendancesGroup.value.find((attendance) => attendance.id === data.id)
        if (response.data && changed) {
            changed[classDaySelected.value.column] = data[classDaySelected.value.column]
        }

        backupCell.value = null
        takeAttendance.value = null
        composeModalAttendance.value.hide()
        showMessage('Guardado correctamente')
    }

    const onClickOpenModalObservations = async (row) => {
        takeAttendance.value = null
        const response = await api.get(`/api/v2/assists/${row.id}`, { params: { action: 'observation' } })
        if (response.data) {
            takeAttendance.value = response.data
            composeModalObservations.value.show()
        } else {
            showMessage("Algo salió mal", 'error')
            takeAttendance.value = null
        }
    }

    const showMessage = (msg = "", type = "success") => {
        const toast = window.Swal.mixin({ toast: true, position: "top", showConfirmButton: false, timer: 5000 });
        toast.fire({ icon: type, title: msg, padding: "10px 20px" });
    };

    onMounted(() => {
        initModals()
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
        onClickOpenModalAttendance,
        onCancelModalAttendance,
        onSaveModalAttendance,
        onClickOpenModalObservations
    }
}