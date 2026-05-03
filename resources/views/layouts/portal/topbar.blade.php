<header class="topbar">
	@php
		$portalGuardian = auth('guardians')->user();
		$portalUser = $portalGuardian ?: auth('players')->user();
		$portalHomeUrl = $portalGuardian ? route('portal.guardians.home') : route('portal.school.index');
		$portalUserPhoto = data_get($portalUser, 'photo_url_public', asset('img/user.png'));
		$portalUserName = data_get($portalUser, 'full_names', data_get($portalUser, 'names', 'Acudiente'));
	@endphp
	<nav class="navbar top-navbar navbar-expand-md navbar-light">
		<div class="navbar-header">
			<a class="navbar-brand" href="{{$portalHomeUrl}}">
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

				@guest('guardians')
				<li class="nav-item">
					<a class="nav-link waves-effect waves-ligth text-themecolor" href="{{route('login')}}">Ingreso Escuela</a>
				</li>
				<li class="nav-item">
					<a class="nav-link waves-effect waves-ligth text-themecolor" href="{{route('portal.login.form')}}">Ingreso Acudiente</a>
				</li>
				@endguest

			</ul>
			@auth('guardians')
			<ul class="navbar-nav my-lg-0">

				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
						data-toggle="dropdown" aria-haspopup="true"
						aria-expanded="false"><img src="https://ui-avatars.com/api/?name={{ $portalUserName }}" alt="user"
							class="profile-pic"></a>
					<div class="dropdown-menu dropdown-menu-right scale-up">
						<ul class="dropdown-user">
							<li>
								<div class="dw-user-box">
									<div class="u-img">
										<img src="https://ui-avatars.com/api/?name={{ $portalUserName }}" class="profile-pic" alt="user">
									</div>
									<div class="u-text">
										<h4>{{ $portalUserName }}</h4>
									</div>
								</div>
							</li>
							<li role="separator" class="divider"></li>

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
