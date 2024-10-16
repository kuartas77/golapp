<nav>
    <ul class="nav nav-tabs customtab" id="tab_inscriptions">
        <li class="nav-item">
            <a class="nav-link active show" id="enabled-tab" data-toggle="tab" href="#enabled" role="tab"
                aria-controls="enabled" aria-expanded="false">@lang('messages.title_enabled')</a>
        </li>

        <li class=" nav-item ml-auto">
            <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                href="javascript:void(0)" data-toggle="modal" data-target="#create" data-backdrop="static"
                data-keyboard="false" id="btn-add">
                <i class="fa fa-plus" aria-hidden="true"></i>
                @lang('messages.add_tournament')
            </a>
        </li>
    </ul>
</nav>
<div class="tab-content clearfix" id="tab_content">
    <div class="tab-pane show active" id="enabled" role="tabpanel" aria-labelledby="enabled-tab">
        <div class="table-responsive-md">
            <table class="display compact" id="active_table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

</div>