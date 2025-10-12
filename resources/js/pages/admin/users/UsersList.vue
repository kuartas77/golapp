<template>
    <panel>
        <template #lateral />
        <template #body>
            <div class="col-sm-auto">
             <a data-bs-toggle="modal" data-bs-target="#composeModalUser" id="btn-compose-user"
                    class="btn btn-block btn-primary" href="javascript:void(0);">
                    Crear usuario
                </a></div>
            <DatatableTemplate :options="options" :id="'users_table'" ref="table" @click="onClickRow($event)" />
        </template>
    </panel>

    <div class="modal fade" id="composeModalUser" tabindex="-1" role="dialog" aria-labelledby="userModal"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-md" role="document">
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="initialData">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModal">Usuario</h5>
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                            class="btn-close" @click="onCancel"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mt-0">
                            <div class="form-group">
                                <inputField label="Nombres" name="name" />
                            </div>
                            <div class="form-group">
                                <inputField label="Correo" name="email" type="email" />
                            </div>
                            <div class="form-group">
                                <Field name="rol_id" v-slot="{ field, handleChange, handleBlur }">
                                    <label class="form-label">Rol</label>
                                    <select class="form-select" v-bind="field" @change="handleChange"
                                        @blur="handleBlur">
                                        <option value="2">School</option>
                                        <option value="3">Instructor</option>
                                    </select>
                                </Field>
                                <ErrorMessage name="rol_id" class="custom-error" />
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" @click="onCancel">
                            <i class="flaticon-cancel-12"></i> Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </Form>
        </div>
    </div>

    <breadcrumb :parent="'Adminstración'" :current="'Cuentas de usuarios'" />
</template>
<script setup>
import useUsersList from '@/composables/admin/users/usersList'
import { ErrorMessage, Field, Form } from 'vee-validate'

const { table, options, initialData, schema, onClickRow, onCancel, submit } = useUsersList()
</script>