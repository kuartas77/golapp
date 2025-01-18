<div class="col-md-2">
    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <img src="{{$player->photo_url_public}}" class="img-fluid rounded img-thumbnail" width="120" height="120" alt="{{$player->full_names}}" />
                <h4 class="card-title m-t-10">{{$player->full_names}}</h4>
                <h6 class="card-subtitle">@lang('messages.unique_code', ['unique_code'=> $player->unique_code])</h6>
                @if($player->dominant_profile)
                <div class="row text-center justify-content-md-center">
                    <div class="col-12">
                        <span class="form-control-static">@lang('messages.dominant_profile', ['profile'=>$player->dominant_profile])</span>
                    </div>
                </div>
                @endif
            </div>
            <hr>
            <small class="text-muted">@lang('messages.identification_document') <strong>{{$player->identification_document}}</strong> </small>
            <small class="text-muted p-t-10 db">@lang('messages.rh') <strong>{{$player->rh}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.date_birth') <strong>{{$player->date_birth}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.status') <strong>{{$player->has('inscription') ? 'Activo' : 'Inactivo'}}</strong></small>

            <hr>
            <div class="text-center">
                <a class="btn waves-effect waves-light btn-rounded btn-outline-info btn-block"
                    href="javascript:void(0)" data-toggle="modal" data-target="#modal_update_player" data-backdrop="static" data-keyboard="false">
                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                    @lang('messages.update_text')
                </a>
            </div>
        </div>
    </div>
</div>
