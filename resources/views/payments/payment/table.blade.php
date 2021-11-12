<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
               aria-controls="enabled" aria-expanded="false">Resultado</a>
        </li>
        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info" href="javascript:void(0)" id="export">
                <i class="fa fa-print" aria-hidden="true"></i> Exportar Pagos En Excel
            </a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">

        <table class="display compact" id="active_table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th class="text-center">Año</th>
                <th class="text-center">Nombres</th>
                <th class="text-center">Código</th>
                <th class="text-center">Ene</th>
                <th class="text-center">Feb</th>
                <th class="text-center">Mar</th>
                <th class="text-center">Abr</th>
                <th class="text-center">May</th>
                <th class="text-center">Jun</th>
                <th class="text-center">Jul</th>
                <th class="text-center">Ago</th>
                <th class="text-center">Sept</th>
                <th class="text-center">Oct</th>
                <th class="text-center">Nov</th>
                <th class="text-center">Dic</th>
            </tr>
            </thead>
            <tbody id="table_body"></tbody>
        </table>

    </div>

</div>

