<div class="col-lg-3 col-xl-3 col-md-12 col-sm-12">
    <div class="card">
        <div class="card-body">
            <div class="text-center"><img src="{{$player->photo_url}}" class="img-circle" width="150"/>
                <h4 class="card-title m-t-10">{{$player->full_names}}</h4>
                <h6 class="card-subtitle">@lang('messages.unique_code', ['unique_code'=> $player->unique_code])</h6>
                <div class="row text-center justify-content-md-center">
                    <div class="col-12">
                        <span class="form-control-static">@lang('messages.dominant_profile', ['profile'=>$player->dominant_profile])</span>
                    </div>
                </div>
            </div>
            <hr>
            <small class="text-muted">@lang('messages.identification_document') <strong>{{$player->identification_document}}</strong> </small>
            <small class="text-muted p-t-10 db">@lang('messages.rh') <strong>{{$player->rh}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.date_birth') <strong>{{$player->date_birth}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.status') <strong>{{$player->has('inscription') ? 'Activo' : 'Inactivo'}}</strong></small>

            <hr>
            <div class="text-center">
                <a href="{{$player->url_impression}}" class="btn waves-effect waves-light btn-rounded btn-info btn-block"><i class="far fa-file-pdf"></i> @lang('messages.print')</a>
                @hasanyrole('super-admin|school')
                <a href="{{$player->url_edit}}" class="btn waves-effect waves-light btn-rounded btn-outline-info btn-block"><i class="fas fa-pencil-alt"></i> @lang('messages.update_text')</a>
                @endhasanyrole
            </div>
        </div>
    </div>
</div>
