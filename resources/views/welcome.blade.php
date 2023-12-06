@extends('layouts.public.public')
@push('css')
@endpush
@section('content')

<section class="container-fluid main-section">
    <div class="main-info">
        <h1>TU MEJOR ALIADO PARA LA ADMINISTRACIÓN Y CONTROL DEPORTIVO DE TU CLUB Ó ESCUELA DE FÚTBOL.</h1>
        <p>En nuestro sistema podrás encontrar diferentes soluciones para mejorar el funcionamiento de todos los procesos internos.</p>
        <ul class="">
            <li class="">Olvidate del Excel.</li>
            <li class="">Los instructores podrán asistencias a los entrenamientos.</li>
            <li class="">Recordatorios de pagos de la mensualidad.</li>
            <li class="">Pagos de torneos.</li>
            <li class="">Creación de grupos de entrenamiento y competencia.</li>
            <li class="">Historicos de pagos, asistencias, competencias por año.</li>
            <li class="">Informes detallados del deportista, trimestral, semestral, anual.</li>
        </ul>
    </div>
    <div class="ball-container">
        <img src="{{asset('img/ball-shadow.png')}}" alt="ball">
    </div>
</section>

<section class="container-fluid ">
    <div class="modules-title">
        <h1 id="our-modules">Nuestros módulos</h1>
        <div class="divider"></div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-6 col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Grupos de entrenamiento</h1>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam assumenda at beatae cumque dolorum eum fuga
                        fugiat harum illum iusto molestiae nisi, pariatur porro reiciendis repellat repellendus sunt temporibus
                        vitae!</p>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Listado de deportistas</h1>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam assumenda at beatae cumque dolorum eum fuga
                        fugiat harum illum iusto molestiae nisi, pariatur porro reiciendis repellat repellendus sunt temporibus
                        vitae!</p>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Listado de inscripciones</h1>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam assumenda at beatae cumque dolorum eum fuga
                        fugiat harum illum iusto molestiae nisi, pariatur porro reiciendis repellat repellendus sunt temporibus
                        vitae!</p>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6 col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Toma de asistencia</h1>
                    <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam assumenda at beatae cumque dolorum eum fuga
                        fugiat harum illum iusto molestiae nisi, pariatur porro reiciendis repellat repellendus sunt temporibus
                        vitae!</p>
                </div>
            </div>
        </div>
    </div>
</section>


<footer class="">
    <div class="col-12">
        <div class="title">Contacto
            <div class="divider"></div>
        </div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eaque, fugiat, labore. Dolorem doloribus enim
        exercitationem expedita illum nulla odit officiis quod, rerum similique temporibus totam voluptatibus
        voluptatum! Accusantium doloremque, quibusdam.
    </div>
    <div class="col-12">
        <div class="title">Redes cociales
            <div class="divider"></div>
        </div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto aspernatur, at, blanditiis corporis
        distinctio doloribus eum excepturi fuga fugit ipsa iste libero nostrum perspiciatis provident quibusdam quos
        tempora voluptas voluptates.
    </div>
    <div class="col-12">
        <div class="title">Sobre nosotros
            <div class="divider"></div>
        </div>
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus aspernatur aut autem cumque debitis dolor
        eligendi incidunt iusto nesciunt, nostrum odio quo, reprehenderit rerum saepe soluta totam velit voluptate
        voluptatem.
    </div>
</footer>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>

    </script>
@endsection
