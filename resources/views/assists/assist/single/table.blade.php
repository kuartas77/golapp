<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                aria-controls="enabled" aria-expanded="false">Resultado</a>
        </li>
        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info hide" href="javascript:void(0)" id="print" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Exportar Asistencias En PDF
            </a>
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info hide" href="javascript:void(0)" id="print_excel" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Exportar Asistencias En Excel
            </a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="active_table" style="width:100%">
                <thead>
                    <tr>
                        <th>Deportista</th>
                        <th>Asistencia</th>
                        <th>Observación</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

</div>