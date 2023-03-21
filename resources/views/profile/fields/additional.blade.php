<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12">

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active show" data-toggle="tab" href="#studies" role="tab">Estudios</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#references" role="tab">Referencias</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#contacts" role="tab">Contactos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#experience" role="tab">Experiencias</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#aptitude" role="tab">Aptitudes</a>
                </li>

                <li class="nav-item ml-auto card-actions">
                    <a class="nav-link" data-action="collapse" data-toggle="tooltip" data-placement="left"
                       title="Click acá"><i class="ti-plus"></i></a>
                </li>
            </ul>

        </div>
        <div class="card-body collapse show" style="">

            <div class="tab-content">
                <div class="tab-pane show active" id="studies" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <blockquote class="blockquote">
                                    <p class="mb-0">{!! $profile->studies !!}</p>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="references" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <blockquote class="blockquote">
                                    <p class="mb-0">{!! $profile->references !!}</p>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="contacts" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <blockquote class="blockquote">
                                    <p class="mb-0">{!! $profile->contacts !!}</p>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="experience" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <blockquote class="blockquote">
                                    <p class="mb-0">{!! $profile->experience !!}</p>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="aptitude" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <blockquote class="blockquote">
                                    <p class="mb-0">{!! $profile->aptitude !!}</p>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
