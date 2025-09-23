<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options" :ajax="ajaxConfig"
                class="table table-bordered table-sm" id="training_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Grupos de entrenamiento'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useTrainingList from '@/composables/admin/groups/trainingList'
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('G. Entrenamiento')
const { table, columns, options, ajaxConfig } = useTrainingList()
</script>