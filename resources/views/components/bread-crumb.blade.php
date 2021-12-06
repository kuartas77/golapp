<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor m-b-0 m-t-0">{{$title}}</h3>
    </div>
    <div class="col-md-7 col-4 align-self-center">
        <div class="d-flex m-t-10 justify-content-end">
            <div class="d-flex m-r-20 m-l-10 hidden-md-down">
                @switch($option)
                    @case(1)
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-info" data-toggle="modal" data-target="#create" data-backdrop="static" data-keyboard="false">Agregar Tarea</button>
                    @break
                    @case(2)
                    <a href="{{route('export.clients')}}" class="btn waves-effect waves-light btn-rounded btn-info" id="export"><strong class="text-warning">Exportar</strong> Clientes en Excel</a>
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-info" data-toggle="modal" data-target="#import_client" id="import"><strong class="text-warning">Importar</strong> Clientes con Excel</button>
                    @break

                    @default
                @endswitch
            </div>
        </div>
    </div>
</div>