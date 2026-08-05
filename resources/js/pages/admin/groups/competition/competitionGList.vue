<template>
    <panel>
        <template #lateral/>
        <template #header>
            <div class="row">
                <div class="col-md-auto d-flex gap-2 flex-wrap" data-tour="admin-competition-groups-actions">
                    <a data-bs-toggle="modal" data-bs-target="#composeModalCompetitionG"
                        class="btn btn-block btn-primary" href="javascript:void(0);">
                        Crear Grupo
                    </a>
                    <router-link :to="{ name: 'competition-tournaments' }" class="btn btn-outline-info">
                        Torneos
                    </router-link>
                    <router-link :to="{ name: 'competition-groups-admin' }" class="btn btn-info">
                        Administrar Grupos
                    </router-link>
                </div>
                <div class="col-md-8">
                    <p>Los grupos de competencia organizan los torneos y permiten consultar las estadísticas de los deportistas.</p>
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
            <div v-show="!globalError" data-tour="admin-competition-groups-table">
                <DatatableTemplate :options="options" :id="'competition_table'" ref="table" @click="onClickRow">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
                </DatatableTemplate>
            </div>

            <ModalCompetitionGroup :id="selectedId" @update="reloadTable" @cancel="onCancel"/>

        </template>
    </panel>
    <breadcrumb :parent="'Administración'" :current="'Grupos de competencia'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import ContentState from '@/components/general/ContentState.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import dayjs from "@/utils/dayjs";
import useCompetitionGList from '@/composables/admin/groups/competitionGList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import ModalCompetitionGroup from "./ModalCompetitionGroup.vue";
import { competitionGroupsTutorial } from '@/tutorials/admin'
import { useTemplateRef } from 'vue'

const table = useTemplateRef('table')
const { options, selectedId, globalError, onClickRow, reloadTable, onCancel } = useCompetitionGList(table)
const tutorial = usePageTutorial(competitionGroupsTutorial)
</script>
