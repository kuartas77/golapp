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
					<a class="nav-link waves-effect waves-ligth" href="{{route('login')}}">Ingreso Escuela</a>
				</li>
				<!-- <li class="nav-item">
					<a class="nav-link waves-effect waves-ligth {{ Request::is('escuelas') ? 'active' : '' }}" href="{{route('portal.school.index')}}">Escuelas</a>
				</li> -->
				<!-- <li class="nav-item {{ Request::is('*/ingreso') ? 'active' : '' }}">
					<a class="nav-link waves-effect waves-ligth" href="{{route('portal.login.form')}}">Ingreso Acudiente</a>
				</li> -->
				<li class="nav-item dropdown {{ Request::is('*/escuelas/*') ? 'active' : '' }}">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
						Escuelas
					</a>
					<ul class="dropdown-menu">
						@foreach($public_schools as $slug => $school)
							<li><a class="dropdown-item" href="{{route('portal.school.show', [$slug])}}">{{$school}}</a></li>
							@if (!$loop->last)
							<li>
								<hr class="dropdown-divider">
							</li>
							@endif
						@endforeach
					</ul>
				</li>
				@endguest
			@auth
			<li class="nav-item {{ Request::is('*/jugador') ? 'active' : '' }}">
				<a class="nav-link waves-effect waves-ligth" href="{{route('portal.player.home')}}">Inicio <span class="sr-only">(current)</span></a>
			</li>
			<!-- menu -->
			@endauth
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
								<a href="{{ route('public.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
									<i class="fa fa-power-off"></i>
									{{ __('messages.Logout') }}
								</a>
								<form id="logout-form" action="{{ route('public.logout') }}" method="POST" style="display: none;">
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