<tr class="tr-tit">
    <td colspan="3" rowspan="2"></td>
    <td class="center texto bold">Fecha:</td>
    @foreach ($classDays as $class)
        <th class="text-center bold">{{$class['day']}}</th>
    @endforeach
    <td rowspan="2"></td>
</tr>
<tr class="tr-tit">
    <td class="center texto bold">Clases:</td>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <td class="center texto bold">{{$i}}</td>
    @endfor
</tr>
<tr class="tr-tit">
    <td class="center texto bold">#</td>
    <td class="center texto bold">Nombres y Apellidos</td>
    <td class="center texto bold">Categoría</td>
    <td class="center texto bold">Teléfonos</td>
    @for ($i = 1; $i <= count($classDays); $i++)
        <td class="center texto"></td>
    @endfor
    <td class="center texto bold">% Asis</td>
</tr>
