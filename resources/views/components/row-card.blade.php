<div class="row {{ $margin }}">
    @if($colOutside !== null && $colInside !== '12')
        <div class="col-md-{{ $colOutside }}"></div>
    @endif
    <div class="col-md-{{ $colInside }}">
        <div class="card m-b-0">
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
    @if($colOutside !== null && $colInside !== '12')
        <div class="col-md-{{ $colOutside }}"></div>
    @endif
</div>
