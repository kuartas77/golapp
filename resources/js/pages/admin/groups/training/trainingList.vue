<template>
    <panel>
        <template #header>
            <div class="row">
                <div class="col-md-auto d-flex gap-2 flex-wrap" data-tour="admin-training-groups-actions">
                    <a data-bs-toggle="modal" data-bs-target="#composeModalTrainigG" id="btn-compose-user"
                        class="btn btn-block btn-primary" href="javascript:void(0);">
                        Crear Grupo
                    </a>
                    <router-link :to="{ name: 'training-schedules' }" class="btn btn-outline-info">
                        Horarios
                    </router-link>
                    <router-link :to="{ name: 'training-groups-admin' }" class="btn btn-info">
                        Administrar Grupos
                    </router-link>
                </div>
                <div class="col-md-8">
                    <p>Los grupos de entrenamiento organizan las inscripciones, los pagos y las asistencias de cada equipo.</p>
                </div>
                <div class="col-md-auto ms-md-auto">
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guía
                    </button>
                </div>
            </div>
        </template>
        <template #body>

            <ContentState
                v-if="globalError"
                type="error"
                :message="globalError"
                action-label="Reintentar"
                @action="reloadTable"
            />
            <div v-show="!globalError" data-tour="admin-training-groups-table">
                <DatatableTemplate :key="tableKey" :options="options" :id="'training_table'" ref="table" @click="onClickRow"/>
            </div>
        </template>
    </panel>

    <ModalTrainingGroup :id="selectedId" @update="reloadTable" @cancel="onCancel"/>

    <breadcrumb :parent="'Administración'" :current="'Grupos de entrenamiento'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import ContentState from '@/components/general/ContentState.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useTrainingList from '@/composables/admin/groups/trainingList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import ModalTrainingGroup from './ModalTrainingGroup.vue';
import { trainingGroupsTutorial } from '@/tutorials/admin'
import { useTemplateRef } from 'vue'

const table = useTemplateRef('table')
const { tableKey, options, selectedId, globalError, onClickRow, reloadTable, onCancel } = useTrainingList(table)
const tutorial = usePageTutorial(trainingGroupsTutorial)
</script>
