import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// import { useAuthUser } from '@/store/auth-user'
import api from '@/utils/axios'

export default function useInfoSchoolList() {
    const table = ref(null)

    const columns = [
        { data: 'logo_file', width: '1%', render: '#photo', searchable: false, orderable: false },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'inscriptions_count', title: 'inscripciones', searchable: false, orderable: true },
        { data: 'players_count', title: 'Deportistas', searchable: false, orderable: true },
        { data: 'payments_count', title: 'Pagos', searchable: false, orderable: true },
        { data: 'assists_count', title: 'Asistencias', searchable: false, orderable: true },
        { data: 'matches_count', title: 'Competencias', searchable: false, orderable: true },
        { data: 'skill_controls_count', title: 'Controles comp', searchable: false, orderable: true },
        { data: 'tournament_payouts_count', title: 'Pagos Torneos', searchable: false, orderable: true },
        { data: 'users_count', title: 'Usuarios', searchable: false, orderable: true },
        { data: 'tournaments_count', title: 'Torneos', searchable: false, orderable: true },
        { data: 'training_groups_count', title: 'G. Entre', searchable: false, orderable: true },
        { data: 'competition_groups_count', title: 'G. Comp', searchable: false, orderable: true },
        { data: 'incidents_count', title: 'Incidentes', searchable: false, orderable: true },
    ]

    const ajaxConfig = async (data, callback, settings) => {
        try {
            const response = await api.get('/api/v2/datatables/schools_info', { params: data }); // Adjust endpoint and method
            callback({
                data: response.data.data, // Adjust based on your API response structure
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            });
        } catch (error) {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            console.error('Error fetching data:', error);
        }
    }

    const options = {
        ...configLanguaje,
        lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        columnDefs: [
            { responsivePriority: 2, targets: 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            }
        ],
        scrollX: true,
        serverSide: true,
        processing: true,
        order: [[6, 'desc']],
        ajax: ajaxConfig,
        columns: columns
    };

    return { table, options }
}