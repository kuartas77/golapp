<table>
    <thead>
    <tr>
        <th>Código</th>
        <th>Fecha Registro</th>
        <th>Años Inscripción</th>
        <th>Categoria</th>
        <th>Nombres</th>
        <th>Genero</th>
        <th>Fecha De Nacimiento</th>
        <th>Lugar De Nacimiento</th>
        <th>Documento ID</th>
        <th>Grupo Sanguineo</th>
        <th>Colegio Escuela</th>
        <th>Grado</th>
        <th>Dirección</th>
        <th>Municipio</th>
        <th>Barrio</th>
        <th>Teléfonos</th>
        <th>Celular</th>
        <th>EPS</th>
        <th>Correo</th>
        <th>Grupo De Entrenamiento</th>
        <th>Familiar Nombres</th>
        <th>Familiar Cedula</th>
        <th>Familiar Teléfonos</th>
        <th>Familiar Nombres</th>
        <th>Familiar Cedula</th>
        <th>Familiar Teléfonos</th>
    </tr>
    </thead>
    <tbody>
    @foreach($players as $player)
        <tr>
            <td>{{$player->unique_code}}</td>
            <td>{{$player->created_at->format('Y-m-d')}}</td>
            <td>{{$player->pay_years->implode(',')}}</td>
            <td>{{$player->category}}</td>
            <td>{{$player->full_names}}</td>
            <td>{{$player->gender}}</td>
            <td>{{$player->date_birth}}</td>
            <td>{{$player->place_birth}}</td>
            <td>{{$player->identification_document}}</td>
            <td>{{$player->rh}}</td>
            <td>{{$player->school}}</td>
            <td>{{$player->degree}}</td>
            <td>{{$player->address}}</td>
            <td>{{$player->municipality}}</td>
            <td>{{$player->neighborhood}}</td>
            <td>{{$player->phones}}</td>
            <td>{{$player->mobile}}</td>
            <td>{{$player->eps}}</td>
            <td>{{$player->email}}</td>
            <td>{{$player->inscription ? $player->inscription->trainingGroup->name : ''}}</td>
            @forelse($player->people as $people)
                <td>{{$people?->names}}</td>
                <td>{{$people?->identification_card}}</td>
                <td>{{$people?->phone ?? '' . " " . $people?->mobile ?? '' }}</td>
            @empty
                <td>No Registra</td>
                <td>No Registra</td>
                <td>No Registra</td>
                <td>No Registra</td>
                <td>No Registra</td>
                <td>No Registra</td>
            @endforelse
        </tr>
    @endforeach
    </tbody>
</table>
