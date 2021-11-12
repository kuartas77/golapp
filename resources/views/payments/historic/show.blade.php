@extends('layouts.app')
@section('title', 'Pagos')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Pagos', 'option' => 0])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <input type="hidden" name="year" id="year" value="{{$year}}">
                <input type="hidden" name="training_group_id" id="training_group_id" value="{{$trainingGroup->id}}">
                <div class="card-body">
                    <h4 class="card-title text-themecolor m-t-5">{{$trainingGroup->full_schedule_group}}</h4>
                    @include('payments.payment.table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        let table = $('#active_table');
        const initTable = () => {
            table = $('#active_table').DataTable({
                "paging": false,
                "ordering": false,
                "info": false,
                "scrollX": true,
                "scrollY": true,
                "columns": [
                    {'width': '3%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                    {'width': '7%'},
                ]
            });
        }

        $(document).ready(()=>{
            $("#export").attr('disabled',true);
            table = $('#active_table').DataTable();

            let data = {};
            data.training_group_id = $("#training_group_id").val();
            data.year = $("#year").val();
            $.get(url_current, data, function (response) {
                if (response.count > 0) {
                    table.destroy();
                    $('#table_body').empty();
                    $('#table_body').append(response.rows);
                    initTable();
                    $("#export").attr("href", response.url_export);
                } else {
                    $("#export").attr('disabled',true);
                    $("#export").attr("href","javascript:void(0)");
                    table.destroy();
                    $('#table_body').empty();
                    initTable();
                }
            });
        })
    </script>
@endsection
