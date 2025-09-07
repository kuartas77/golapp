import configLanguaje from '@/utils/datatableUtils';
import { ref } from 'vue';

const groups = ref([])
const inscription_table = ref();

const columns = [
    { data: 'player.photo_url', width: '1%', render: '#photo', searchable: false},
    { data: 'player.unique_code', render: '#link', searchable: true},
    { data: 'player.identification_document', searchable: true},
    { data: 'training_group.name', name: 'training_group_id', orderable: false, searchable: true},
    { data: 'player.category', name: 'inscriptions.category', orderable: false, searchable: true},
    { data: 'player.full_names', name :'player.last_names', searchable: true, orderable: false},
    { data: 'created_at', render: '#date', searchable: false},
];

const options = {
    ...configLanguaje,
    lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        {
            targets: [1, 2, 3, 4, 6],
            width: '5%'
        },
        {
            targets: ['_all'],
            className: 'dt-head-center dt-body-center', // Center align their headers
        },
        // { searchable: false, targets: [0,4, 5]},
        // { orderable: false, targets: [0,4, 5]},
    ],
    scrollX: true,
    serverSide: true,
    processing: true,
    order: [[1, 'desc']],
};






// function redrawUserGrid() {
//     inscription_table.value['dt'].ajax.reload(null, false);
// }

export { columns, options, inscription_table};