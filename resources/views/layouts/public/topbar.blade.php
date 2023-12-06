<header class="top-public">
    <div class="">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand" href="#">
                <img src="{{asset('img/logo-white.png')}}" alt="homepage" class="logo d-inline-block align-text-top">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
                <ul class="navbar-nav d-flex">
                    <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                        <a class="nav-link" href="/">Inicio <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item {{ Request::is('escuelas*') ? 'active' : '' }} dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                            Escuelas
                        </a>
                        <ul class="dropdown-menu">
                            @foreach($public_schools as $slug => $school)
                                <li><a class="dropdown-item" href="{{route('public.school.show', [$slug])}}">{{$school}}</a></li>
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
    </div>
</header>