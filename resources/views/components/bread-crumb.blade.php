<!-- BREADCRUMB -->
<div class="breadcrumb-wrapper-content d-flex justify-content-start">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
            <li class="breadcrumb-item">
                @switch($option)
                @case(1)
                @if($birthdays >= 1)
                <a href="{{route('birthDays')}}" class="waves-effect waves-light" id="export"><strong>Cumpleaños</strong></a>
                @endif
                @break
                @case(2)
                @hasanyrole('super-admin')
                <a href="javascript:void(0);" class="waves-effect waves-light" data-toggle="modal" data-target="#import_players" id="import">
                    <strong class="text-warning">Importar</strong>
                </a>
                @endhasanyrole
                @break
                @default
                @endswitch
            </li>
        </ol>
    </nav>

</div>
<!-- /BREADCRUMB -->