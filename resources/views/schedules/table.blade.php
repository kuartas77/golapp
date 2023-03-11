<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
               aria-controls="enabled" aria-expanded="false">@lang('messages.title_enabled')</a>
        </li>

        <li class=" nav-item ml-auto">
            @hasanyrole('super-admin|school')
                <a class="float-right btn waves-effect waves-light btn-rounded btn-info" id="btn-add"
                   href="javascript:void(0)" data-toggle="modal" data-target="#create" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    @lang('messages.schedule_add')
                </a>
            @endhasanyrole
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">

        <table class="display compact" id="active_table" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Horario</th>
                <th>Opciones</th>
            </tr>
            </thead>
        </table>

    </div>

</div>
