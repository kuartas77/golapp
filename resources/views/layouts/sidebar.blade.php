<aside class="left-sidebar">
    {{-- Sidebar scroll--}}
    <div class="scroll-sidebar">
        <!-- User profile -->

        {{-- <div class="user-profile" style="background: url({{asset('imagenes/user-info.jpg')}}) no-repeat;">
            <!-- User profile image -->
            <div class="profile-img" id="turno_contenedor_img"> 
                    <img src="{{asset('imagenes/login.png')}}" alt="user" height="90" title="Abrir Turno">
            </div>
            <!-- User profile text-->
            <div class="profile-text"> <a href="javascript:void(0)" id="turno_text">Abrir Turno</a> </div>
        </div> --}}

        <!-- End User profile text-->
        {{-- Sidebar navigation--}}
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                {{-- @include('plantillas.menu', ['menus'=>$menus, 'permissions_id'=>$permissions_id]) --}}
                @include('layouts.menu')
            </ul>
        </nav>
        {{-- End Sidebar navigation --}}
    </div>
    {{-- End Sidebar scroll--}}
    {{-- Bottom points--}}
    <div class="sidebar-footer">
        {{-- <a href="" class="link" data-toggle="tooltip" title="Settings"><i class="ti-settings"></i></a>
        <a href="" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a> --}}
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="link" data-toggle="tooltip" title="{{ __('Logout') }}"><i class="mdi mdi-power"></i></a>
    </div>
    {{-- End Bottom points--}}
</aside>