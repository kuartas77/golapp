<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options"
                class="table table-bordered table-sm" id="competition_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Grupos de competencia'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useCompetitionGList from '@/composables/admin/groups/competitionGList'
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('G Competencia')
const { table, columns, options } = useCompetitionGList()
</script>