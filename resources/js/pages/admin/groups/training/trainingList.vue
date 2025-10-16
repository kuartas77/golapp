<template>
    <panel>
        <template #body>
            <a data-bs-toggle="modal" data-bs-target="#composeModalTrainigG" id="btn-compose-user"
                class="btn btn-block btn-primary" href="javascript:void(0);">
                Crear Grupo
            </a>
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