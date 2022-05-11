<thead>
<tr>
    <th colspan="3"></th>
    <th>Fecha:</th>
    @foreach ($classDays as $class)
        <th>{{$class['day']}}</th>
    @endforeach
    <th></th>
</tr>
<tr>
    <th colspan="3"></th>
    <th>Clases:</th>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <th>{{$i}}</th>
    @endfor
</tr>
<tr>
    <td>#</td>
    <td>Nombres y Apellidos</td>
    <td>Categoría</td>
    <td>Teléfonos</td>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <th></th>
    @endfor
    <td>% Asis</td>
</tr>
</thead>
