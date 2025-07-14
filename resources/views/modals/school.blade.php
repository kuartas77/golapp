<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('config.schools.store')}}" id="form_create" class="form-material m-t-0" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Agregar Escuela</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        @include('backoffice.school.form')
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-info waves-effect text-left">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    function readFile(input) {
        let label = $(input).next('label.custom-file-label')
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#player-img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
            // label.empty().html(input.files[0].name)
            label.empty().html('Seleccionada.')
        }else{
            label.empty().html("Seleccionar...")
            $('#player-img').attr('src', 'https://app.golapp.com.co/img/ballon.png"');
        }
    }
    $('#file-upload').on('change', function(){
        readFile(this);
    });
</script>
@endpush