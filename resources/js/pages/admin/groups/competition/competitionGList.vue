<template>
    <panel>
        <template #lateral />
        <template #body>
            <DatatableTemplate :options="options" :id="'competition_table'" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
            </DatatableTemplate>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Grupos de competencia'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useCompetitionGList from '@/composables/admin/groups/competitionGList'
import { usePageTitle } from "@/composables/use-meta";
import { ref } from 'vue';

usePageTitle('G Competencia')
const { table, options } = useCompetitionGList()
</script>