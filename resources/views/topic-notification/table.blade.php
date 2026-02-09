<nav>
    <ul class="nav nav-tabs customtab" id="tab_notifications">
        <li class="nav-item">
            <a class="nav-link active show" id="active-tab" data-toggle="tab" href="#active" role="tab"
                aria-controls="active" aria-expanded="false">Notificaciones</a>
        </li>

        <li class=" nav-item ml-auto">
            <a class="float-left btn waves-effect waves-light btn-rounded btn-info"
                href="javascript:void(0)" data-toggle="modal" data-target="#notification_modal"
                data-backdrop="static" data-keyboard="false">
                <i class="fa fa-plus" aria-hidden="true"></i>
                Crear Notificación
            </a>

        </li>

    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">

    <div class="tab-pane show active" id="active" role="tabpanel" aria-labelledby="active-tab">
        <div class="table-responsive-md">
            <table class="display compact cell-border" id="notificationTable">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Topic</th>
                        <th>Titulo</th>
                        <th>Mensaje</th>
                        <th>Creada</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>