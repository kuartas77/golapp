<thead>
    @if(is_null($column))
    <tr>
        <th></th>
        <th>Fecha:</th>
        @foreach ($classDays as $class)
        <th class="text-center">{{$class['day']}}</th>
        @endforeach
        <th></th>
    </tr>
    <tr>
        <th class="text-center">Deportista</th>
        <th>Clase</th>
        @for ($i = 1; $i <= $classDays->count(); $i++)
            <th class="text-center">{{$i}}</th>
            @endfor
            <th class="text-center">Observación</th>
    </tr>
    @else
    <tr>
        <th class="text-center">Deportista</th>
        <th class="text-center">Asistencia</th>
        <th class="text-center">Observación</th>
    </tr>
    @endif
</thead>