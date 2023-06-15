<li class="{{ Request::is('home') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('home')}}" aria-expanded="false"><i class="fas fa-home"></i><span class="hide-menu">Inicio</span></a>
</li>

@hasanyrole('super-admin')
<li class="{{ Request::is('backoffice*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-cogs"></i><span class="hide-menu"> BackOffice</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('config.schools.index')}}">Escuelas</a></li>
        <li><a href="{{route('config.schools_info.index')}}">Información Escuelas</a></li>
        <!-- <li><a href="{{route('config.users.index')}}">Usuarios</a></li>
        <li><a href="{{route('config.settings.index')}}">Escuelas</a></li> -->
    </ul>
</li>
@endhasanyrole

@hasanyrole('super-admin|school')
<li class="{{ Request::is('admin*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-cogs"></i><span class="hide-menu"> Administración</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('school.index', ['school' => getSchool(auth()->user())])}}">Escuela</a></li>
        <li><a href="{{route('users.index')}}">Usuarios</a></li>
        <li><a href="{{route('training_groups.index')}}">G. De Entrenamiento</a></li>
        <li><a href="{{route('competition_groups.index')}}">G. De Competencia</a></li>
        <li><a href="{{route('schedules.index')}}">Horarios</a></li>
        <li><a href="{{route('tournaments.index')}}">Torneos</a></li>
        <li><a href="{{route('incidents.index')}}">Incidencias</a></li>
    </ul>
</li>
@endhasanyrole

<li class="{{ Request::is('players*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('players.index')}}" aria-expanded="false"><i class="fas fa-user-circle"></i><span class="hide-menu">Deportistas</span></a>
</li>

<li class="{{ Request::is('inscriptions*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('inscriptions.index')}}" aria-expanded="false"><i class="fas fa-id-card"></i><span class="hide-menu">Inscripciones</span></a>
</li>

<li class="{{ Request::is('assists*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('assists.index')}}" aria-expanded="false"><i class="fas fa-clipboard-list"></i><span class="hide-menu">Asistencias</span></a>
</li>

<li class="{{ Request::is('payments*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('payments.index')}}" aria-expanded="false"><i class="fas fa-dollar-sign"></i><span class="hide-menu">Pagos</span></a>
</li>

<li class="{{ Request::is('matches*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('matches.index')}}" aria-expanded="false"><i class="fas fa-futbol"></i><span class="hide-menu">C. Competencias</span></a>
</li>

<li class="{{ Request::is('historic*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-history"></i><span class="hide-menu">Historicos</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('historic.assists')}}">Asistencias</a></li>
        <li><a href="{{route('historic.payments')}}">Pagos</a></li>
    </ul>
</li>



