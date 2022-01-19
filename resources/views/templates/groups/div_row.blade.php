<div class="row" data-id="{{$inscription->id}}">
    <div class="col-sm-2"></div>
    <div class="col-sm-8 element">
        <div class="card text-center">
            <div class="card-body">
                <img class='media-object img-rounded' src='{{$inscription->player->photo_url}}' width='60' height='60'>
                <h4 class="media-heading text-themecolor">{{$inscription->player->full_names}}</h4>
                <ul class="list-unstyled">
                    <li><strong>Código: </strong><small>{{$inscription->player->unique_code}}</small></li>
                    <li><strong>Categoría: </strong><small>{{ $inscription->category }}</small></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-2"></div>
</div>
