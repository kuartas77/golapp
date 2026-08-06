<template>
    <h6 class="d-flex block-helper justify-content-center">
        <strong>Los archivos que van a ser cargados deben ser JPG, JPEG, PNG o PDF, con un tamaño máximo de {{ fileSizeMb }} MB.</strong>
    </h6>

    <fieldset class="col-md-12 p-2">
        <div class="row">
            <div class="col-md-6 mb-3">
                <PortalFileInput
                    name="player_document"
                    label="Documento de identidad del deportista escaneado"
                    :accept="documentFileAccept"
                    :required="true"
                />
            </div>

            <div class="col-md-6 mb-3">
                <PortalFileInput
                    name="medical_certificate"
                    label="Certificado EPS escaneado"
                    :accept="documentFileAccept"
                    :required="true"
                />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <PortalFileInput
                    name="tutor_document"
                    label="Documento de identidad del acudiente escaneado"
                    :accept="documentFileAccept"
                    :required="true"
                />
            </div>

            <div class="col-md-6 mb-3">
                <PortalFileInput
                    name="payment_receipt"
                    label="Adjunte recibo de consignación o transferencia (Opcional)"
                    :accept="documentFileAccept"
                />
            </div>
        </div>
    </fieldset>
</template>

<script setup>
import PortalFileInput from '@/components/portal/PortalFileInput.vue';

defineProps({
    fileSizeMb: {
        type: Number,
        required: true,
    },
    documentFileAccept: {
        type: String,
        required: true,
    },
});
</script>
