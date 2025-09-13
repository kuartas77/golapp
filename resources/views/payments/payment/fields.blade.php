<fieldset class="col-md-12">
    <legend>Inscripción:</legend>
    <div class="row col-md-12">

        <div class="col-md-3">
            <div class="form-group">
                <img src="/img/user.png" class="rounded img-center mx-auto mb-1" alt="player" id="player_img" width="120" height="120">
            </div>
        </div>
        <div class="col-md-9">

            <div class="form-row align-items-center">
                <div class="form-group">
                    <label for="player_unique_code" class="font-weight-bold">Código único</label>
                    <input type="text" name="player_unique_code" class="form-control-plaintext" id="player_unique_code" readonly />
                </div>
                <div class="form-group">
                    <label for="player_document" class="font-weight-bold"># Documento</label>
                    <input type="text" name="player_document" class="form-control-plaintext" id="player_document" readonly />
                </div>
                <div class="form-group">
                    <label for="player" class="font-weight-bold">Deportista</label>
                    <input type="text" name="player" class="form-control-plaintext" id="player" readonly />
                </div>
            </div>

            <div class="form-group">
                <label for="january_amount" class="font-weight-bold">Inscripción</label>
                {{ html()->text('enrollment_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'enrollment_amount']) }}
                {!! html()->select('enrollment', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'enrollment'])->placeholder('Selecciona...') !!}
            </div>
        </div>
    </div>

</fieldset>

<fieldset class="col-md-12">
    <legend>Mensualidades:</legend>
    <div class="row col-md-12">

        <input type="hidden" id="payment_id" name="id">
        <div class="col-md-3">
            <div class="form-group">
                <label for="january_amount" class="font-weight-bold">Enero</label>
                {{ html()->text('january_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'january_amount']) }}
                {!! html()->select('january', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'january'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="february_amount" class="font-weight-bold">Febrero</label>
                {{ html()->text('february_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'february_amount']) }}
                {!! html()->select('february', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'february'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="march_amount" class="font-weight-bold">Marzo</label>
                {{ html()->text('march_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'march_amount']) }}
                {!! html()->select('march', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'march'])->placeholder('Selecciona...') !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="april_amount" class="font-weight-bold">Abril</label>
                {{ html()->text('april_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'april_amount']) }}
                {!! html()->select('april', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'april'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="may_amount" class="font-weight-bold">Mayo</label>
                {{ html()->text('may_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'may_amount']) }}
                {!! html()->select('may', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'may'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="june_amount" class="font-weight-bold">Junio</label>
                {{ html()->text('june_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'june_amount']) }}
                {!! html()->select('june', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'june'])->placeholder('Selecciona...') !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="july_amount" class="font-weight-bold">Julio</label>
                {{ html()->text('july_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'july_amount']) }}
                {!! html()->select('july', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'july'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="august_amount" class="font-weight-bold">Agosto</label>
                {{ html()->text('august_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'august_amount']) }}
                {!! html()->select('august', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'august'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="september_amount" class="font-weight-bold">Septiembre</label>
                {{ html()->text('september_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'september_amount']) }}
                {!! html()->select('september', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'september'])->placeholder('Selecciona...') !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="october_amount" class="font-weight-bold">Octubre</label>
                {{ html()->text('october_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'october_amount']) }}
                {!! html()->select('october', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'october'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="november_amount" class="font-weight-bold">Noviembre</label>
                {{ html()->text('november_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'november_amount']) }}
                {!! html()->select('november', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'november'])->placeholder('Selecciona...') !!}
            </div>
            <div class="form-group">
                <label for="december_amount" class="font-weight-bold">Diciembre</label>
                {{ html()->text('december_amount', null)->attributes(['class' => 'form-control form-control-sm payments_amount', 'id' => 'december_amount']) }}
                {!! html()->select('december', config('variables.KEY_PAYMENTS_SELECT'), '0')
                ->attributes(['class' => "form-control form-control-sm payments", 'id' => 'december'])->placeholder('Selecciona...') !!}
            </div>
        </div>

    </div>
</fieldset>