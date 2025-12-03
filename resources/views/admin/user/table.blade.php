<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="activos-tab" data-toggle="tab" href="#activos" role="tab"
                aria-controls="activos" aria-expanded="false">@lang('messages.inscription_actived')</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="inactivos-tab" data-toggle="tab" href="#inactivos" role="tab"
                aria-controls="inactivos" aria-expanded="false">@lang('messages.inscription_retired')</a>
        </li>

        <li class=" nav-item ml-auto">

            <a href="{!! route('users.create') !!}"
                class="float-right btn waves-effect waves-light btn-rounded btn-info">Crear Usuario</a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
        <div class="table-responsive-md">
            <table class="table display compact cell-border" id="user-table" style="width:100%">
                <thead>
                    @hasanyrole('super-admin')
                    <td>Escuela</td>
                    @endhasanyrole
                    <th>Usuarios</th>
                    <th>Rol</th>
                    <th>Correo</th>
                    <th colspan="">Acción</th>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        @hasanyrole('super-admin')
                        <td>{!! $user->school->name !!}</td>
                        @endhasanyrole
                        <td>{!! $user->name !!}</td>
                        <td>{!! $user->roles->implode('name',', ') !!}</td>
                        <td>{!! $user->email !!}</td>
                        <td>
                            {{html()->modelForm($user, 'delete', route('users.destroy', [$user->id]))->open()}}
                            <div class='btn-group'>
                                <a
                                    class="btn btn-warning btn-xs"
                                    href="{!! route('profiles.show', [$user->profile->id]) !!}">
                                    <i
                                        class="fas fa-user"
                                        aria-hidden="true"></i>
                                </a>
                                <a
                                    class="btn btn-info btn-xs"
                                    href="{!! route('users.edit', [$user->id]) !!}">
                                    <i
                                        class="fas fa-edit"
                                        aria-hidden="true"></i>
                                </a>
                                <button class="btn btn-danger btn-xs" type="submit" onclick="return confirm('Estas seguro?')">
                                    <i class="fas fa-user-times" aria-hidden="true"></i>
                                </button>

                            </div>
                            {{ html()->closeModelForm() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <div class="tab-pane" id="inactivos" role="tabpanel" aria-labelledby="inactivos-tab">
        <div class="table-responsive-md">
            <table class="table display compact cell-border" id="userTrash-table" style="width:100%">
                <thead>
                    @hasanyrole('super-admin')
                    <td>Escuela</td>
                    @endhasanyrole
                    <th>Usuarios</th>
                    <th>Rol</th>
                    <th>Correo</th>
                    <th colspan="">Acción</th>
                </thead>
                <tbody>
                    @foreach($usersTrash as $user)
                    <tr>
                        @hasanyrole('super-admin')
                        <td>{!! $user->school->name !!}</td>
                        @endhasanyrole
                        <td>{!! $user->name !!}</td>
                        <td>{!! $user->roles->implode('name',', ') !!}</td>
                        <td>{!! $user->email !!}</td>
                        <td>
                            {{html()->form('post', $user->url_activate)->open()}}
                            <div class='btn-group'>
                                <button class="btn btn-primary btn-xs" type="submit" onclick="return confirm('Estas seguro?')">
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                                </button>
                            </div>
                            {{ html()->form()->close() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>