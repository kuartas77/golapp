<template>
    <panel>
        <template #header>
            <div class="row g-3 align-items-start">
                <div class="col-lg">
                    <h4 class="mb-1">Conformar grupos de competencia</h4>
                    <p class="text-muted mb-0">
                        Selecciona un grupo y administra sus integrantes arrastrando deportistas entre el pool general y el grupo de competencia.
                    </p>
                </div>
                <div class="col-lg-auto d-flex gap-2 flex-wrap justify-content-lg-end">
                    <router-link :to="{ name: 'competition-groups' }" class="btn btn-outline-secondary btn-sm">
                        Volver al listado
                    </router-link>
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>
        </template>
        <template #body>
            <GroupAssignmentBoard mode="competition" />
        </template>
    </panel>
    <breadcrumb :parent="'Adminstración'" :current="'Conformar grupos de competencia'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import GroupAssignmentBoard from '@/pages/admin/groups/shared/GroupAssignmentBoard.vue'
import { usePageTitle } from '@/composables/use-meta'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { groupAssignmentBoardTutorial } from '@/tutorials/admin'

usePageTitle('Conformar G. Competencia')

const tutorial = usePageTutorial(groupAssignmentBoardTutorial)
</script>
