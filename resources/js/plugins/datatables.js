import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'datatables.net-responsive-bs5';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.css';
import DataTable from 'datatables.net-vue3';

DataTable.use(DataTablesCore);

export { DataTablesCore };
export default DataTable;
