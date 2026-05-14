<div class="modal" id="create_inscription" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{route('inscriptions.store')}}" id="form_create" class="form-material m-t-0" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title text-uppercase"><strong>Nueva Inscripción</strong></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body m-l-20 m-r-20">

                    @include('inscription.fields')

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-info waves-effect text-left" id="btn_add_inscription"
                            disabled="true">Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        const urlSearchPlayers = "{{route('autocomplete.search_unique_code')}}?unique=true";
        const urlList = "{{route('autocomplete.list_code_unique')}}?trashed=true";
        const urlCreate = "{{route('inscriptions.store')}}";

        let options = {
            success: showResponse,  // post-submit callback
            error: showError,
            clearForm: true  ,      // clear all form fields after successful submit
            resetForm: true        // reset the form after successful submit
        };

        function alertSwalMessage(message, type = 'warning') {
            Swal.fire('Atención!',
                message,
                type
            );
        }

        function getAutoCompletes(){
            $.get(urlList, ({data}) => {
                $('#unique_code').typeahead({
                    source: data,
                    scrollBar: true
                });
            });
        }

        function showResponse(responseText, statusText, xhr, $form) {
            $(active_table).DataTable().clearPipeline().draw();
            $('#create_inscription').modal('hide');
            alertSwalMessage(xhr.responseJSON[0], 'success');
        }

        function showError(xhr,  statusText, serverError, $form) {
            alertSwalMessage(xhr.responseJSON[0], 'error');
        }

        getAutoCompletes();
        $(document).ready(function () {
            $('#start_date').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');
            $(".select2").select2({dropdownParent: $('#create_inscription')});

            $("#form_create").validate({
                submitHandler: function (form) {
                    prepareCustomChargesPayload();
                    $(form).ajaxSubmit(options);
                }
            });

            $('#custom_charges_due_date').on('change', function() {
                $('.custom-charge-row:not(.custom-charge-existing) .custom-charge-due-date').val($(this).val());
            });

            $(document).on('change', '.custom-charge-checkbox', function() {
                const row = $(this).closest('.custom-charge-row');
                toggleCustomChargeRow(row, $(this).is(':checked'));
            });

            $(document).on('click', '.custom-charge-row', function(event) {
                if ($(event.target).is('input, label, select, button, a')) {
                    return;
                }

                const row = $(this);
                const checkbox = row.find('.custom-charge-checkbox');

                if (checkbox.prop('disabled')) {
                    return;
                }

                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            $('#unique_code').on('change', function (e) {
                let code = $(this).val();
                if (code.length < 7) return;
                $.get(urlSearchPlayers, {'unique_code': code}, ({data}) => {
                    if (data != null) {
                        $("#player_id").val(data.id);
                        $("#member_name").val(data.full_names);
                        $('#btn_add_inscription').attr('disabled', false);
                        $('#start_date').val(moment().format('YYYY-MM-DD'))

                    } else {
                        alertSwalMessage('El Deportista ya tiene una inscripción ó no se encontró.');
                        $("#member_name").val('');
                        $('#btn_add_inscription').attr('disabled', true);
                    }
                }).fail(() => {
                    alertSwalMessage('El Deportista ya tiene una inscripción ó no se encontró.');
                    $("#member_name").val('');
                    $('#btn_add_inscription').attr('disabled', true);
                });
            });
        });

        function toggleCustomChargeRow(row, enabled) {
            if (row.hasClass('custom-charge-locked')) {
                enabled = false;
            }

            row.find('.custom-charge-item-id, .custom-charge-value, .custom-charge-due-date')
                .prop('disabled', !enabled);
            row.find('.custom-charge-value')
                .prop('readonly', row.hasClass('custom-charge-existing') || !enabled);
            row.toggleClass('table-secondary', row.hasClass('custom-charge-existing'));
        }

        function prepareCustomChargesPayload() {
            const dueDate = $('#custom_charges_due_date').val();
            let index = 0;

            $('.custom-charge-row').each(function() {
                const row = $(this);
                const enabled = row.find('.custom-charge-checkbox').is(':checked') && !row.hasClass('custom-charge-locked');

                row.find('.custom-charge-item-id').attr('name', `custom_charges[${index}][invoice_custom_item_id]`);
                row.find('.custom-charge-value').attr('name', `custom_charges[${index}][value]`);
                row.find('.custom-charge-due-date')
                    .attr('name', `custom_charges[${index}][due_date]`)
                    .val(row.hasClass('custom-charge-existing') ? row.find('.custom-charge-due-date').val() : dueDate);

                toggleCustomChargeRow(row, enabled);

                if (enabled) {
                    index++;
                }
            });
        }

        function customChargeStatusLabel(status) {
            const labels = {
                pending: 'Pendiente',
                due: 'Debe',
                paid: 'Pagado',
            };

            return labels[status] || 'Disponible';
        }

        function setCustomChargeValue(row, value) {
            const input = row.find('.custom-charge-value');

            input.val(parseInt(value, 10) || 0);

            if (input.inputmask) {
                input.inputmask("pesos");
            }
        }

        window.resetCustomCharges = function() {
            $('#existing_custom_charges').addClass('d-none').empty();
            $('#custom_charges_due_date').val(moment().add(15, 'days').format('YYYY-MM-DD'));
            $('.custom-charge-row').removeClass('custom-charge-existing custom-charge-locked table-secondary');
            $('.custom-charge-checkbox').prop('checked', false).prop('disabled', false);
            $('.custom-charge-row').each(function() {
                const row = $(this);
                setCustomChargeValue(row, row.data('unit-price'));
                row.find('.custom-charge-due-date').val($('#custom_charges_due_date').val());
                row.find('.custom-charge-status')
                    .text('Disponible')
                    .removeClass('badge-warning badge-danger badge-success')
                    .addClass('badge-secondary');
                toggleCustomChargeRow(row, false);
            });
        }

        window.renderExistingCustomCharges = function(charges) {
            resetCustomCharges();

            if (!Array.isArray(charges) || charges.length === 0) {
                return;
            }

            const labels = charges.map((charge) => `${charge.name} (${charge.status === 'due' ? 'Debe' : 'Pendiente'})`).join(', ');
            $('#existing_custom_charges')
                .removeClass('d-none')
                .html(`<strong>Cargos activos:</strong> ${labels}`);

            charges.forEach((charge) => {
                const row = $(`.custom-charge-row[data-item-id="${charge.invoice_custom_item_id}"]`);

                if (!row.length) {
                    return;
                }

                row.addClass('custom-charge-existing table-secondary');
                row.toggleClass('custom-charge-locked', charge.status !== 'pending' || Boolean(charge.invoice_item_id));
                row.find('.custom-charge-checkbox')
                    .prop('checked', true)
                    .prop('disabled', row.hasClass('custom-charge-locked'));
                setCustomChargeValue(row, charge.value);
                row.find('.custom-charge-due-date').val(moment(charge.due_date).format('YYYY-MM-DD'));
                row.find('.custom-charge-status')
                    .text(customChargeStatusLabel(charge.status))
                    .removeClass('badge-secondary badge-success')
                    .addClass(charge.status === 'due' ? 'badge-danger' : 'badge-warning');
                toggleCustomChargeRow(row, false);
            });
        }

        resetCustomCharges();
    </script>
@endpush
