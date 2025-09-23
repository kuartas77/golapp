<template>
    <panel>
        <template #body>
            <DataTable :columns="columns" :options="options" :ajax="ajaxConfig"
                class="table table-bordered table-sm" id="players_table" ref="table"
                @click="resolveRouteFromClick($event)">

                <template #photo="props">
                    <div class="avatar avatar-sm me-1">
                        <img :src="props.cellData" alt="avatar" class="rounded-circle">
                    </div>
                </template>

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

            </DataTable>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Deportistas'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import usePlayerList from '@/composables/playersList'
import { usePageTitle } from "@/composables/use-meta";

usePageTitle('Deportistas')

const { table, columns, options, ajaxConfig, resolveRouteFromClick } = usePlayerList()

</script>