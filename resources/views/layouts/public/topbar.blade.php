<header class="topbar">
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">
            <img src="{{asset('img/logo-ext.jpg')}}" alt="homepage" class="dark-logo" width="148" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="navbarScroll">
            <ul class="navbar-nav mr-auto my-2 my-lg-0 navbar-nav-scroll">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a class="nav-link" href="/">Inicio <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item {{ Request::is('escuelas*') ? 'active' : '' }} dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                        Escuelas
                    </a>
                    <ul class="dropdown-menu">
                        @foreach($public_schools as $slug => $school)
                            <li><a class="dropdown-item" href="{{route('escuelas.show', [$slug])}}">{{$school}}</a></li>
                            @if (!$loop->last)
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            </ul>
            
        </div>
    </nav>
</header>