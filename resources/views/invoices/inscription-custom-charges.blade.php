@extends('layouts.app')
@section('title', 'Cargos personalizados')
@section('content')
<x-bread-crumb title="Cargos personalizados" :option="0" />
<x-row-card col-inside="12">
    <p>Administra los cargos personalizados asignados a inscripciones antes y después de facturarlos.</p>
    <div class="table-responsive-md">
        <table class="display compact cell-border" id="chargesTable">
            <thead class="thead-light">
                <tr>
                    <th>Deportista</th>
                    <th>Código</th>
                    <th>Catálogo</th>
                    <th class="text-right">Valor</th>
                    <th class="text-center">Estado</th>
                    <th>Vencimiento</th>
                    <th>Factura</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</x-row-card>

@endsection

@section('modals')
<div class="modal" id="chargeEditModal" tabindex="-1" role="dialog" aria-labelledby="chargeEditModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="chargeEditForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="chargeEditModalTitle">Actualizar cargo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="charge-id">

                    <div class="form-group">
                        <label for="charge-value">Valor</label>
                        <input id="charge-value" name="value" type="text" inputmode="numeric" class="form-control form-control-sm money" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="charge-status">Estado</label>
                        <select id="charge-status" name="status" class="form-control form-control-sm">
                            @foreach($statuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="charge-due-date">Vencimiento</label>
                        <input id="charge-due-date" name="due_date" type="date" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="charge-save-button">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const chargesUrl = "{{ route('inscription-custom-charges.index') }}";
    const statusLabels = @json($statuses);

    function money(value) {
        return '$' + (parseFloat(value) || 0).toFixed(0);
    }

    function cleanMoney(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function searchInputs() {
        let player = this.api().columns(0);
        $("<input type='search' class='' placeholder='Deportista' />")
            .appendTo($(player.header()).empty())
            .on('keyup search', function () {
                if (player.search() !== this.value) {
                    player.search(this.value)
                        .draw();
                }
            });
        let code = this.api().columns(1);
        $("<input type='search' class='' placeholder='Código' />")
            .appendTo($(code.header()).empty())
            .on('keyup search', function () {
                if (code.search() !== this.value) {
                    code.search(this.value)
                        .draw();
                }
            });
    }

    $(document).ready(function() {

        $('.money').inputmask("pesos");

        const table = $('#chargesTable').DataTable({
            dom: 'litp',
            lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
            order: [[6, 'desc']],
            processing: true,
            serverSide: true,
            deferRender: true,
            initComplete: searchInputs,
            ajax: $.fn.dataTable.pipeline({
                url: chargesUrl,
                pages: 5
            }),
            columns: [
                {
                    data: 'player.full_names',
                    name: 'player.full_names',
                    orderable: false,
                    searchable: true,
                    defaultContent: ''
                },
                {
                    data: 'inscription.unique_code',
                    name: 'inscription.unique_code',
                    orderable: false,
                    searchable: true,
                    defaultContent: ''
                },
                {
                    data: 'invoice_custom_item.name',
                    name: 'invoice_custom_item.name',
                    orderable: false,
                    searchable: false,
                    defaultContent: 'Sin catálogo'
                },
                {
                    data: 'value',
                    name: 'value',
                    className: 'dt-body-right',
                    orderable: false,
                    render: data => money(data)
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'dt-body-center',
                    orderable: false,
                    render: data => statusLabels[data] || data
                },
                {
                    data: 'due_date',
                    name: 'due_date',
                    orderable: false,
                    render: data => data ? moment(data).format('DD/MM/YYYY') : ''
                },
                {
                    data: 'invoice_item.invoice.invoice_number',
                    name: 'invoice_item.invoice.invoice_number',
                    orderable: false,
                    searchable: false,
                    defaultContent: ''
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'dt-body-center',
                    render: row => {
                        const canEditDelete = ['pending', 'due'].includes(row.status) && !row.invoice_item_id;

                        return canEditDelete ? `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-xs btn-warning edit-charge"
                            data-id="${row.id}"
                            data-value="${row.value}"
                            data-status="${row.status}"
                            data-due-date="${moment(row.due_date).format('YYYY-MM-DD')}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-danger delete-charge"
                                data-id="${row.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>` : '';
                    }
                }
            ]
        });

        $('#chargesTable tbody').on('click', '.edit-charge', function() {
            const button = $(this);

            $('#charge-id').val(button.data('id'));
            $('#charge-value').val(button.data('value')).inputmask("pesos");
            $('#charge-status').val(button.data('status'));
            $('#charge-due-date').val(button.data('due-date'));
            $('#chargeEditModal').modal('show');
        });

        $('#chargeEditForm').on('submit', function(event) {
            event.preventDefault();

            const chargeId = $('#charge-id').val();
            const saveButton = $('#charge-save-button');

            saveButton.prop('disabled', true);

            $.ajax({
                url: `${chargesUrl}/${chargeId}`,
                method: 'POST',
                data: {
                    value: cleanMoney($('#charge-value').val()),
                    status: $('#charge-status').val(),
                    due_date: $('#charge-due-date').val(),
                    _method: 'PUT',
                    _token: "{{ csrf_token() }}",
                },
                success: (response) => {
                    $('#chargeEditModal').modal('hide');
                    Swal.fire('Listo', response.message, 'success');
                    table.clearPipeline().draw(false);
                },
                error: (xhr) => {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No fue posible actualizar el cargo.', 'error');
                },
                complete: () => {
                    saveButton.prop('disabled', false);
                }
            });
        });

        $('#chargesTable tbody').on('click', '.delete-charge', function() {
            const chargeId = $(this).data('id');

            Swal.fire({
                title: '¿Eliminar cargo?',
                text: 'Sólo se eliminará si está pendiente o en debe sin factura.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (!result.value) {
                    return;
                }

                $.ajax({
                    url: `${chargesUrl}/${chargeId}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: "{{ csrf_token() }}",
                    },
                    success: (response) => {
                        Swal.fire('Listo', response.message, 'success');
                        table.clearPipeline().draw(false);
                    },
                    error: (xhr) => {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No fue posible eliminar el cargo.', 'error');
                    }
                });
            });
        });
    });
</script>
@endpush
