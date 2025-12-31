<template>
    <panel>
        <template #header>
            <div class="row">
                <div class="col-md-auto">
                    <a data-bs-toggle="modal" data-bs-target="#composeModalTrainigG" id="btn-compose-user"
                        class="btn btn-block btn-primary" href="javascript:void(0);">
                        Crear Grupo
                    </a>
                </div>
                <div class="col-md-8">
                    <p>Grupos de entrenamiento, son parte fundamental del sistema, ya que con ellos se pueden buscar las inscripciones, pagos y asistencias.</p>
                </div>
            </div>
        </template>
        <template #body>

            <DatatableTemplate :options="options" :id="'training_table'" ref="table" @click="onClickRow"/>
        </template>
    </panel>

    <ModalTrainingGroup :id="selectedId" @update="reloadTable" @cancel="onCancel"/>

    <breadcrumb :parent="'Adminstración'" :current="'Grupos de entrenamiento'" />
</template>
<script setup>
import useTrainingList from '@/composables/admin/groups/trainingList'
import ModalTrainingGroup from './ModalTrainingGroup.vue';
const { table, options, selectedId, onClickRow, reloadTable, onCancel } = useTrainingList()
</script>