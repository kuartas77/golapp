<aside class="left-sidebar">
    {{-- Sidebar scroll--}}
    <div class="scroll-sidebar">
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
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="link"><i class="mdi mdi-power"></i></a>
    </div>
    {{-- End Bottom points--}}
</aside>