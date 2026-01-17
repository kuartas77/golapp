<li class="{{ Request::is('home') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('home')}}" aria-expanded="false"><i class="fas fa-home"></i><span class="hide-menu">Inicio</span></a>
</li>
@hasanyrole(['super-admin'])
@if(!empty($admin_schools))
    <li class="{{ Request::is('players*') ? 'active' : '' }}">
        <a class="waves-effect waves-dark" href="#" onclick="selectSchool()" aria-expanded="false"><i class="fas fa-home"></i><span class="hide-menu">Seleccionar Escuela</span></a>
    </li>
    @endif
@endhasanyrole
@hasanyrole(['school'])
    @if(!empty($admin_schools))
    <li class="{{ Request::is('players*') ? 'active' : '' }}">
        <a class="waves-effect waves-dark" href="#" onclick="selectSchool()" aria-expanded="false"><i class="fas fa-home"></i><span class="hide-menu">Seleccionar Sede</span></a>
    </li>
    @endif
@endhasanyrole
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
        <li><a href="{{route('schedules.index')}}">Horarios</a></li>
        <li><a href="{{route('tournaments.index')}}">Torneos</a></li>
        <li><a href="{{route('training_groups.index')}}">G. De Entrenamiento</a></li>
        <li><a href="{{route('competition_groups.index')}}">G. De Competencia</a></li>
        {{-- <li><a href="{{route('incidents.index')}}">Incidencias</a></li> --}}
    </ul>
</li>
<li class="{{ Request::is('players*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('players.index')}}" aria-expanded="false"><i class="fas fa-user-circle"></i><span class="hide-menu">Deportistas</span></a>
</li>

<li class="{{ Request::is('inscriptions*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('inscriptions.index')}}" aria-expanded="false"><i class="fas fa-id-card"></i><span class="hide-menu">Inscripciones</span></a>
</li>

<li class="">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-dollar-sign"></i><span class="hide-menu"> Pagos</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('payments.index')}}">Mensualidades</a></li>
        <li><a href="{{route('tournamentpayout.index')}}">Torneos</a></li>
    </ul>
</li>


<li class="{{ Request::is('invoices*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-dollar-sign"></i><span class="hide-menu"> Facturación</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('invoices.index')}}">Facturas</a></li>
        <li><a href="{{route('items.invoices.index')}}">Items Facturas</a></li>
    </ul>
</li>
@endhasanyrole

<!-- <li class="{{ Request::is('training-sessions*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('training-sessions.index')}}" aria-expanded="false"><i class="fas fa-clipboard-list"></i><span class="hide-menu">S. Entrenamiento</span></a>
</li> -->

<li class="{{ Request::is('assists*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('assists.index')}}" aria-expanded="false"><i class="fas fa-clipboard-list"></i><span class="hide-menu">Asistencias</span></a>
</li>

<li class="{{ Request::is('matches*') ? 'active' : '' }}">
    <a class="waves-effect waves-dark" href="{{route('matches.index')}}" aria-expanded="false"><i class="fas fa-futbol"></i><span class="hide-menu">C. Competencias</span></a>
</li>

@hasanyrole('super-admin|school')
<li class="{{ Request::is('reports*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fa fa-flag"></i><span class="hide-menu">Informes</span></a>
    <ul aria-expanded="false" class="collapse">
        <!-- <li><a href="{{route('reports.assists')}}">Asistencias</a></li> -->
        <li><a href="{{route('reports.payments')}}">Mensualidades</a></li>
    </ul>
</li>

<!-- <li class="{{ Request::is('historic*') ? 'active' : '' }}">
    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="fas fa-history"></i><span class="hide-menu">Historicos</span></a>
    <ul aria-expanded="false" class="collapse">
        <li><a href="{{route('historic.assists')}}">Asistencias</a></li>
        <li><a href="{{route('historic.payments')}}">Pagos</a></li>
    </ul>
</li> -->
@endhasanyrole

@push('scripts')
@hasanyrole(['super-admin','school'])
@if(!empty($admin_schools))
<script>
    const isSchool = {{$isSchool}};
    const text = isSchool === 1 ? 'sede': 'escuela';
    const urlchooseSchool = "{{route('school.choose')}}";
    const schools = @json($admin_schools);
    function selectSchool(){
        swal({
            title: `Para seguir seleciona una ${text}`,
            type: "info",
            input: 'select',
            inputOptions: schools,
            inputPlaceholder: 'Selecciona...',
            allowOutsideClick: false,
            allowEscapeKey:false,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: "Cancelar",
            inputValidator: function (value) {
                return new Promise(function (resolve) {
                    if (value !== '') {
                        resolve();
                    } else {
                        resolve(`Necesitas seleccionar una ${text}`);
                    }
                });
            }
        }).then(function (result) {
            if(result.value){
                $.post(urlchooseSchool, {'school_id': result.value}, function(data){
                    setTimeout(location.reload(), 2000)
                });
            }
        });
    }
</script>
@endif
@endhasanyrole
@endpush