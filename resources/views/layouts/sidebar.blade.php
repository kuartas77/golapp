<div class="sidebar-wrapper sidebar-theme">

    <nav id="sidebar">

        <div class="navbar-nav theme-brand flex-row  text-center">
            <div class="nav-logo">
                <div class="nav-item theme-logo">
                    <a href="{{route('home')}}">
                        <img src="{{asset('img/ballon_dark.png')}}" alt="logo" class="navbar-logo" width="34" height="33">
                    </a>
                </div>
                <div class="nav-item theme-text">
                    <a href="{{route('home')}}" class="nav-link">
                        <img src="{{asset('img/dark.png')}}" class="logo-text" alt="homepage" width="148" height="33">
                    </a>

                </div>
            </div>
            <div class="nav-item sidebar-toggle">
                <div class="btn-toggle sidebarCollapse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevrons-left">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        @include('layouts.menu')
    </nav>
</div>