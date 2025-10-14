<template>
    <panel>
        <template #body>
            <a data-bs-toggle="modal" data-bs-target="#composeModalTrainigG" id="btn-compose-user"
                class="btn btn-block btn-primary" href="javascript:void(0);">
                Crear usuario
            </a>
            <DatatableTemplate :options="options" :id="'training_table'" ref="table" @click="onClickRow"/>
        </template>
    </panel>

    <ModalTrainingGroup :id="selectedId"/>

    <breadcrumb :parent="'Adminstración'" :current="'Grupos de entrenamiento'" />
</template>
<script setup>
import useTrainingList from '@/composables/admin/groups/trainingList'
import { usePageTitle } from "@/composables/use-meta";
import ModalTrainingGroup from './ModalTrainingGroup.vue';
usePageTitle('G. Entrenamiento')
const { table, options, selectedId, onClickRow } = useTrainingList()
</script>