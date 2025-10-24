<template>
    <panel>
        <template #body>
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="onClickRow"/>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import usePlayerList from '@/composables/player/playersList'
import ModalPlayer from './ModalPlayer.vue';

const {  options, table, onClickRow, reloadTable  } = usePlayerList()

</script>