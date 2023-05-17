<section>
    <fieldset class="col-md-12  p-2">
        <legend class="w-auto text-center">Familiar</legend>

        <div class="form-group ">
            <input type="checkbox"
                    name="people[0][tutor]"
                    id="tutor_0"
                    class="chk-col-blue" checked value="true">
            <label for="tutor_0">¿Es Acudiente?</label>
        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group ">
                    {!! Form::label("people[0][relationship]", 'Relación Familiar') !!}(<span class="text-danger">*</span>)
                    {!! Form::select("people[0][relationship]", $relationships, null,
                    ['class' => 'form-control form-control-sm','placeholder' => 'Seleccione uno...']) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label("people[0][names]", 'Nombres Y Apellidos') !!}(<span class="text-danger">*</span>)
                    {!! Form::text("people[0][names]", null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label("people[0][identification_card]", 'Número De Cédula') !!}(<span class="text-danger">*</span>)
                    {!! Form::text("people[0][identification_card]", null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label("people[0][phone]", 'Teléfonos/Celular') !!}
                    {!! Form::text("people[0][phone]", null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>
        </div>

    </fieldset>

</section>
