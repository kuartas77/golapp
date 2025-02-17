<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor m-b-0 m-t-0 text-uppercase">{{$title}}</h3>
    </div>
    <div class="col-md-7 col-4 align-self-center">
        <div class="d-flex m-t-10 justify-content-end">
            <div class="d-flex m-r-20 m-l-10 hidden-md-down">
                @switch($option)
                    @case(1)
                    @if($birthdays >= 1)
                    <a href="{{route('birthDays')}}" class="btn waves-effect waves-light btn-rounded btn-info" id="export"><strong>Cumpleaños</strong></a>
                    @endif
                    @break
                    @case(2)
                    @hasanyrole('super-admin')
                        <button type="button" class="btn waves-effect waves-light btn-rounded btn-info"
                                data-toggle="modal" data-target="#import_players" id="import"><strong
                                class="text-warning">Importar</strong> Deportistas
                        </button>
                    @endhasanyrole
                    @break
                    @default
                @endswitch

            </div>
        </div>
    </div>
</div>
