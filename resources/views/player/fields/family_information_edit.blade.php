<h6>Información Familiar</h6>
<section>
    @foreach($peoples as $people)
        <fieldset class="col-md-12  p-2">
            <legend class="w-auto text-center">Familiar</legend>

            <div class="form-group ">
                <input type="checkbox"
                       name="people[{{$loop->index}}][tutor]"
                       id="tutor_{{$loop->index}}"
                       class="chk-col-blue" {{ $people->is_tutor ? 'checked':''}}
                       value="true">
                <label for="tutor_{{$loop->index}}">¿Es Acudiente?</label>
            </div>

            <div class="row">

                <div class="col-md-3">
                    <div class="form-group ">
                        {!! html()->label('Relación Familiar', "people[{$loop->index}][relationship]") !!}(<span class="text-danger">*</span>)
                        {{ html()->select("people[{$loop->index}][relationship]", $relationships, $people->relationship ?? null)->attributes(['class' => 'form-control form-control-sm','placeholder' => 'Seleccione uno...']) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! html()->label('Nombres Y Apellidos', "people[{$loop->index}][names]") !!}(<span class="text-danger">*</span>)
                        {!! html()->text("people[{$loop->index}][names]", $people->names ?? '')->attributes(['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! html()->label('Número De Cédula', "people[{$loop->index}][identification_card]") !!}
                        {!! html()->text("people[{$loop->index}][identification_card]", $people->identification_card ?? '')->attributes(['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! html()->label('Teléfonos/Celular', "people[{$loop->index}][phone]") !!}(<span class="text-danger">*</span>)
                        {!! html()->text("people[{$loop->index}][phone]", $people->phone ?? '')->attributes(['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>
            </div>

        </fieldset>
    @endforeach

</section>
