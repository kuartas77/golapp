<template>
    <panel>
        <template #body>
            <DatatableTemplate :options="options" :id="'schools_table'" :table="table"/>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Escuelas'" />
</template>
<script setup>
import useSchoolList from '@/composables/admin/school/schoolList'
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('Escuelas')
const { table, options } = useSchoolList()
</script>