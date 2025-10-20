<template>
    <panel>
        <template #body>
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="onClickRow"/>
        </template>
    </panel>

    <ModalPlayer :unique_code="selectedId" @update="reloadTable" @cancel="onCancel"></ModalPlayer>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import usePlayerList from '@/composables/player/playersList'
import ModalPlayer from './ModalPlayer.vue';

const {  options, table, selectedId, onClickRow, reloadTable, onCancel  } = usePlayerList()

</script>