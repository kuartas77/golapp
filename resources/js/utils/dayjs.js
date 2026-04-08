import dayjs from 'dayjs';
import 'dayjs/locale/es';
import localizedFormat from 'dayjs/plugin/localizedFormat';

// dayjs.extend(localeData);
dayjs.locale('es');
dayjs.extend(localizedFormat);


export default dayjs