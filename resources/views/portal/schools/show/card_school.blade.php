<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{$school->logo_file}}" class="img-fluid rounded img-thumbnail" width="120" height="120" alt="{{$school->name}}" />
                <h4 class="card-title m-t-10"><strong>{{$school->name}}</strong></h4>
            </div>
            <hr>
            <div class="text-center">
                <small class="card-title db">Dirección: <strong>{{$school->address}}</strong> </small>
                <small class="card-title db">Teléfono: <strong>{{$school->phone}}</strong></small>
                <small class="card-title db">Correo: <strong>{{$school->email_info}}</strong></small>
            </div>
            <hr>
            <div class="text-center">
                <!-- <a class="btn waves-effect waves-light btn-rounded btn-outline-info btn-block"
                    href="javascript:void(0)" data-toggle="modal" data-target="#modal_update_player" data-backdrop="static" data-keyboard="false">
                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                    @lang('messages.update_text')
                </a> -->
            </div>
        </div>
    </div>
</div>
