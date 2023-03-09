<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{route('home')}}">
                <!-- Logo icon -->
                <b>
                    <!-- Dark Logo icon -->
                    <img src="{{asset('img/ballon.png')}}" alt="homepage" class="dark-logo" width="34" height="33">
                    <!-- Light Logo icon -->
                    <img src="{{asset('img/ballon.png')}}" alt="homepage" class="light-logo" width="34" height="33">
                </b>
                <!--End Logo icon -->
                <!-- Logo text -->
                <span style="display: none;">
                    <!-- dark Logo text -->
                    <img src="{{asset('img/logo-ext.jpg')}}" alt="homepage" class="dark-logo">
                    <!-- Light Logo text -->
                    <img src="{{asset('img/logo-ext.jpg')}}" class="light-logo" alt="homepage">
                </span>
            </a>

        </div>
        {{-- End Logo --}}
        <div class="navbar-collapse">
            {{-- toggle and nav items --}}
            <ul class="navbar-nav mr-auto mt-md-0">
                <!-- This is  -->
                <li class="nav-item">
                    <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark"
                       href="javascript:void(0)"><i class="mdi mdi-menu"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark"
                       href="javascript:void(0)"><i class="ti-menu"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav my-lg-0">

                {{--                @include( 'plantillas.notification')--}}
                {{-- Profile --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
                       data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false"><img src="{{asset('img/user_login.png')}}" alt="user"
                                                  class="profile-pic"></a>
                    <div class="dropdown-menu dropdown-menu-right scale-up">
                        <ul class="dropdown-user">
                            <li>
                                <div class="dw-user-box">
                                    <div class="u-img">
                                        <img src="{{asset('img/user_login.png')}}" alt="user">
                                    </div>
                                    <div class="u-text">
                                        <h4>{{auth()->user()->name}}</h4>
                                    </div>
                                </div>
                            </li>
                            <li role="separator" class="divider"></li>
                            @if(auth()->user()->profile)
                            <li><a href="{{route('profiles.show', [auth()->user()->profile->id])}}"><i class="fas fa-user"></i> {{ __('messages.Profile') }}</a></li>
                            <li role="separator" class="divider"></li>
                            @endif
                            <li><a href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="fa fa-power-off"></i> {{ __('Logout') }}</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
