import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'datatables.net-responsive-bs5';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.css';
import DataTable from 'datatables.net-vue3';

DataTable.use(DataTablesCore);

const clone = (value) => JSON.parse(JSON.stringify(value ?? null));

const sameDataTableRequest = (currentRequest, previousRequest) => (
    JSON.stringify(currentRequest.order) === JSON.stringify(previousRequest?.order)
    && JSON.stringify(currentRequest.columns) === JSON.stringify(previousRequest?.columns)
    && JSON.stringify(currentRequest.search) === JSON.stringify(previousRequest?.search)
);

export const dataTablePipeline = (ajaxHandler, options = {}) => {
    const pages = Number(options.pages) > 0 ? Number(options.pages) : 5;
    let cacheLower = -1;
    let cacheUpper = null;
    let cacheLastRequest = null;
    let cacheLastJson = null;

    return (request, drawCallback, settings) => {
        let ajax = false;
        let requestStart = request.start;
        const drawStart = request.start;
        const requestLength = request.length;
        const requestEnd = requestStart + requestLength;

        if (settings.clearCache) {
            ajax = true;
            settings.clearCache = false;
        } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
            ajax = true;
        } else if (!sameDataTableRequest(request, cacheLastRequest)) {
            ajax = true;
        }

        cacheLastRequest = clone(request);

        if (ajax) {
            if (requestStart < cacheLower) {
                requestStart -= requestLength * (pages - 1);

                if (requestStart < 0) {
                    requestStart = 0;
                }
            }

            cacheLower = requestStart;
            cacheUpper = requestStart + (requestLength * pages);
            request.start = requestStart;
            request.length = requestLength * pages;

            return ajaxHandler(request, (json) => {
                const response = clone(json);
                cacheLastJson = clone(json);

                if (cacheLower !== drawStart) {
                    response.data.splice(0, drawStart - cacheLower);
                }

                if (requestLength > -1) {
                    response.data.splice(requestLength, response.data.length);
                }

                drawCallback(response);
            }, settings);
        }

        const json = clone(cacheLastJson);
        json.draw = request.draw;
        json.data.splice(0, requestStart - cacheLower);
        json.data.splice(requestLength, json.data.length);
        drawCallback(json);
    };
};

DataTablesCore.Api.register('clearPipeline()', function () {
    return this.iterator('table', (settings) => {
        settings.clearCache = true;
    });
});

export { DataTablesCore };
export default DataTable;
