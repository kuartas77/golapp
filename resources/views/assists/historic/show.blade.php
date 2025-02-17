@extends('layouts.app')
@section('title', 'Historico Asistencias')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Historico Asistencias', 'option' => 0])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="month">Mes</label>
                            <span class="bar"></span>
                            {{ html()->select('month', $monthsG, null)->attributes(['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month'])->placeholder('Selecciona...') }}
                            <input type="hidden" name="training_group_id" id="training_group_id"
                                   value="{{$trainingGroup->id}}">
                            <input type="hidden" name="year" id="year" value="{{$year}}">
                        </div>
                    </div>
                    <h6 class="card-subtitle text-themecolor m-b-0 m-t-0">{{$trainingGroup->full_schedule_group}}</h6>
                    @include('assists.assist.table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    @include('modals.assist_observation')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}/';
        let btnPrint = $('#print');
        let btnPrintExcel = $("#print_excel");
        let tableActive = $('#active_table');

        $(document).ready(() => {
            tableActive = $('#active_table').DataTable();
            $("#month").on('change', (e) => {
                let month = e.target.value
                let data = {};
                data.training_group_id = $("#training_group_id").val();
                data.year = $("#year").val();
                data.month = month

                $.get(url_current + `${month}`, data, (response) => {
                    validateData(response, true);
                });
            });
        });

        const validateData = ({table, group_name, count, url_print, url_print_excel}, search) => {
            if (count > 0) {
                let message = search ? `Se Han Encontrado ${count} Asistencia(s).` : `Se Han Creado ${count} Asistencia(s).`;
                tableActive.destroy();
                $("#enabled").empty().append(table);
                initTable();
                btnPrint.prop("href", url_print);
                btnPrint.removeClass('hide');
                btnPrintExcel.prop("href", url_print_excel);
                btnPrintExcel.removeClass('hide');
                swal.fire({
                    title: 'Atención!!',
                    text: message,
                    type: 'info',
                    showCancelButton: false,
                    timer: 1500
                });
            } else {
                tableActive.destroy();
                $("#enabled").empty().append(table);
                initTable();
                btnPrint.prop("href", "");
                btnPrint.addClass('hide');
                btnPrintExcel.prop("href", "");
                btnPrintExcel.addClass('hide');
                swal.fire({
                    title: 'Atención!!',
                    text: "No Se Encontró Información Para El Mes Seleccionado.",
                    type: 'warning',
                    showCancelButton: false,
                    timer: 1500
                });
            }
        }

        const initTable = () => {
            tableActive = $('#active_table').DataTable({
                "paging": false,
                "ordering": false,
                "info": false,
                "scrollY": true,
            });
        }

        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
@endsection
