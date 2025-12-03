<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="activos-tab" data-toggle="tab" href="#activos" role="tab"
                aria-controls="activos" aria-expanded="false">Contratos</a>
        </li>

        <li class=" nav-item ml-auto">

            <a href="{!! route('config.contracts.create') !!}"
                class="float-right btn waves-effect waves-light btn-rounded btn-info">Crear Contrato</a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <div class="table-responsive-md">
            <table class="table display compact cell-border" id="user-table" style="width:100%">
                <thead>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Tipo</th>
                    <th>Escuela</th>
                    <th>Nombre</th>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                    <tr>
                        <td><a href="{{ route('config.contracts.edit', [$contract->id]) }}" class="">{{ $contract->id }}</a></td>
                        <td>{{ $contract->contract_type->code }}</td>
                        <td>{{ $contract->contract_type->name }}</td>
                        <td>{{ $contract->school->name }}</td>
                        <td>{{ $contract->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>