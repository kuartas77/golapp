<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                aria-controls="enabled" aria-expanded="false">Resultado</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="total-tab" data-toggle="tab" role="tab"
                aria-controls="disabled" aria-expanded="false">Total: $ 0</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="cash-tab" data-toggle="tab" role="tab"
                aria-controls="disabled" aria-expanded="false">Efectivo: $ 0</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="consignment-tab" data-toggle="tab" role="tab"
                aria-controls="disabled" aria-expanded="false">Consignación: $ 0</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" id="other-tab" data-toggle="tab" role="tab"
                aria-controls="disabled" aria-expanded="false">Otros: $ 0</a>
        </li>
        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info disabled" href="" id="export-excel" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Exportar Pagos En Excel
            </a>
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info disabled" href="" id="export-pdf" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> Exportar Pagos En PDF
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
                        <th class="text-center">Nombres</th>
                        <th class="text-center">Categoria</th>
                        <th class="text-center">Matrícula</th>
                        <th class="text-center">Enero</th>
                        <th class="text-center">Febrero</th>
                        <th class="text-center">Marzo</th>
                        <th class="text-center">Abril</th>
                        <th class="text-center">Mayo</th>
                        <th class="text-center">Junio</th>
                        <th class="text-center">Julio</th>
                        <th class="text-center">Agosto</th>
                        <th class="text-center">Septiembre</th>
                        <th class="text-center">Octubre</th>
                        <th class="text-center">Noviembre</th>
                        <th class="text-center">Diciembre</th>
                    </tr>
                </thead>
                <tbody id="table_body"></tbody>
                <tfoot>
                    <tr>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                        <th style="text-align:center"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>