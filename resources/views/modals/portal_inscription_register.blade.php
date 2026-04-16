<div class="modal" id="modal_inscription">
    <div class="modal-dialog modal-xl mw-80 w-100">
        <div class="wizard-content">
            <div class="modal-content">
                <div class="modal-body">
                    {{html()->form('post', route('portal.school.inscription.store', [$school->slug]))->attributes(['id' => 'form_inscripcion', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class' => 'validation-wizard wizard-circle'])->open()}}
                    {!! RecaptchaV3::field('inscriptions', 'g-recaptcha-response', true, 'form_inscripcion') !!}
                    @include('portal.inscriptions.fields.step_1')
                    @include('portal.inscriptions.fields.step_2')
                    @include('portal.inscriptions.fields.step_3')
                    @includeWhen($school->create_contract, 'portal.inscriptions.fields.step_4')
                    @includeWhen($school->send_documents, 'portal.inscriptions.fields.step_5')
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    let imgUser = "{{ asset('img/user.png') }}";
    const school = @json($school);
    const url_autocomplete = "{{ route('portal.autocomplete.fields') }}";
    const url_search = "{{ route('portal.autocomplete.search_doc') }}";
    const form_inscripcion = $("#form_inscripcion");
    const MinDateBirth = moment().subtract(20, 'year');
    const MaxDateBirth = moment().subtract(3, 'year');
    const fileSize = 3;

    // ----------------------------
    // Estado de la foto
    // ----------------------------
    let selectedPhotoFile = null;
    let rotatedPhotoBlob = null;
    let currentRotation = 0;
    let currentPreviewObjectUrl = null;

    $("#filesize").html(`${fileSize} `);

    form_inscripcion.validate({
        rules: {
            names : {required: true, maxlength:50},
            last_names : {required: true, maxlength:50},
            date_birth : {required: true, dateLessThan: MaxDateBirth.format('YYYY-MM-DD'), dateGreaterThan: MinDateBirth.format('YYYY-MM-DD')},
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

            photo: {required: false, extension: "png|jpeg|jpg", filesize: fileSize},
            player_document: {required: true, extension: "png|jpeg|jpg|pdf", filesize: fileSize},
            medical_certificate: {required: true, extension: "png|jpeg|jpg|pdf", filesize: fileSize},
            tutor_document: {required: true, extension: "png|jpeg|jpg|pdf", filesize: fileSize},
            payment_receipt: {required: false, extension: "png|jpeg|jpg|pdf", filesize: fileSize},

            contrato_aff: {required: true},
            contrato_insc: {required: true},
        },
    });

    form_inscripcion.steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "fade",
        stepsOrientation: "horizontal",
        titleTemplate: '<span class="step">#index#</span> #title#',
        autoFocus: true,
        enableAllSteps: false,
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
            events();

            $("#date_birth").bootstrapMaterialDatePicker({
                time: false,
                clearButton: false,
                lang: 'es',
                cancelText: 'Cancelar',
                okText: 'Aceptar',
                minDate: MinDateBirth,
                maxDate: MaxDateBirth
            });
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            if (currentIndex == 3 && tutorSignatureRequiredAndMissing()) {
                Swal.fire({
                    title: window.app_name,
                    text: 'Ingresa la firma del acudiente para poder continuar',
                    type: 'warning',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                });
                return false;
            }

            if (currentIndex == 3 && playerSignatureRequiredAndMissing()) {
                Swal.fire({
                    title: window.app_name,
                    text: 'Ingresa la firma del deportista para poder continuar',
                    type: 'warning',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                });
                return false;
            }

            return currentIndex > newIndex || (
                currentIndex < newIndex &&
                (
                    form_inscripcion.find(".body:eq(" + newIndex + ") label.error").remove(),
                    form_inscripcion.find(".body:eq(" + newIndex + ") .error").removeClass("error")
                ),
                form_inscripcion.validate().settings.ignore = ":disabled,:hidden",
                form_inscripcion.valid()
            );
        },
        onFinishing: function(event, currentIndex) {
            onClickRecaptcha(event);
            return form_inscripcion.validate().settings.ignore = ":disabled", form_inscripcion.valid();
        },
        onFinished: function(event, currentIndex) {
            if (currentIndex == 3 && tutorSignatureRequiredAndMissing()) {
                Swal.fire({
                    title: window.app_name,
                    text: 'Ingresa la firma del acudiente para poder continuar',
                    type: 'warning',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                });
                return false;
            }

            if (currentIndex == 3 && playerSignatureRequiredAndMissing()) {
                Swal.fire({
                    title: window.app_name,
                    text: 'Ingresa la firma del deportista para poder continuar',
                    type: 'warning',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                });
                return false;
            }

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
            });
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
                    form_inscripcion.resetForm();
                    form_inscripcion.clearForm();
                    document.getElementById("form_inscripcion").reset();
                    resetPhotoState();
                    localStorage.removeItem('form-storage');
                    $('#modal_inscription').modal('hide');
                }
            });
        }
    });

    const tutorCanvas = document.getElementById("firma_tutor");
    const playerCanvas = document.getElementById("firma_alumno");

    const signaturePadTutor = (school.create_contract && tutorCanvas)
        ? new SignaturePad(tutorCanvas)
        : null;

    const signaturePadPlayer = (school.sign_player && playerCanvas)
        ? new SignaturePad(playerCanvas)
        : null;

    function tutorSignatureRequiredAndMissing() {
        return school.create_contract && (!signaturePadTutor || signaturePadTutor.isEmpty());
    }

    function playerSignatureRequiredAndMissing() {
        return school.sign_player && (!signaturePadPlayer || signaturePadPlayer.isEmpty());
    }

    // ----------------------------
    // Envío
    // ----------------------------
    function sendData() {
        let data = new FormData();
        let form_data = $(form_inscripcion).serializeArray();
        let url = $(form_inscripcion).attr('action');

        $.each(form_data, function (key, input) {
            data.append(input.name, input.value);
        });

        $.each($("input[type=file]"), function(i, input) {
            if (input.name === 'photo') {
                if (rotatedPhotoBlob) {
                    const originalName = input.files?.[0]?.name || 'photo.jpg';
                    const extension = rotatedPhotoBlob.type === 'image/png' ? '.png' : '.jpg';
                    const cleanName = originalName.replace(/\.[^/.]+$/, '');
                    data.append('photo', rotatedPhotoBlob, cleanName + extension);
                } else if (input.files && input.files[0]) {
                    data.append('photo', input.files[0]);
                }
            } else {
                $.each(input.files, function(j, file) {
                    data.append(input.name, file);
                });
            }
        });

        if (school.create_contract && signaturePadTutor) {
            data.append("signatureTutor", signaturePadTutor.toDataURL("image/png"));
        }

        if (school.sign_player && signaturePadPlayer) {
            data.append("signatureAlumno", signaturePadPlayer.toDataURL("image/png"));
        }

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
                        resetPhotoState();
                        localStorage.removeItem('form-storage');
                        window.location.reload();
                    }
                });
            },
            error: function(xhr, status, error) {
                let message = 'Algo salío mal, no hemos podido procesar la información en este momento, por favor intenta de nuevo más tarde!';
                if (xhr.status == 422 || xhr.status == 500) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    type: 'error',
                    title: window.app_name,
                    text: message,
                }).then(okay => {
                    if (okay) {
                        window.location.reload();
                    }
                });
            }
        });
    }

    // ----------------------------
    // Autocompletado y otros eventos
    // ----------------------------
    $('#identification_document').on('keyup', function(){
        let documentInput = $("#identification_document");
        let documentVal = documentInput.val().toLowerCase().trim();

        if(documentVal.length >= 8 && documentInput.valid()){
            $.get(url_search, {doc: documentVal, school_id: school.id}, function (result) {
                let info = result.data;
                if(info?.names){
                    $('#names').val(info.names);
                    $('#last_names').val(info.last_names);
                    $('#date_birth').val(info.date_birth);
                    $('#place_birth').val(info.place_birth);
                    $('#document_type').val(info.document_type);
                    $('#gender').val(info.gender);
                    $('#email').val(info.email.toLowerCase());
                    $('#mobile').val(info.mobile);
                    $('#medical_history').val(info.medical_history);
                    $('#address').val(info.address);
                    $('#municipality').val(info.municipality);
                    $('#neighborhood').val(info.neighborhood);
                    $('#rh').val(info.rh);
                    $('#eps').val(info.eps);
                    $('#student_insurance').val(info.student_insurance);
                    $('#school').val(info.school);
                    $('#degree').val(info.degree);
                    $('#jornada').val(info.jornada);
                }
            });
        }
    });

    $('#email').on('change', function(){
        let inputEmail = $("#email");
        let email = inputEmail.val().toLowerCase().trim();
        inputEmail.val(email);
        $('#tutor_email').val(email);
    });

    // ----------------------------
    // Foto: seleccionar + rotar
    // ----------------------------
    $(document).on('change', '#file-upload', function () {
        handlePhotoSelection(this);
    });

    $(document).on('click', '#rotate-left', function () {
        if (!selectedPhotoFile) return;
        currentRotation = (currentRotation - 90 + 360) % 360;
        updateRotatedPreview();
    });

    $(document).on('click', '#rotate-right', function () {
        if (!selectedPhotoFile) return;
        currentRotation = (currentRotation + 90) % 360;
        updateRotatedPreview();
    });

    function handlePhotoSelection(input) {
        const label = $(input).next('label.custom-file-label');

        if (!input.files || !input.files[0]) {
            resetPhotoState();
            label.html('Seleccionar...');
            return;
        }

        const file = input.files[0];

        if (!/^image\/(jpeg|png)$/.test(file.type)) {
            Swal.fire({
                type: 'warning',
                title: window.app_name,
                text: 'Solo se permiten imágenes JPG, JPEG o PNG.'
            });
            input.value = '';
            resetPhotoState();
            label.html('Seleccionar...');
            return;
        }

        selectedPhotoFile = file;
        currentRotation = 0;
        label.html('Seleccionada.');

        updateRotatedPreview();
    }

    function updateRotatedPreview() {
        if (!selectedPhotoFile) {
            resetPhotoState();
            return;
        }

        createRotatedImage(selectedPhotoFile, currentRotation)
            .then(function (result) {
                rotatedPhotoBlob = result.blob;

                if (currentPreviewObjectUrl) {
                    URL.revokeObjectURL(currentPreviewObjectUrl);
                }

                currentPreviewObjectUrl = result.url;
                $('#player-img').attr('src', currentPreviewObjectUrl);
            })
            .catch(function (error) {
                console.error('Error procesando imagen:', error);

                // Fallback para que al menos la vista previa salga
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#player-img').attr('src', e.target.result);
                };
                reader.readAsDataURL(selectedPhotoFile);

                rotatedPhotoBlob = null;
            });
    }

    function createRotatedImage(file, rotation) {
        return new Promise(function (resolve, reject) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const image = new Image();

                image.onload = function () {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    const normalizedRotation = ((rotation % 360) + 360) % 360;
                    const isSideways = normalizedRotation === 90 || normalizedRotation === 270;

                    canvas.width = isSideways ? image.height : image.width;
                    canvas.height = isSideways ? image.width : image.height;

                    ctx.save();

                    if (normalizedRotation === 90) {
                        ctx.translate(canvas.width, 0);
                    } else if (normalizedRotation === 180) {
                        ctx.translate(canvas.width, canvas.height);
                    } else if (normalizedRotation === 270) {
                        ctx.translate(0, canvas.height);
                    }

                    ctx.rotate(normalizedRotation * Math.PI / 180);
                    ctx.drawImage(image, 0, 0);
                    ctx.restore();

                    const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('No se pudo generar el blob de la imagen.'));
                            return;
                        }

                        const objectUrl = URL.createObjectURL(blob);

                        resolve({
                            blob: blob,
                            url: objectUrl
                        });
                    }, mimeType, 0.95);
                };

                image.onerror = function () {
                    reject(new Error('No se pudo cargar la imagen.'));
                };

                image.src = e.target.result;
            };

            reader.onerror = function () {
                reject(new Error('No se pudo leer el archivo.'));
            };

            reader.readAsDataURL(file);
        });
    }

    function resetPhotoState() {
        selectedPhotoFile = null;
        rotatedPhotoBlob = null;
        currentRotation = 0;

        if (currentPreviewObjectUrl) {
            URL.revokeObjectURL(currentPreviewObjectUrl);
            currentPreviewObjectUrl = null;
        }

        $('#player-img').attr('src', imgUser);
        $('#file-upload').val('');
        $('#file-upload').next('label.custom-file-label').html('Seleccionar...');
    }

    function events() {
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
        const clone = {};

        for (const attribute of attributes) {
            const value = elem[attribute];
            if (value ?? false) {
                clone[attribute] = value;
            }
        }

        return clone;
    };

    const deMethodize = (fn) => (arg0, ...args) => fn.apply(arg0, args);
    const flatMap = deMethodize(Array.prototype.flatMap);
    const notEmpty = (obj) => Object.keys(obj).length;

    window.addEventListener('DOMContentLoaded', () => {
        const filterFormElements = function(form, {
            attributes = [],
            exclude = []
        }) {
            if (!attributes.length) return [];

            const selectedInputs = `input:not(${exclude.join(',')})`;

            return flatMap(form, (elem, i) => {
                if (elem.matches(selectedInputs) || elem.matches('select') || elem.matches('textarea')) {
                    const inputs = objectFromAttributes(elem, attributes);
                    if (notEmpty(inputs)) {
                        return [[i, inputs]];
                    }
                }

                return [];
            });
        };

        const getFormData = function() {
            return JSON.parse(localStorage.getItem('form-storage')) ?? [];
        };

        const deleteFormData = function() {
            localStorage.removeItem('form-storage');
        };

        const storeFormData = function(formData = []) {
            localStorage.setItem('form-storage', JSON.stringify(formData));
        };

        const populateForm = function(form, formData) {
            formData.forEach(([i, attributes]) => {
                const formElement = form[i];

                for (const key in attributes) {
                    formElement[key] = attributes[key];
                }
            });
        };

        const form = document.querySelector('#form_inscripcion');

        form.addEventListener('change', (event) => {
            const form = event.currentTarget;
            const formData = filterFormElements(form, {
                attributes: ['value', 'checked'],
                exclude: ['[type=password]', '[type=hidden]', '[type=file]']
            });

            storeFormData(formData);
        });

        form.addEventListener('submit', deleteFormData);
        form.addEventListener('reset', deleteFormData);
        populateForm(form, getFormData());
    });

    function forceLower(strInput) {
        strInput.value = strInput.value.toLowerCase();
    }
</script>
@endpush
@push('css')
{!! RecaptchaV3::initJs() !!}
@endpush
