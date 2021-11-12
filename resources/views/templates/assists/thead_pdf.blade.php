<tr class="tr-tit">
    <td colspan="3" rowspan="2"></td>
    <td class="center texto">Días:</td>
    @foreach ($classDays as $class)
        <th class="text-center">{{$class['day']}}</th>
    @endforeach
    <td rowspan="2"></td>
</tr>
<tr class="tr-tit">
    <td class="center texto">Clases:</td>
    @for ($i = 1; $i <= $classDays->count(); $i++)
        <td class="center texto">{{$i}}</td>
    @endfor
</tr>
<tr class="tr-tit">
    <td class="center texto">#</td>
    <td class="center texto">Nombres y Apellidos</td>
    <td class="center texto">Categoría</td>
    <td class="center texto">Teléfonos</td>
    @for ($i = 1; $i <= count($classDays); $i++)
        <td class="center texto"></td>
    @endfor
    <td class="center texto">% Asis</td>
</tr>
