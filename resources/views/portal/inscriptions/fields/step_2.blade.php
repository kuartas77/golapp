<h6>Información general</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los Campos Con (<span class="text-danger">*</span>) Son Requeridos.</h6>
    <fieldset class="col-md-12 p-2">

        <div class="row">

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="address">Direccion de residencia (<span class="text-danger">*</span>)</label>
                    {{ html()->text('address', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="municipality">Municipio de residencia (<span class="text-danger">*</span>)</label>
                    {{ html()->text('municipality', null)->attributes(['class' => 'form-control', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="neighborhood">Barrio de residencia (<span class="text-danger">*</span>)</label>
                    {{ html()->text('neighborhood', null)->attributes(['class' => 'form-control', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-4 mb-3">
                <div class="form-group">
                <label for="rh">Grupo sanguíneo (<span class="text-danger">*</span>)</label>
                {{ html()->select('rh', $blood_types, null)->attributes(['class' => 'form-control'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="eps">EPS (<span class="text-danger">*</span>)</label>
                    {{ html()->text('eps', null)->attributes(['class' => 'form-control', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="student_insurance">Nombre del Seguro Estudiantil</label>
                    {{ html()->text('student_insurance', 'Sura')->attributes(['class' => 'form-control']) }}
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="school">Institución educativa </label>
                    {{ html()->text('school', null)->attributes(['class' => 'form-control', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                <label for="degree">Grado que cursa </label>
                {{ html()->select('degree', [0,1,2,3,4,5,6,7,8,9,10,11], null)->attributes(['class' => 'form-control select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    <label for="jornada">Jornada de estudio </label>
                    {{ html()->select('jornada', $jornada, null)->attributes(['class' => 'form-control select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

        </div>

    </fieldset>
</section>