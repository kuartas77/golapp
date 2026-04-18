<template>
    <panel>
        <template #body>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                    <i class="fa-regular fa-circle-question me-2"></i>
                    Guia
                </button>
            </div>
            <div data-tour="admin-schools-info-table">
                <DatatableTemplate :options="options" :id="'schools_table'" :table="table"/>
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Información Escuelas'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useSchoolList from '@/composables/admin/school/infoSchoolList'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from "@/composables/use-meta";
import { schoolsInfoTutorial } from '@/tutorials/admin'
usePageTitle('Escuelas')
const { table, options } = useSchoolList()
const tutorial = usePageTutorial(schoolsInfoTutorial)
</script>
