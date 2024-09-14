<h6>Información Familiar</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los Campos con (<span class="text-danger">*</span>) son requeridos.</h6>
    <h6 class="row block-helper justify-content-center "><strong>Sí el acudiente es el padre o la madre no es necesario ingresar la información.</strong></h6>
    @foreach($peoples as $people)
    @if((is_numeric($people) && $people === 1) || data_get($people, 'tutor', false))

    <fieldset class="col-md-12">
        <legend>Acudiente:</legend>

        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('Nombres completos', "people[{$loop->index}][names]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][names]", ($people->names ?? null))->attributes(['class' => 'form-control']) }}
                    <input type="hidden" name="people[{{$loop->index}}][tutor]" value="true" />
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('# Documento de identidad', "people[{$loop->index}][identification_card]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][identification_card]", ($people->identification_card ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('Parentesco', "people[{$loop->index}][relationship]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->select("people[{$loop->index}][relationship]", $relationships, ($people->relationship ?? null))->attributes(['class' => 'form-control'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('# WhatsApp ó télefono', "people[{$loop->index}][phone]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][phone]", ($people->phone ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>

        </div>

        <!-- <div class="row">

            <div class="col-md-4 mb-3">
                <div class="form-group">
                    {!! html()->label('Empresa donde labora', "people[{$loop->index}][business]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][business]", ($people->business ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-group">
                    {!! html()->label('Cargo que desempeña', "people[{$loop->index}][profession]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][profession]", ($people->profession ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-group">
                    {!! html()->label('Correo electrónico', "people[{$loop->index}][email]") !!}(<span class="text-danger">*</span>)</label>
                    {{ html()->text("people[{$loop->index}][email]", ($people->email ?? null))->attributes(['class' => 'form-control']) }}
                    <small class="form-text text-muted">Correo Electrónico para enviar notificaciónes.</small>
                </div>
            </div>

        </div> -->

    </fieldset>
    @else
    <fieldset class="col-md-12 p-2">
        <legend>Parentesco : {{ html()->select("people[{$loop->index}][relationship]", $relationships, null)->attributes(['class' => ''])->placeholder('Selecciona...') }}</legend>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('Nombres completos', "people[{$loop->index}][names]") !!}
                    {{ html()->text("people[{$loop->index}][names]", ($people->names ?? null))->attributes(['class' => 'form-control']) }}
                    <input type="hidden" name="people[{{$loop->index}}][tutor]" value="false" />
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('# Doc de identidad', "people[{$loop->index}][identification_card]") !!}
                    {{ html()->text("people[{$loop->index}][identification_card]", ($people->identification_card ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('# WhatsApp ó télefono', "people[{$loop->index}][phone]") !!}
                    {{ html()->text("people[{$loop->index}][phone]", ($people->phone ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="form-group">
                    {!! html()->label('Ocupación', "people[{$loop->index}][business]") !!}
                    {{ html()->text("people[{$loop->index}][business]", ($people->business ?? null))->attributes(['class' => 'form-control']) }}
                </div>
            </div>

        </div>
    </fieldset>
    @endif
    @endforeach
</section>