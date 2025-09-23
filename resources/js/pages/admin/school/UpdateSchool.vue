<template>
    <panel>
        <template #lateral></template>
        <template #body>
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="formData">
                <div class="row col-md-12">
                    <div class="form-group col-md-4 text-center">
                        <inputFile label="Logo" name="logo" />
                    </div>
                    <div class="col-md-4">
                        <div class="form-group ">
                            <inputField label="Nombre Escuela" name="name" />
                        </div>
                        <div class="form-group ">
                            <inputField label="Correo Escuela" type="email" name="email" />
                        </div>
                        <div class="form-group ">
                            <inputField label="Representante" name="agent" />
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group ">
                            <inputField label="Dirección" name="address" />
                        </div>
                        <div class="form-group ">
                            <inputField label="Teléfono" name="phone" />
                        </div>
                    </div>

                </div>
                <br>
                <h6>Configuraciones</h6>
                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group ">
                            <inputField label="Precio de la Matricula" name="INSCRIPTION_AMOUNT" :currency="true" />
                        </div>
                        <div class="form-group ">
                            <inputField label="Precio de la Mensualidad" name="MONTHLY_PAYMENT" :currency="true" />
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group ">
                            <inputField label="Precio de la Anualidad / Mes" name="ANNUITY" :currency="true" />
                        </div>
                        <div class="form-group ">
                            <inputField label="Día de notificación" type="number" name="NOTIFY_PAYMENT_DAY" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <checkbox label="Inscripciones habilitadas" name="inscriptions_enabled" disabled="disabled"/>
                        <checkbox label="Creación de contratos?" name="create_contract" disabled="disabled"/>
                        <checkbox label="Envio documentos?" name="send_documents" disabled="disabled"/>
                    </div>
                    <div class="col-md-3">
                        <checkbox label="Plataforma Tutores?" name="tutor_platform" disabled="disabled"/>
                        <checkbox label="Firma Deportistas?" name="sign_player" disabled="disabled"/>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Guardar</button>
            </Form>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Escuela'" />
</template>
<script setup>
import { Form } from 'vee-validate'
import useFormSchool from '@/composables/admin/school/formSchool';
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('Escuela')
const { form, formData, schema, submit, reset } = useFormSchool()

// Swal.fire("SweetAlert2 is working!");
</script>