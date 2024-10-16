<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="active-tab" data-toggle="tab" href="#active" role="tab"
                aria-controls="active" aria-expanded="false">@lang('messages.inscription_actived')</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="inactive-tab" data-toggle="tab" href="#inactive" role="tab"
                aria-controls="inactive" aria-expanded="false">@lang('messages.inscription_inactived')</a>
        </li>
        <li class="nav-item">
            <a class="nav-link">
                {{ html()->select('inscription_year', $inscription_years, now()->year)->attributes(['id'=>'inscription_year','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </a>
        </li>

        @hasanyrole('super-admin|school')
        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                href="{{route('export.inscriptions')}}">
                <i class="fa fa-print" aria-hidden="true"></i>
                @lang('messages.export_excel_inscriptions')
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

    <div class="tab-pane show active" id="active" role="tabpanel" aria-labelledby="active-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="active_table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Foto</th>
                        <th>Código</th>
                        <th>Doc.Identidad</th>
                        <th>Nombres</th>
                        <th>F.Nacimiento</th>
                        <th>Genero</th>
                        <th>Grupo</th>
                        <th>Cert. Médico</th>
                        <th>Teléfonos</th>
                        <th>F.Inicio</th>
                        <th>Categoría</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="tab-pane" id="inactive" role="tabpanel" aria-labelledby="inactive-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="inactive_table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Código</th>
                        <th>Doc.Identidad</th>
                        <th>Nombres</th>
                        <th>F.Nacimiento</th>
                        <th>Genero</th>
                        <th>Cert. Médico</th>
                        <th>Teléfonos</th>
                        <th>Categoría</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>