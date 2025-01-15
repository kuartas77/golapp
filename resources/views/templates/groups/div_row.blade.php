<div class="col-md-4" data-id="{{$inscription->id}}" data-info="{{$inscription->player->full_names}} {{ $inscription->category }}">
    <div class="col element">
        <div class="card text-center text-themecolor">
            <div class="card-body">
                <img class='media-object img-rounded card-img-top' src='{{$inscription->player->photo_url}}' width='90' height='90'>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><small class="card-text">{{$inscription->player->full_names}}</small></li>
                    <li class="list-group-item"><small class="card-text">Categoría: {{ $inscription->category }}</small></li>
                </ul>
            </div>
        </div>
    </div>
</div>