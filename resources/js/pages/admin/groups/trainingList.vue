<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options" ajax="/datatables/training_groups_enabled"
                class="table table-bordered table-sm" id="training_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :active="'Grupos de entrenamiento'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useTrainingList from '@/composables/admin/groups/trainingList'

const { table, columns, options } = useTrainingList()
</script>