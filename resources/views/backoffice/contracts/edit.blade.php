@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<x-bread-crumb title="Usuario" :option="0"/>
<x-row-card col-inside="8" col-outside="2">
    {{html()->modelForm($contract, 'patch', route('config.contracts.update', [$contract->id]))->attributes(['id'=>'form_user','class' => 'form-material m-t-0'])->open()}}
        <div class="form-body">
            @include('backoffice.contracts.fields')
        </div>
        <div class="form-actions m-t-0 text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar</button>
            <a href="{{ route('config.contracts.index') }}" class="btn waves-effect waves-light btn-rounded btn-outline-warning">Cancelar</a>
        </div>
    {{ html()->closeModelForm() }}
</x-row-card >
@endsection
@section('modals')
@endsection
@push('scripts')
<script src="{{ asset('js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
<script>
    const stylePDF = "{{asset('css/dompdf.css')}}"
    const headerTinymce = tinymce.init({
        selector: 'textarea#header', // Replace this CSS selector to match the placeholder element for TinyMCE
        plugins: 'quickbars code table lists image wordcount searchreplace',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table image',
        language: 'es_MX',
        content_css: stylePDF,
        entity_encoding : "raw",
        setup: function (editor) {
            editor.on('change', function () {
                $(`#${editor.id}`).val(editor.getContent())
                $(`#${editor.id}`).valid()
            });
        },
        extended_valid_elements : '+*[*]',
        indent: true,
        br_in_pre: true,
    });
    const bodyTinymce = tinymce.init({
        selector: 'textarea#body', // Replace this CSS selector to match the placeholder element for TinyMCE
        plugins: 'quickbars code table lists image wordcount searchreplace pagebreak',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table image',
        language: 'es_MX',
        content_css: stylePDF,
        entity_encoding : "raw",
        setup: function (editor) {
            editor.on('change', function () {
                $(`#${editor.id}`).val(editor.getContent())
                $(`#${editor.id}`).valid()
            });
        },
        extended_valid_elements : '+*[*]',
        pagebreak_separator: '<pagebreak />',
        indent: true,
        br_in_pre: true,
    });
    const footerTinymce = tinymce.init({
        selector: 'textarea#footer', // Replace this CSS selector to match the placeholder element for TinyMCE
        plugins: 'quickbars code table lists image wordcount searchreplace',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table image',
        language: 'es_MX',
        content_css: stylePDF,
        entity_encoding : "raw",
        setup: function (editor) {
            editor.on('change', function () {
                $(`#${editor.id}`).val(editor.getContent())
                $(`#${editor.id}`).valid()
            });
        },
        extended_valid_elements : '+*[*]',
        indent: true,
        br_in_pre: true,
    });

    let validator = $("#form_contracts").submit(function() {
        // update underlying textarea before submit validation
        tinymce.triggerSave();
    }).validate({
        ignore: ':hidden:not(textarea)',
        rules:{
            name:{required:true},
            school_id:{required:true},
            header:{required:true},
            body:{required:true},
            footer:{required:true},
        },
        errorPlacement: function(label, element){
            if(element.is("textarea")) {
                label.insertAfter(element.next())
            } else {
                label.insertAfter(element)
            }
        }
    });
    validator.focusInvalid = function() {
        // put focus on tinymce on submit validation
        if (this.settings.focusInvalid) {
            try {
                var toFocus = $(this.findLastActive() || this.errorList.length && this.errorList[0].element || []);
                if (toFocus.is("textarea")) {
                    tinyMCE.get(toFocus.attr("id")).focus();
                } else {
                    toFocus.filter(":visible").focus();
                }
            } catch (e) {
                // ignore IE throwing errors when focusing hidden elements
            }
        }
    }
</script>
@endpush
