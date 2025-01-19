<div class="modal" id="modal_inscription">
    <div class="modal-dialog modal-xl mw-80 w-100">
        <div class="wizard-content">
            <div class="modal-content">
                <div class="modal-body">
                    {{html()->form('post', route('portal.school.inscription.store', [$school->slug]))->attributes(['id' => 'form_inscripcion', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class' => 'validation-wizard wizard-circle'])->open()}}
                    {!! RecaptchaV3::field('inscriptions') !!}
                    @include('portal.inscriptions.fields.step_1')
                    @include('portal.inscriptions.fields.step_2')
                    @include('portal.inscriptions.fields.step_3')
                    @include('portal.inscriptions.fields.step_4')
                    @include('portal.inscriptions.fields.step_5')
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{asset('js/signature_pad.umd.min.js')}}"></script>
<script>
    const school = @json($school);
    const url_autocomplete = "{{ route('portal.autocomplete.fields') }}";
    const url_search = "{{ route('portal.autocomplete.search_doc') }}";
    const form_inscripcion = $("#form_inscripcion");


    form_inscripcion.validate({
        rules: {
            names : {required: true, maxlength:50},
            last_names : {required: true, maxlength:50},
            date_birth : {required: true},
            place_birth : {required: true, maxlength:50},
            identification_document : {required: true, maxlength:50, numbers: true},
            document_type : {required: true},
            gender : {required: true},
            email : {required: true, emails:true},
            mobile : {required: true, maxlength:50},
            medical_history : {required: false, maxlength:200},

            address: {required: true, maxlength:50},
            municipality: {required: true, maxlength:50},
            neighborhood: {required: true, maxlength:50},
            rh: {required: true},
            eps: {required: true, maxlength:50},
            student_insurance: { maxlength:50},
            school: {required: false, maxlength:50},
            degree: {required: (element) => $("#school").val().length > 0 },
            jornada: {required: (element) => $("#school").val().length > 0},

            tutor_name: {required: true, maxlength:50},
            tutor_doc: {required: true, maxlength:50},
            tutor_relationship: {required: true},
            tutor_phone: {required: true, maxlength:50},
            tutor_work: {required: true, maxlength:50},
            tutor_position_held: {required: true, maxlength:50},
            tutor_email: {required: true, emails:true},

            dad_name: {required: false, maxlength:50},
            dad_doc: {required: function(element){
                return $("#dad_name").val().length > 0;
            }, maxlength:50},
            dad_phone: {required: function(element){
                return $("#dad_name").val().length > 0;
            }, maxlength:50},
            dad_work: {required: function(element){
                return $("#dad_name").val().length > 0;
            }, maxlength:50},

            mom_name: {required: false, maxlength:50},
            mom_doc: {required: function(element){
                return $("#mom_name").val().length > 0;
            }, maxlength:50},
            mom_phone: {required: function(element){
                return $("#mom_name").val().length > 0;
            }, maxlength:50},
            mom_work: {required: function(element){
                return $("#mom_name").val().length > 0;
            }, maxlength:50},

            photo: {required: true, extension: "png|jpeg|jpg", filesize: 2},
            player_document: {required: true, extension: "png|jpeg|jpg|pdf", filesize: 2},
            medical_certificate: {required: true, extension: "png|jpeg|jpg|pdf", filesize: 2},
            tutor_document: {required: true, extension: "png|jpeg|jpg|pdf", filesize: 2},
            payment_receipt: {required: true, extension: "png|jpeg|jpg|pdf", filesize: 2},

            contrato_aff: {required: true},
            contrato_insc: {required: true},
        },
    })

    form_inscripcion.steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "fade",
        stepsOrientation: "horizontal",
        titleTemplate: '<span class="step">#index#</span> #title#',
        autoFocus: true,
        enableAllSteps: true,
        enableCancelButton: true,
        labels: {
            finish: "Guardar",
            next: "Siguiente",
            previous: "Anterior",
            cancel: "Cancelar Y Borrar Formulario"
        },
        onInit: function(event, currentIndex){
            $('.date').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');
            events()
            $("#date_birth").bootstrapMaterialDatePicker({
                time: false,
                clearButton: false,
                lang: 'es',
                cancelText: 'Cancelar',
                okText: 'Aceptar',
                minDate: moment().subtract(18, 'year'),//TODO: settings
                maxDate: moment().subtract(2, 'year')// TODO: settings
            });
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            if(currentIndex == 3 && (signaturePadTutor.isEmpty() || signaturePadAlumno.isEmpty())){
                Swal.fire({
                    title: window.app_name,
                    text: 'Ingresa las firmas para poder continuar',
                    type: 'warning',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                })
                return false
            }

            return currentIndex > newIndex || (currentIndex < newIndex &&
                (form_inscripcion.find(".body:eq(" + newIndex + ") label.error").remove(),
                    form_inscripcion.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
                form_inscripcion.validate().settings.ignore = ":disabled,:hidden", form_inscripcion.valid())
        },
        onFinishing: function(event, currentIndex) {
            return form_inscripcion.validate().settings.ignore = ":disabled", form_inscripcion.valid()
        },
        onFinished: function(event, currentIndex) {

            Swal.fire({
                title: window.app_name,
                text: '¿Deseas envíar el formulario y crear una inscripción?',
                type: 'warning',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result?.value !== undefined) {
                    sendData();
                }
            })
        },
        onCanceled: function (event) {
            Swal.fire({
                title: '¡Atención!',
                text: "Está acción borrará la información agregada en el formulario ¿Deseas proceder?",
                type: 'warning',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result?.value !== undefined) {
                    form_inscripcion.resetForm()
                    form_inscripcion.clearForm()
                    document.getElementById("form_inscripcion").reset();
                    localStorage.removeItem('form-storage')
                    $('#modal_inscription').modal('hide');
                }
            })

        }
    });

    const firma_tutor = document.getElementById("firma_tutor");
    const firma_alumno = document.getElementById("firma_alumno");

    const signaturePadTutor = new SignaturePad(firma_tutor);
    const signaturePadAlumno = new SignaturePad(firma_alumno);

    function sendData(){
        let data = new FormData();
        let form_data = $(form_inscripcion).serializeArray();
        let url = $(form_inscripcion).attr('action');

        $.each(form_data, function (key, input) {
            data.append(input.name, input.value);
        });

        $.each($("input[type=file]"), function(i, input) {
            $.each(input.files,function(j, file){
                data.append(input.name, file);
            })
        })

        var signatureTutor = firma_tutor.toDataURL("image/png");
        var signatureAlumno = firma_alumno.toDataURL("image/png");
        data.append("signatureTutor", signatureTutor);
        data.append("signatureAlumno", signatureAlumno);

        $.ajax({
            url: url,
            method: "post",
            processData: false,
            contentType: false,
            data: data,
            success: function (data) {
                Swal.fire({
                    type: 'success',
                    title: window.app_name,
                    text: 'Se ha creado la inscripción correctamente, se enviará al correo de notificación el contrato y el código del deportista.',
                }).then(okay => {
                    if (okay) {
                        $('#modal_inscription').modal('hide');
                        document.getElementById("form_inscripcion").reset();
                        localStorage.removeItem('form-storage')
                        window.location.reload()
                    }
                })

            },
            error: function (e) {
                Swal.fire({
                    type: 'error',
                    title: window.app_name,
                    text: 'Algo salío mal, no hemos podido procesar la información en este momento, por favor intenta de nuevo más tarde!',
                })
            }
        });
    }


    $('#identification_document').on('keyup', function(){
        let documentInput = $("#identification_document");
        let documentVal = documentInput.val().toLowerCase().trim()
        if(documentVal.length >= 8 && documentInput.valid()){
            $.get(url_search, {doc: documentVal, school_id: school.id}, function (result) {
                let info = result.data
                if(info?.names){
                    $('#names').val(info.names)
                    $('#last_names').val(info.last_names)
                    $('#date_birth').val(info.date_birth)
                    $('#place_birth').val(info.place_birth)
                    $('#document_type').val(info.document_type)
                    $('#gender').val(info.gender)
                    $('#email').val(info.email.toLowerCase())
                    $('#mobile').val(info.mobile)
                    $('#medical_history').val(info.medical_history)
                    $('#address').val(info.address)
                    $('#municipality').val(info.municipality)
                    $('#neighborhood').val(info.neighborhood)
                    $('#rh').val(info.rh)
                    $('#eps').val(info.eps)
                    $('#student_insurance').val(info.student_insurance)
                    $('#school').val(info.school)
                    $('#degree').val(info.degree)
                    $('#jornada').val(info.jornada)
                }
            })
        }
    });

    $('#email').on('change', function(){
        let inputEmail = $("#email");
        let email = inputEmail.val().toLowerCase().trim()
        inputEmail.val(email)
        $('#tutor_email').val(email)
    });

    function events() {
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
        let campos = ['school', 'place_birth', 'neighborhood', 'eps', 'commune'];
        $.get(url_autocomplete, {fields: campos}, function (result) {
            $('#place_birth').typeahead({
                source: result.place_birth,
                scrollBar: true,
                appendTo: "#modal_inscription"
            });

            $('#school').typeahead({
                source: result.school,
                scrollBar: true,
                appendTo: "#modal_inscription"
            });

            $('#municipality').typeahead({
                source: result.place_birth,
                scrollBar: true,
                appendTo: "#modal_inscription"
            });

            $('#neighborhood').typeahead({
                source: result.neighborhood,
                scrollBar: true,
                appendTo: "#modal_inscription"
            });

            $('#eps').typeahead({
                source: result.eps,
                scrollBar: true,
                appendTo: "#modal_inscription"
            });
        });
    }

    const objectFromAttributes = function(elem, attributes = []) {
        const clone = {}

        for (const attribute of attributes) {
            const value = elem[attribute]
            // attributes with false and undefined values are ignored
            if (value ?? false) {
                clone[attribute] = value
            }
        }
        return clone
    }

    const deMethodize = (fn) => (arg0, ...args) => fn.apply(arg0, args)

    // will work on array like objects e.g. HTMLCollections
    const flatMap = deMethodize(Array.prototype.flatMap)

    // check for empty objects
    const notEmpty = (obj) => Object.keys(obj).length

    window.addEventListener('DOMContentLoaded', () => {

        // Extracts user filled inputs and copies those entries to an Array
        // @param(HTMLElement) form
        // @param(Array) attributes = Array of attribute names e.g. ['value' , 'checked']
        // @param(Array) exclude = Array of types to exclude e.g. ['[type=password]', ...]
        // @returns(Array) An array of user inputs e.g. [[0, {value:'John'}], [3, {checked:true}]]
        const filterFormElements = function(form, {
            attributes = [],
            exclude = []
        }) {
            if (!attributes.length) return []

            // create selector e.g. 'input:not([type=password],[type=hidden])'
            const selectedInputs = `input:not(${exclude.join(',')})`

            // using flatMap to filter selected inputs
            return flatMap(form, (elem, i) => {

                if (elem.matches(selectedInputs) || elem.matches('select') || elem.matches('textarea')) {
                    const inputs = objectFromAttributes(elem, attributes)
                    // ignore empty inputs
                    if (notEmpty(inputs)) return [
                        [i, inputs]
                    ]
                }

                return []
            })
        }

        const getFormData = function() {
            return JSON.parse(localStorage.getItem('form-storage')) ?? []
        }

        const deleteFormData = function() {
            localStorage.removeItem('form-storage')
        }

        const storeFormData = function(formData = []) {
            localStorage.setItem('form-storage', JSON.stringify(formData))
        }

        // Populate form with form data from localStorage
        // @param(HTMLElement) form
        // @param(Array) formData
        const populateForm = function(form, formData) {
            formData.forEach(([i, attributes]) => {
                const formElement = form[i]

                for (const key in attributes) {
                    formElement[key] = attributes[key]
                }
            })
        }

        const form = document.querySelector('#form_inscripcion')

        form.addEventListener('change', (event) => {
            const form = event.currentTarget
            const formData = filterFormElements(
                form, {
                    // input attributes to store
                    attributes: ['value', 'checked'],
                    // input types to ignore
                    exclude: ['[type=password]', '[type=hidden]', '[type=file]']
                }
            )

            storeFormData(formData)
        })

        // clear form-storage on submitting
        form.addEventListener('submit', deleteFormData)

        // clear form-storage on reset
        form.addEventListener('reset', deleteFormData)

        // populate form with stored data
        populateForm(form, getFormData())
    })
</script>
@endpush
@push('css')
{!! RecaptchaV3::initJs() !!}
@endpush