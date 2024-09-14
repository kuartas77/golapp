<div class="modal" id="modal_training_sessions">
    <div class="modal-dialog modal-xl mw-80 w-100">
        <div class="wizard-content">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Crear una sessión de entrenamiento.</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    {{html()->form('post', route('training-sessions.store'))->attributes(['id' => 'form_session', 'class' => 'form-material m-t-0 validation-wizard wizard-circle'])->open()}}
                    @include('training_sessions.fields.header')

                    @foreach($numberTasks as $task)

                    @include('training_sessions.fields.task', ['task' => $task])

                    @endforeach

                    @include('training_sessions.fields.footer')
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
</div>