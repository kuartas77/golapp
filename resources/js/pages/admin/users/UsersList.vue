<template>
    <panel>
        <template #lateral/>
        <template #body>
             <DataTable :columns="columns" :options="options"
                class="table table-bordered table-sm" id="users_table" ref="table">
                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>
             </DataTable>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Cuentas de usuarios'" />
</template>
<script setup>
import dayjs from "@/utils/dayjs";
import useUsersList from '@/composables/admin/users/usersList'
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('Usuarios')
const { table, columns, options } = useUsersList()
</script>