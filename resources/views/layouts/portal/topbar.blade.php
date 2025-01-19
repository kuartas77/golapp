<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-light">
		<div class="navbar-header">
			<a class="navbar-brand" href="{{route('portal.school.index')}}">
				<!-- Logo icon -->
				<b>
					<img src="{{asset('img/light.png')}}" alt="homepage" class="dark-logo" width="148" height="33">
					@if(isset($school) && !Request::routeIs('portal.school.*'))
					{{$school->name}}
					@endif
				</b>
				<!--End Logo icon -->
			</a>
		</div>

		<div class="navbar-collapse">

			<ul class="navbar-nav mr-auto mt-md-0">

				<li class="nav-item">
					<a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark"
						href="javascript:void(0)"></a>
				</li>
				<li class="nav-item">
					<a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark"
						href="javascript:void(0)"></a>
				</li>

				@guest
				<li class="nav-item">
					<a class="nav-link waves-effect waves-ligth text-themecolor" href="{{route('login')}}">Ingreso Escuela</a>
				</li>
				<li class="nav-item">
					<a class="nav-link waves-effect waves-ligth text-themecolor" href="{{route('portal.login.form')}}">Ingreso Acudiente/Deportista</a>
				</li>
				@endguest

			</ul>
			@auth
			<ul class="navbar-nav my-lg-0">

				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
						data-toggle="dropdown" aria-haspopup="true"
						aria-expanded="false"><img src="{{ auth()->user()->photo_url_public }}" alt="user"
							class="profile-pic"></a>
					<div class="dropdown-menu dropdown-menu-right scale-up">
						<ul class="dropdown-user">
							<li>
								<div class="dw-user-box">
									<div class="u-img">
										<img src="{{ auth()->user()->photo_url_public }}" class="profile-pic" alt="user">
									</div>
									<div class="u-text">
										<h4>{{auth()->user()->full_names}}</h4>
									</div>
								</div>
							</li>
							<li role="separator" class="divider"></li>
							@if(auth()->user()->profile)
							<li><a href="{{route('profiles.show', [auth()->user()->profile->id])}}"><i class="fas fa-user"></i> {{ __('messages.Profile') }}</a></li>
							<li role="separator" class="divider"></li>
							@endif

							<li>
								<a href="{{ route('portal.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
									<i class="fa fa-power-off"></i>
									{{ __('messages.Logout') }}
								</a>
								<form id="logout-form" action="{{ route('portal.logout') }}" method="POST" style="display: none;">
									@csrf
								</form>
							</li>
						</ul>
					</div>
				</li>
			</ul>
			@endauth
		</div>
	</nav>
</header>