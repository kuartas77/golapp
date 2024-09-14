<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                aria-controls="enabled" aria-expanded="false">@lang('messages.title_enabled')</a>
        </li>

        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                href="{!! route('training-sessions.create') !!}">
                <i class="fa fa-plus" aria-hidden="true"></i>

            </a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="active_table" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Creado por</th>
                        <th>Grupo Entrenamiento</th>
                        <th>Lugar</th>
                        <th>Periodo</th>
                        <th>Sesion</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>N° Ejercicios</th>
                        <th>Creado En</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

</div>