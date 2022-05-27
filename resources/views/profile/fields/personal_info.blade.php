<div class="col-lg-3 col-xl-3 col-md-12 col-sm-12">
    <div class="card">
        <div class="card-body">
            <div class="text-center"><img src="{{$profile->url_photo}}" class="img-circle" width="150"/>
                <h4 class="card-title m-t-10">{{$profile->user->name}}</h4>
                <h6 class="card-subtitle">@lang('messages.unique_code', ['unique_code'=> $profile->user->id])</h6>
            </div>
            <hr>
            <small class="text-muted">@lang('messages.identification_document') <strong>{{$profile->identification_document}}</strong> </small>
            <small class="text-muted p-t-10 db">@lang('messages.date_birth') <strong>{{$profile->date_birth}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.gender') <strong>{{$profile->gender}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.address') <strong>{{$profile->address}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.phone') <strong>{{$profile->phone}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.mobile') <strong>{{$profile->mobile}}</strong></small>
            <small class="text-muted p-t-10 db">@lang('messages.position') <strong>{{$profile->position}}</strong></small>
            <hr>
            <div class="text-center">
                <a class="btn waves-effect waves-light btn-rounded btn-info btn-block" href="{!! route('profiles.edit', [$profile->id]) !!}">Modificar Perfil</a>
            </div>
        </div>
    </div>
</div>
