<ul class="list-unstyled menu-categories" id="accordionExample">

    <li class="menu {{ Request::is('home') ? 'active' : '' }}">
        <a href="{{route('home')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Inicio</span>
            </div>
        </a>
    </li>

    @hasanyrole(['super-admin'])
    @if(!empty($admin_schools))
    <li class="menu">
        <a href="javascript:void(0);" onclick="selectSchool()" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                <span>Cambiar Escuela</span>
            </div>
        </a>
    </li>
    @endif
    @endhasanyrole
    @hasanyrole(['school'])
    @if(!empty($admin_schools))
    <li class="menu">
        <a href="javascript:void(0);" onclick="selectSchool()" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
                <span>Seleccionar Sede</span>
            </div>
        </a>
    </li>
    @endif
    @endhasanyrole


    @hasanyrole('super-admin')
    <li class="menu {{ Request::is('backoffice*') ? 'active' : '' }}">

        <a href="#super-admin" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-hash">
                    <line x1="4" y1="9" x2="20" y2="9"></line>
                    <line x1="4" y1="15" x2="20" y2="15"></line>
                    <line x1="10" y1="3" x2="8" y2="21"></line>
                    <line x1="16" y1="3" x2="14" y2="21"></line>
                </svg>
                <span>BackOffice</span>
            </div>
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>
        <ul class="collapse submenu list-unstyled" id="super-admin" data-bs-parent="#accordionExample">
            <li>
                <a href="{{route('config.schools.index')}}"> Escuelas </a>
            </li>
            <li>
                <a href="{{route('config.schools_info.index')}}"> Información Escuelas </a>
            </li>


        </ul>
    </li>
    @endhasanyrole

    @hasanyrole('super-admin|school')
    <li class="menu {{ Request::is('admin*') ? 'active' : '' }}">

        <a href="#admin" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-hash">
                    <line x1="4" y1="9" x2="20" y2="9"></line>
                    <line x1="4" y1="15" x2="20" y2="15"></line>
                    <line x1="10" y1="3" x2="8" y2="21"></line>
                    <line x1="16" y1="3" x2="14" y2="21"></line>
                </svg>
                <span>Administración</span>
            </div>
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>
        <ul class="collapse submenu list-unstyled" id="admin" data-bs-parent="#accordionExample">
            <li>
                <a href="{{route('school.index', ['school' => getSchool(auth()->user())])}}"> Escuela </a>
            </li>
            <li>
                <a href="{{route('users.index')}}"> Usuarios </a>
            </li>
            <li>
                <a href="{{route('training_groups.index')}}"> Grupos Entrenamiento </a>
            </li>
            <li>
                <a href="{{route('competition_groups.index')}}"> Grupos Competencia </a>
            </li>
            <li>
                <a href="{{route('schedules.index')}}"> Horarios </a>
            </li>
            <li>
                <a href="{{route('tournaments.index')}}"> Torneos </a>
            </li>
            <li>
                <a href="{{route('incidents.index')}}"> Incidencias </a>
            </li>


        </ul>
    </li>
    <li class="menu {{ Request::is('players*') ? 'active' : '' }}">
        <a href="{{route('players.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Deportistas</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('inscriptions*') ? 'active' : '' }}">
        <a href="{{route('inscriptions.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Inscripciones</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('payments*') ? 'active' : '' }}">
        <a href="{{route('payments.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Pagos</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('tournamentpayout*') ? 'active' : '' }}">
        <a href="{{route('tournamentpayout.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Pagos Torneos</span>
            </div>
        </a>
    </li>

    @endhasanyrole

    <li class="menu {{ Request::is('training-sessions*') ? 'active' : '' }}">
        <a href="{{route('training-sessions.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <span>S. Entrenamiento</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('assists*') ? 'active' : '' }}">
        <a href="{{route('assists.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Asistencias</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('matches*') ? 'active' : '' }}">
        <a href="{{route('matches.index')}}" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <span>C. Competencias</span>
            </div>
        </a>
    </li>

    <li class="menu {{ Request::is('reports*') ? 'active' : '' }}">

        <a href="#reports" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
            <div class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-hash">
                    <line x1="4" y1="9" x2="20" y2="9"></line>
                    <line x1="4" y1="15" x2="20" y2="15"></line>
                    <line x1="10" y1="3" x2="8" y2="21"></line>
                    <line x1="16" y1="3" x2="14" y2="21"></line>
                </svg>
                <span>Informes</span>
            </div>
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>
        <ul class="collapse submenu list-unstyled" id="reports" data-bs-parent="#accordionExample">
            <li>
                <a href="{{route('reports.payments')}}"> Pagos </a>
            </li>
        </ul>
    </li>
</ul>

@push('scripts')
@hasanyrole(['super-admin','school'])
@if(!empty($admin_schools))
<script>
    const isSchool = @json($isSchool);
    const text = isSchool === 1 ? 'sede' : 'escuela';
    const urlchooseSchool = "{{route('school.choose')}}";
    const schools = @json($admin_schools);

    function selectSchool() {
        swal({
            title: `Para seguir seleciona una ${text}`,
            type: "info",
            input: 'select',
            inputOptions: schools,
            inputPlaceholder: 'Selecciona...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: "Cancelar",
            inputValidator: function(value) {
                return new Promise(function(resolve) {
                    if (value !== '') {
                        resolve();
                    } else {
                        resolve(`Necesitas seleccionar una ${text}`);
                    }
                });
            }
        }).then(function(result) {
            if (result.value) {
                $.post(urlchooseSchool, {
                    'school_id': result.value
                }, function(data) {
                    setTimeout(location.reload(), 2000)
                });
            }
        });
    }
</script>
@endif
@endhasanyrole
@endpush