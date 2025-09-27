<template>
    <panel>
        <template #body>
            <DatatableTemplate :options="options" :id="'players_table'" ref="table" @click="resolveRouteFromClick($event)">



                <template #link="props">
                    <div class="text-center">
                        <a href="#" :data-item-id="props.cellData" class="link-primary">
                            {{ props.rowData.unique_code}}
                        </a>
                    </div>
                </template>

                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>

            </DatatableTemplate>
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