<template>
    <panel>
        <template #lateral/>
        <template #header>
            <div class="row">
                <div class="col-md-auto">
                    <a data-bs-toggle="modal" data-bs-target="#composeModalCompetitionG"
                        class="btn btn-block btn-primary" href="javascript:void(0);">
                        Crear Grupo
                    </a>
                </div>
                <div class="col-md-8">
                    <p>Estos grupos cómo su nombre lo dice se crean para poder gestionar las competencias y así obtener los datos de las estadísticos de los deportistas.</p>
                </div>
            </div>
        </template>
        <template #body>

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