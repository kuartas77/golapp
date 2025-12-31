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
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="onClickRow" />
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import usePlayerList from '@/composables/player/playersList'

const { options, table, onClickRow, reloadTable } = usePlayerList()

</script>