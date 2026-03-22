@extends('layouts.app')

@section('title', 'Reportes de asistencia')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">Reportes de asistencia</h1>

    <div class="row g-4">

        {{-- MENSUAL POR JUGADOR --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Reporte mensual por jugador</strong>
                </div>
                <div class="card-body">
                    <form id="form-monthly-player" class="row g-3 mb-3">

                        <div class="col-md-2">
                            <label class="form-label">Año</label>
                            {{ html()->select('year', $years, $defaultYear)->attributes(['class' => 'form-control form-control-sm', 'id'=>'year']) }}
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="month">Mes</label>
                                {{ html()->select('month', $months_keys, $defaultMonth)->attributes(['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month']) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Grupo</label>
                            <select name="training_group_id" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($trainingGroups as $group)
                                    <option value="{{ $group->id }}" data-school-id="{{ $group->school_id }}">
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info mt-4">Consultar</button>
                        </div>
                    </form>

                    <table id="table-monthly-player" class="display compact cell-border nowrap w-100">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Jugador</th>
                                <th>Grupo</th>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Asistencias</th>
                                <th>Faltas</th>
                                <th>Excusas</th>
                                <th>Retiros</th>
                                <th>Incapacidades</th>
                                <th>Sesiones</th>
                                <th>% Asistencia</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- MENSUAL POR GRUPO --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Reporte mensual por grupo</strong>
                </div>
                <div class="card-body">
                    <form id="form-monthly-group" class="row g-3 mb-3">
                         <div class="col-md-2">
                            <label class="form-label">Año</label>
                            {{ html()->select('year', $years, $defaultYear)->attributes(['class' => 'form-control form-control-sm', 'id'=>'year']) }}
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="month">Mes</label>
                                {{ html()->select('month', $months_keys, $defaultMonth)->attributes(['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month']) }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Grupo</label>
                            <select name="training_group_id" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($trainingGroups as $group)
                                    <option value="{{ $group->id }}" data-school-id="{{ $group->school_id }}">
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info mt-4">Consultar</button>
                        </div>
                    </form>

                    <table id="table-monthly-group" class="display compact cell-border nowrap w-100">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Jugadores</th>
                                <th>Asistencias</th>
                                <th>Faltas</th>
                                <th>Excusas</th>
                                <th>Retiros</th>
                                <th>Incapacidades</th>
                                <th>Sesiones</th>
                                <th>% Asistencia</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- ANUAL CONSOLIDADO --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Reporte anual consolidado</strong>
                </div>
                <div class="card-body">
                    <form id="form-annual-consolidated" class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Año</label>
                            {{ html()->select('year', $years, $defaultYear)->attributes(['class' => 'form-control form-control-sm', 'id'=>'year']) }}
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Grupo</label>
                            <select name="training_group_id" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($trainingGroups as $group)
                                    <option value="{{ $group->id }}" data-school-id="{{ $group->school_id }}">
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info mt-4">Consultar</button>
                        </div>
                    </form>

                    <table id="table-annual-consolidated" class="display compact cell-border nowrap w-100">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Jugador</th>
                                <th>Grupo</th>
                                <th>Año</th>
                                <th>Asistencias</th>
                                <th>Faltas</th>
                                <th>Excusas</th>
                                <th>Retiros</th>
                                <th>Incapacidades</th>
                                <th>Sesiones</th>
                                <th>% Asistencia</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')


<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script>


    const reportRoutes = {
        monthlyPlayer: @json(route('reports.assists.monthly-by-player')),
        monthlyGroup:  @json(route('reports.assists.monthly-by-group')),
        annualConsolidated: @json(route('reports.assists.annual-consolidated')),
        exportBase: @json(url('/export')),
    };

    function formParams(formSelector) {
        const params = {};
        $(formSelector).serializeArray().forEach(function(item) {
            if (item.value !== '') {
                params[item.name] = item.value;
            }
        });
        return params;
    }

    function exportUrl(report, format, formSelector) {
        return `${reportRoutes.exportBase}/${report}/${format}?${$.param(formParams(formSelector))}`;
    }

    function filterGroupsBySchool(formSelector) {
        const $form = $(formSelector);
        const schoolId = $form.find('[name="school_id"]').val();
        const $group = $form.find('[name="training_group_id"]');

        $group.find('option[data-school-id]').each(function() {
            const visible = !schoolId || String($(this).data('school-id')) === String(schoolId);
            $(this).toggle(visible);
        });

        const currentOption = $group.find('option:selected');
        if (currentOption.length && currentOption.data('school-id') && String(currentOption.data('school-id')) !== String(schoolId)) {
            $group.val('');
        }
    }

    function createReportTable(config) {
        return $(config.table).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            searching: true,
            stateSave: false,
            dom: 'Brtip',
            ajax: {
                url: config.ajax,
                data: function(d) {
                    return $.extend({}, d, formParams(config.form));
                }
            },
            buttons: [
                {
                    text: 'Excel',
                    className: 'btn btn-success btn-sm',
                    action: function () {
                        window.open(exportUrl(config.reportKey, 'xlsx', config.form), '_blank');
                    }
                },
                {
                    text: 'PDF',
                    className: 'btn btn-danger btn-sm',
                    action: function () {
                        window.open(exportUrl(config.reportKey, 'pdf', config.form), '_blank');
                    }
                },
            ],
            language: {
                processing: 'Procesando...',
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                zeroRecords: 'No se encontraron resultados',
                emptyTable: 'No hay datos disponibles',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            },
            columns: config.columns
        });
    }

    $(function () {
        ['#form-monthly-player', '#form-monthly-group', '#form-annual-consolidated'].forEach(function(formSelector) {
            filterGroupsBySchool(formSelector);

            $(formSelector).find('[name="school_id"]').on('change', function() {
                filterGroupsBySchool(formSelector);
            });
        });

        const monthlyPlayerTable = createReportTable({
            table: '#table-monthly-player',
            form: '#form-monthly-player',
            ajax: reportRoutes.monthlyPlayer,
            reportKey: 'monthly-player',
            columns: [
                { data: 'unique_code', name: 'p.unique_code' },
                { data: 'player_name', name: 'player_name' },
                { data: 'training_group_name', name: 'training_group_name' },
                { data: 'year', name: 'd.year' },
                { data: 'month', name: 'd.month' },
                { data: 'total_asistencias', name: 'total_asistencias' },
                { data: 'total_faltas', name: 'total_faltas' },
                { data: 'total_excusas', name: 'total_excusas' },
                { data: 'total_retiros', name: 'total_retiros' },
                { data: 'total_incapacidades', name: 'total_incapacidades' },
                { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas' },
                { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia' }
            ]
        });

        const monthlyGroupTable = createReportTable({
            table: '#table-monthly-group',
            form: '#form-monthly-group',
            ajax: reportRoutes.monthlyGroup,
            reportKey: 'monthly-group',
            columns: [
                { data: 'training_group_name', name: 'training_group_name' },
                { data: 'year', name: 'd.year' },
                { data: 'month', name: 'd.month' },
                { data: 'total_jugadores', name: 'total_jugadores' },
                { data: 'total_asistencias', name: 'total_asistencias' },
                { data: 'total_faltas', name: 'total_faltas' },
                { data: 'total_excusas', name: 'total_excusas' },
                { data: 'total_retiros', name: 'total_retiros' },
                { data: 'total_incapacidades', name: 'total_incapacidades' },
                { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas' },
                { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia' }
            ]
        });

        const annualConsolidatedTable = createReportTable({
            table: '#table-annual-consolidated',
            form: '#form-annual-consolidated',
            ajax: reportRoutes.annualConsolidated,
            reportKey: 'annual-consolidated',
            columns: [
                { data: 'unique_code', name: 'p.unique_code' },
                { data: 'player_name', name: 'player_name' },
                { data: 'training_group_name', name: 'training_group_name' },
                { data: 'year', name: 'd.year' },
                { data: 'total_asistencias', name: 'total_asistencias' },
                { data: 'total_faltas', name: 'total_faltas' },
                { data: 'total_excusas', name: 'total_excusas' },
                { data: 'total_retiros', name: 'total_retiros' },
                { data: 'total_incapacidades', name: 'total_incapacidades' },
                { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas' },
                { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia' }
            ]
        });

        $('#form-monthly-player').on('submit', function (e) {
            e.preventDefault();
            monthlyPlayerTable.ajax.reload();
        });

        $('#form-monthly-group').on('submit', function (e) {
            e.preventDefault();
            monthlyGroupTable.ajax.reload();
        });

        $('#form-annual-consolidated').on('submit', function (e) {
            e.preventDefault();
            annualConsolidatedTable.ajax.reload();
        });
    });
</script>
@endpush