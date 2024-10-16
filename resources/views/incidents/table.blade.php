<nav>
    <ul class="nav nav-tabs customtab">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                aria-controls="enabled" aria-expanded="false">Resultado</a>
        </li>
        @hasanyrole('super-admin|school')
        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info" id="btn-add"
                href="javascript:void(0)" data-toggle="modal" data-target="#create" data-backdrop="static" data-keyboard="false">
                <i class="fa fa-plus" aria-hidden="true"></i>
                Agregar Incidencia
            </a>

        </li>
        @endhasanyrole
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="active_table">
                <thead>
                    <tr>
                        <th>Formador</th>
                        <th># Incidencias</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>