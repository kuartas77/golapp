<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nombre </label>
            <span class="bar"></span>
            <input type="text" name="name" id="name" class="form-control" required autocomplete="off" value="{{$school->name}}" readonly>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Correo </label>
            <span class="bar"></span>
            <input type="text" name="email" id="email" class="form-control" required autocomplete="off" value="{{$school->email}}" readonly>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="agent">Representante </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="agent" id="agent" class="form-control" required autocomplete="off" value="{{$school->agent}}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="address">Dirección </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="address" id="address" class="form-control" required autocomplete="off" value="{{$school->address}}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="phone">Teléfono </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="phone" id="phone" class="form-control" required autocomplete="off" value="{{$school->phone}}">
        </div>
    </div>
</div>
<br>
<h4>Configuraciones</h4>
<hr>
<div class="row">

    <div class="col-md-3">
        <div class="form-group">
            <label for="NOTIFY_PAYMENT_DAY">Día de notificación.</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="NOTIFY_PAYMENT_DAY" id="NOTIFY_PAYMENT_DAY" class="form-control notify_day" required autocomplete="off" value="{{$notify_payment_day}}">
            <small class="form-text text-muted">El día ingresado se enviarán las notificaciones de pagos en estado <span class="text-danger">Deben</span> por correo</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="INSCRIPTION_AMOUNT">Precio de la Inscripción.</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="INSCRIPTION_AMOUNT" id="INSCRIPTION_AMOUNT" class="form-control money" required autocomplete="off" value="{{$inscription_amount}}">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="MONTHLY_PAYMENT">Precio de la Mensualidad.</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="MONTHLY_PAYMENT" id="MONTHLY_PAYMENT" class="form-control money" required autocomplete="off" value="{{$monthly_payment}}">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="ANNUITY">Precio de la Anualidad </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="ANNUITY" id="ANNUITY" class="form-control money" required autocomplete="off" value="{{$annuity}}">
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Logo</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="logo">
                <label class="custom-file-label" for="file-upload">Seleccionar...</label>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <label>Logo</label>
        <img src="{{$school->logo_file}}" class="rounded" alt="player" id="player-img" width="200" height="200">
    </div>
    @if($school->inscriptions_enabled)
    <div class="col-md-3">
        <label>Enlace:</label>
        <a href="https://app.golapp.com.co/portal/{{$school->slug}}">Enlace Inscripciones</a>
        <br>
        <small class="text-muted">Si tiene la sesión iniciada el enlace no te sirve.</small>
    </div>
    @endif
</div>