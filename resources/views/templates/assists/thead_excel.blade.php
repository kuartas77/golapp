<thead>
<tr>
    <th></th>
    <th></th>
    <th></th>
    <th>Fecha:</th>
    @foreach ($classDays as $class)
        <th>{{$class['day']}}</th>
    @endforeach
    <th></th>
</tr>
<tr>
    <th>Categoría</th>
    <th>Deportista</th>
    <th>Mes</th>
    <th>Clase</th>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <th>{{$i}}</th>
    @endfor
    <th>Observación</th>
</tr>
</thead>
