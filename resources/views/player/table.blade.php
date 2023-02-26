<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="activos-tab" data-toggle="tab" href="#activos" role="tab"
               aria-controls="activos" aria-expanded="false">TODOS</a>
        </li>
        @hasanyrole('super-admin|school')
        <li class=" nav-item ml-auto">

            <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
               href="{!! route('players.create') !!}">
                <i class="fa fa-plus" aria-hidden="true"></i>
                Agregar Deportista
            </a>

            <a class="float-left btn waves-effect waves-light btn-rounded btn-info create_inscription"
               href="javascript:void(0)" data-toggle="modal" data-target="#create_inscription" data-backdrop="static"
               data-keyboard="false">
                <i class="fa fa-plus" aria-hidden="true"></i>
                @lang('messages.inscription_add')
            </a>

        </li>
        @endhasanyrole
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">

        <table class="display compact" id="table_players" width="100%">
            <thead>
            <tr>
                <th></th>
                <th>Foto</th>
                <th>Código</th>
                <th>Doc.Identidad</th>
                <th>Nombres</th>
                <th>Teléfonos</th>
                <th>Genero</th>
                <th>F.Nacimiento</th>
                <th>F.Registro</th>
                <th>Opciones</th>
            </tr>
            </thead>
        </table>
    </div>
</div>


