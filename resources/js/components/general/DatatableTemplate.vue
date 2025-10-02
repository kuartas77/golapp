<template>
    <DataTable :options="options" class="table table-bordered table-sm" :id="id" ref="table">

        <slot name="thead"></slot>

        <template #photo="props">
            <div class="avatar avatar-sm me-1">
                <img :src="props.cellData" alt="avatar" class="rounded-circle">
            </div>
        </template>
        <template #date="props">
            {{ dayjs(props.cellData).format('l') }}
        </template>
        <template #check="props">
            <template v-if="props.cellData">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-check-square">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
            </template>
            <template v-else>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="red"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-x-square">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                </svg>
            </template>
        </template>
        <template #link="props">
            <div class="text-center">
                <a href="#" :data-item-id="props.cellData" class="link-primary">{{
                    props.rowData.unique_code }}</a>
            </div>
        </template>
        <template #player="props">
            <div class="text-center">
                {{ props.rowData.unique_code }}
                {{ props.rowData.full_names }}
            </div>
        </template>

    </DataTable>
</template>
<script>
export default {
    name: 'DatatableTemplate'
}
</script>
<script setup>
import dayjs from "@/utils/dayjs";
import { useTemplateRef } from 'vue';
const props = defineProps(['options', 'id'])

const table = useTemplateRef('table')
defineExpose({
    table // Expone la referencia de la entrada
});
</script>