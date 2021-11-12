<h6>Información Familiar</h6>
<section>
    @foreach($player->peoples as $people)
        <fieldset class="col-md-12  p-2">
            <legend class="w-auto text-center">Familiar</legend>

            <div class="form-group ">
                <input type="checkbox"
                       name="people[{{$loop->index}}][tutor]"
                       id="tutor_{{$loop->index}}"
                       class="chk-col-blue" {{$people->is_tutor? 'checked':''}}
                       value="true">
                <label for="tutor_{{$loop->index}}">¿Es Acudiente?</label>
            </div>

            <div class="row">

                <div class="col-md-3">
                    <div class="form-group ">
                        {!! Form::label("people[{$loop->index}][relationship]", 'Relación Familiar') !!}<span class="text-danger">*</span>
                        {!! Form::select("people[{$loop->index}][relationship]", $relationships, $people->relationship,
                        ['class' => 'form-control form-control-sm','placeholder' => 'Seleccione uno...']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][names]", 'Nombres Y Apellidos') !!}<span class="text-danger">*</span>
                        {!! Form::text("people[{$loop->index}][names]", $people->names, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][identification_card]", 'Número De Cédula') !!}
                        {!! Form::text("people[{$loop->index}][identification_card]", $people->identification_card, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][phone]", 'Teléfonos') !!}<span class="text-danger">*</span>
                        {!! Form::text("people[{$loop->index}][phone]", $people->phone, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][mobile]", 'Número Celular') !!}<span class="text-danger">*</span>
                        {!! Form::text("people[{$loop->index}][mobile]", $people->mobile, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][profession]", 'Profesión') !!}
                        {!! Form::text("people[{$loop->index}][profession]", $people->profession, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][business]", 'Empresa') !!}
                        {!! Form::text("people[{$loop->index}][business]", $people->business, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label("people[{$loop->index}][position]", 'Cargo En La Empresa') !!}
                        {!! Form::text("people[{$loop->index}][position]", $people->position, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

            </div>

        </fieldset>
    @endforeach

</section>
