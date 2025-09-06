<div class="modal" id="create_inscription" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl mw-100 w-75">
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
                    $(form).ajaxSubmit(options);
                }
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
    </script>
@endpush
