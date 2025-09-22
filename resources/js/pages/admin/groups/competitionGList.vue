<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options" ajax="/datatables/competition_groups_enabled"
                class="table table-bordered table-sm" id="competition_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :active="'Grupos de competencia'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useCompetitionGList from '@/composables/admin/groups/competitionGList'

const { table, columns, options } = useCompetitionGList()
</script>