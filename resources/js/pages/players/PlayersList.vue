<template>
    <panel>
        <template #lateral />
        <template #header>
            <div class="row">
                <div class="col-md-auto">
                    <p>En este listado se incluyen todos los deportistas que han sido parte de la escuela en el transcurso de los años.</p>
                </div>
            </div>
        </template>
        <template #body>
            <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <span>{{ globalError }}</span>
                <button type="button" class="btn btn-sm btn-outline-danger" @click="reloadTable">
                    Reintentar
                </button>
            </div>
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="onClickRow" />
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import usePlayerList from '@/composables/player/playersList'

const { options, table, onClickRow, reloadTable, globalError } = usePlayerList()

</script>
