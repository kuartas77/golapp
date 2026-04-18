<template>
    <panel>
        <template #header>
            <div class="row">
                <div class="col-md-auto" data-tour="admin-training-groups-actions">
                    <a data-bs-toggle="modal" data-bs-target="#composeModalTrainigG" id="btn-compose-user"
                        class="btn btn-block btn-primary" href="javascript:void(0);">
                        Crear Grupo
                    </a>
                </div>
                <div class="col-md-8">
                    <p>Grupos de entrenamiento, son parte fundamental del sistema, ya que con ellos se pueden buscar las inscripciones, pagos y asistencias.</p>
                </div>
                <div class="col-md-auto ms-md-auto">
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>
        </template>
        <template #body>

            <div data-tour="admin-training-groups-table">
                <DatatableTemplate :options="options" :id="'training_table'" ref="table" @click="onClickRow"/>
            </div>
        </template>
    </panel>

    <ModalTrainingGroup :id="selectedId" @update="reloadTable" @cancel="onCancel"/>

    <breadcrumb :parent="'Adminstración'" :current="'Grupos de entrenamiento'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useTrainingList from '@/composables/admin/groups/trainingList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import ModalTrainingGroup from './ModalTrainingGroup.vue';
import { trainingGroupsTutorial } from '@/tutorials/admin'
const { table, options, selectedId, onClickRow, reloadTable, onCancel } = useTrainingList()
const tutorial = usePageTutorial(trainingGroupsTutorial)
</script>
