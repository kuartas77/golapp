<template>
    <breadcrumb :active="'Listado'" />
    <div class="layout-px-spacing ">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="panel br-6 p-2">

                    <DataTable :columns="columns" :options="options" ajax="datatables/players_enabled"
                        class="table table-bordered table-sm" id="players_table" ref="players_table"
                        @click="resolveRouteFromClick($event)">

                        <template #photo="props">
                            <!-- <div class="person text-center"> -->
                                <!-- <div class="user-info"> -->
                                    <div class="avatar avatar-sm me-1">
                                        <img :src="props.cellData" alt="avatar" class="rounded-circle">
                                    </div>
                                <!-- </div> -->
                            <!-- </div> -->
                        </template>

                        <template #link="props">
                            <div class="text-center">
                                <a href="#" :data-item-id="props.cellData" class="link-primary">{{ props.rowData.unique_code }}</a>
                            </div>
                        </template>

                        <template #date="props">
                            <div class="text-center">
                                {{ dayjs(props.cellData).format('l') }}
                            </div>
                        </template>

                    </DataTable>

                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import breadcrumb from "@/components/layout/breadcrumb.vue";
import dayjs from "@/utils/dayjs";
import { useRouter } from 'vue-router';
import { players_table, columns, options } from '@/composables/playersList'
const router = useRouter()
const resolveRouteFromClick = (e) => {
    const itemId = e.target.dataset.itemId
    if (!itemId) {
        return
    }
    e.preventDefault()
    router.push('/deportistas/' + itemId);
}


</script>