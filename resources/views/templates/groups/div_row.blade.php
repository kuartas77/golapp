<div class="row no-gutters mb-0 mt-0" data-id="{{$inscription->id}}">
    <div class="col-md-12 element">
        <div class="card text-center" style="max-height: 244px; margin: 5px">
            <div class="card-body">
                <img class='media-object img-rounded' src='{{$inscription->player->photo_url}}' width='120' height='120'>
                <br>
                <small class="media-heading text-themecolor"><strong>{{$inscription->player->full_names}}</strong></small>
                <ul class="list-unstyled">
                    <li><strong>Código: </strong><small>{{$inscription->player->unique_code}}</small></li>
                    <li><strong>Categoría: </strong><small>{{ $inscription->category }}</small></li>
                </ul>
            </div>
        </div>
    </div>
</div>