<div class="row">
    @if($colOutside !== null && $colInside !== '12')
    <div class="col-sm-{{$colOutside}} col-md-{{$colOutside}} col-lg-{{$colOutside}}"></div>
    @endif
    <div class="col-sm-{{$colInside}} col-md-{{$colInside}} col-lg-{{$colInside}}">        
        <div class="card {{$margin}}">
            <div class="card-body">
                {{$slot}}
            </div>
        </div>
    </div>
    @if($colOutside !== null && $colInside !== '12')
    <div class="col-sm-{{$colOutside}} col-md-{{$colOutside}} col-lg-{{$colOutside}}"></div>
    @endif
</div>