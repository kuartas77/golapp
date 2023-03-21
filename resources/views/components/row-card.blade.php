<div class="row">
    @if($colOutside !== null && $colInside !== '12')
    <div class="col-{{$colOutside}}"></div>
    @endif
    <div class="col-{{$colInside}}">        
        <div class="card m-b-0">
            <div class="card-body">
                {{$slot}}
            </div>
        </div>
    </div>
    @if($colOutside !== null && $colInside !== '12')
    <div class="col-{{$colOutside}}"></div>
    @endif
</div>