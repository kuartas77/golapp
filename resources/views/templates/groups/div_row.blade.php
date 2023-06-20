<div class="row" data-id="{{$inscription->id}}" data-info="{{$inscription->player->full_names}} {{ $inscription->category }}">
    <div class="col-sm-12 col-md-12 col-lg-12 element">
        <div class="card text-center">
            <div class="card-body" style="min-height: 202px;">
                <img class='media-object img-rounded' src='{{$inscription->player->photo_url}}' width='90' height='90'>
                <ul class="list-unstyled">
                    <small class="media-heading text-themecolor">{{$inscription->player->full_names}}</small>
{{--                    <small>Código: {{$inscription->player->unique_code}}</small>--}}
                    <small>Categoría: {{ $inscription->category }}</small>
                </ul>
            </div>
        </div>
    </div>
</div>
