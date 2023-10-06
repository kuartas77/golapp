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
                    {!! html()->label('Relación Familiar', "people[0][relationship]") !!}(<span class="text-danger">*</span>)
                    {{ html()->select("people[0][relationship]", $relationships, null)->attributes(['class' => 'form-control form-control-sm','placeholder' => 'Seleccione uno...']) }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! html()->label('Nombres Y Apellidos', "people[0][names]") !!}(<span class="text-danger">*</span>)
                    {!! html()->text("people[0][names]", null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! html()->label('Número De Cédula', "people[0][identification_card]") !!}(<span class="text-danger">*</span>)
                    {!! html()->text("people[0][identification_card]", null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! html()->label('Teléfonos/Celular', "people[0][phone]") !!}
                    {!! html()->text("people[0][phone]", null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>
        </div>

    </fieldset>

</section>
