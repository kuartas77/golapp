<template>
    <panel>
        <template #lateral />
        <template #header>
            <div class="row">
                <div class="col-md-auto">
                    <p data-tour="players-list-intro">En este listado se incluyen todos los deportistas que han sido parte de la escuela en el transcurso de los años.</p>
                </div>
                <div class="col-md-auto ms-md-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="tutorial.start()">
                        Guia
                    </button>
                </div>
            </div>
        </template>
        <template #body>
            <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <span>{{ globalError }}</span>
                <button type="button" class="btn btn-sm btn-danger" @click="reloadTable">
                    Reintentar
                </button>
            </div>
            <div data-tour="players-list-table">
                <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="onClickRow" />
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import usePlayerList from '@/composables/player/playersList'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { playersListTutorial } from '@/tutorials/players'

const { options, table, onClickRow, reloadTable, globalError } = usePlayerList()
const tutorial = usePageTutorial(playersListTutorial)

</script>
