<h5 class="">Informacion de la Escuela</h5>
<div class="row">

    
    <div class="col-md-4">
        <div class="form-group">
            <label for="name">Nombre </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="name" id="name" class="form-control" required autocomplete="off">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="address">Dirección </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="address" id="address" class="form-control" required autocomplete="off">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="phone">Teléfono </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="phone" id="phone" class="form-control" required autocomplete="off">
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-4">
        <div class="form-group">
            <label for="is_enable">Estado </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <select name="is_enable" id="is_enable" class="form-control">
                <option value="1">Activo</option>
                <option value="0" selected>Inactivo</option>
            </select>
        </div>
    </div>

</div>

<h5 class="">Informacion del Usuario Administrador</h5>
<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            <label for="agent">Representante </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="agent" id="agent" class="form-control" required autocomplete="off">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Correo </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="text" name="email" id="email" class="form-control" required autocomplete="off">
        </div>
    </div>

    <div class="col-md-6" id="password_div">
        <div class="form-group">
            <label for="agent">Contraseña </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="password" name="password" id="password" class="form-control" required autocomplete="off">
        </div>
    </div>

    <div class="col-md-6" id="password_confirmation_div">
        <div class="form-group">
            <label for="agent">Confirmación Contraseña </label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="off">
        </div>
    </div>    

</div>
<div class="row">

    <div class="col-md-8">
        <div class="form-group">
            <label>Logo</label> 
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="logo">
                <label class="custom-file-label" for="file-upload">Seleccionar...</label>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <label>Logo</label>
        <img src="https://golapp.softdreamc.com/img/ballon.png" class="rounded" alt="player" id="player-img" width="200" height="200">
    </div>

</div>