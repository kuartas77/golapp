<template>
    <panel>
        <template #body>
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="resolveRouteFromClick($event)"/>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import usePlayerList from '@/composables/player/playersList'
import { usePageTitle } from "@/composables/use-meta";

usePageTitle('Deportistas')

const { table, options, resolveRouteFromClick } = usePlayerList()

</script>