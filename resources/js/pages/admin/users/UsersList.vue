<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options" ajax="/datatables/users_enabled"
                class="table table-bordered table-sm" id="users_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :active="'Cuentas de usuarios'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useUsersList from '@/composables/admin/users/usersList'

const { table, columns, options } = useUsersList()
</script>