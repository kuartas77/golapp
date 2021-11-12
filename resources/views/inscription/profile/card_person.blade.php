<div class="col-lg-3 col-xlg-3 col-md-3">
    <div class="card">
        <div class="card-body">
            <div class="m-t-30 text-center"><img src="{{$inscription->photo}}" class="img-circle" width="150"/>
                <h4 class="card-title m-t-10">{{$inscription->full_names}}</h4>
                <h6 class="card-subtitle">@lang('messages.unique_code', ['unique_code'=> $inscription->unique_code])</h6>
                <div class="row text-center justify-content-md-center">
                    <div class="col-12">
                        <span class="form-control-static">@lang('messages.dominant_profile', ['profile'=>$inscription->dominant_profile])</span>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <hr>
        </div>
        <div class="card-body">
            <small class="text-muted">@lang('messages.identification_document') <strong>{{$inscription->identification_document}}</strong> </small>
            <small class="text-muted p-t-10 db">@lang('messages.rh') <strong>{{$inscription->rh}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.date_birth') <strong>{{$inscription->date_birth}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.status') <strong>{{$inscription->delete_at ? 'Inactivo' : 'Activo'}}</strong></small>

            <hr>
            <div class="text-center">
                <a href="{{$inscription->url_impression}}" class="btn waves-effect waves-light btn-rounded btn-info btn-block"><i class="far fa-file-pdf"></i> @lang('messages.print')</a>
                <a href="{{$inscription->url_edit}}" class="btn waves-effect waves-light btn-rounded btn-outline-info btn-block"><i class="fas fa-pencil-alt"></i> @lang('messages.update_text')</a>
            </div>
        </div>
    </div>
</div>
