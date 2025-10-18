<template>
    <panel>
        <template #lateral />
        <template #body>
            <a data-bs-toggle="modal" data-bs-target="#composeModalCompetitionG"
                class="btn btn-block btn-primary" href="javascript:void(0);">
                Crear Grupo
            </a>
            <DatatableTemplate :options="options" :id="'competition_table'" ref="table" @click="onClickRow">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
            </DatatableTemplate>


            <ModalCompetitionGroup :id="selectedId" @update="reloadTable" @cancel="onCancel"/>


        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Grupos de competencia'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useCompetitionGList from '@/composables/admin/groups/competitionGList'
import ModalCompetitionGroup from "./ModalCompetitionGroup.vue";

const { table, options, selectedId, onClickRow, reloadTable, onCancel } = useCompetitionGList()
</script>