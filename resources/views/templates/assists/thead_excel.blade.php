<tr>
    <th colspan="4" rowspan="2">{{$month}}</th>
    <th>Fecha:</th>
    @foreach ($classDays as $class)
        <th>{{$class['day']}}</th>
    @endforeach
    <th rowspan="2"></th>
</tr>
<tr>
    <th>Clases:</th>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <th>{{$i}}</th>
    @endfor
</tr>
<tr>
    <td>#</td>
    <td>Nombres y Apellidos</td>
    <td>Codigo</td>
    <td>Categoría</td>
    <td>Teléfonos</td>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <th></th>
    @endfor
    <td>% Asis</td>
</tr>