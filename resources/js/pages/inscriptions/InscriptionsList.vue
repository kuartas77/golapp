<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-3">
                <div class="row g-3 align-items-end flex-grow-1 m-0">
                    <div class="col-sm-6 col-md-4 col-lg-3 px-0 pe-sm-3" data-tour="inscription-year-filter">
                        <label for="inscription_year" class="form-label fw-semibold">Año</label>
                        <select id="inscription_year" v-model="selectedYear" class="form-select form-select-sm">
                            <option
                                v-for="yearOption in yearOptions"
                                :key="yearOption.value"
                                :value="String(yearOption.value)"
                            >
                                {{ yearOption.label }}
                            </option>
                            <option v-if="!yearOptions.length" :value="selectedYear">
                                {{ selectedYear }}
                            </option>
                        </select>
                    </div>

                    <div v-if="canExportInscriptions" class="col-sm-auto px-0" data-tour="inscription-export">
                        <a :href="exportExcelUrl" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                            <i class="far fa-file-excel me-2"></i>
                            Exportar Excel
                        </a>
                    </div>
                </div>

                <div class="d-flex justify-content-start justify-content-lg-end">
                    <button type="button" class="btn btn-outline-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Ver tutorial
                    </button>
                </div>
            </div>

            <div data-tour="inscriptions-table">
                <DatatableTemplate :id="'inscription_table'" :options="options" ref="inscription_table"
                    @click="resolveRouteFromClick($event)">
                    <template #thead>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Código</th>
                                <th id="select_groups"></th>
                                <th id="select_categories"></th>
                                <th>Genero</th>
                                <th>Nombres</th>
                                <th>Cert. Médico</th>
                                <th>F.Inicio</th>
                                <th></th>
                            </tr>
                        </thead>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <teleport defer to="#select_groups">
        <select
            placeholder="Grupos"
            id="groups"
            name="groups"
            class="form-select form-select-sm form-select-custom"
            data-tour="inscription-group-filter"
        >
            <option value="">Grupos...</option>
            <option v-for="group in settings.all_groups" :value="group.id" :key="group.id">{{ group.name }}
            </option>
        </select>
    </teleport>

    <teleport defer to="#select_categories">
        <select
            placeholder="Categorias"
            id="categories"
            name="categories"
            class="form-select form-select-sm form-select-custom"
            data-tour="inscription-category-filter"
        >
            <option value="">Categorias...</option>
            <option v-for="category in settings.categories" :value="category.category" :key="category.category">
                {{ category.category }}
            </option>
        </select>
    </teleport>

    <ModalInscription :inscription_id="selectedInscriptionId" @success="onSuccessModal" @cancel="onCancelModal"/>
    <PageTutorialOverlay :tutorial="tutorial" />

    <breadcrumb :parent="'Plataforma'" :current="'Inscripciones'" />
</template>
<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSetting } from '@/store/settings-store';
import { useAuthUser } from '@/store/auth-user';
import useInscriptionConfig from '@/composables/inscription/inscriptionList';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue';
import { usePageTutorial } from '@/composables/usePageTutorial';
import { usePageTitle } from "@/composables/use-meta";
import { inscriptionsTutorial } from '@/tutorials/inscriptions';
import ModalInscription from './ModalInscription.vue';

usePageTitle('Inscripciones')

const route = useRoute()
const router = useRouter()
const settings = useSetting()
const auth = useAuthUser()
const currentYear = String(new Date().getFullYear())
const yearOptions = computed(() => settings.inscription_years || [])
const canExportInscriptions = computed(() => auth.hasAnyRole(['super-admin', 'school']))
const selectedYear = ref(String(route.query.inscription_year || currentYear))
const exportExcelUrl = computed(() => `/export/inscriptions/excel?inscription_year=${encodeURIComponent(selectedYear.value || currentYear)}`)
const { inscription_table, options, reloadTable, selectedInscriptionId, resolveRouteFromClick, onCancelModal, onSuccessModal } = useInscriptionConfig(selectedYear, canExportInscriptions)
const tutorial = usePageTutorial(inscriptionsTutorial, {
    canExportInscriptions,
})

function resolveDefaultYear() {
    const availableYears = yearOptions.value.map((option) => String(option.value))

    if (!availableYears.length) {
        return currentYear
    }

    const routeYear = String(route.query.inscription_year || '')
    if (routeYear && availableYears.includes(routeYear)) {
        return routeYear
    }

    if (availableYears.includes(currentYear)) {
        return currentYear
    }

    return availableYears[availableYears.length - 1]
}

watch(yearOptions, () => {
    const year = resolveDefaultYear()

    if (selectedYear.value !== year) {
        selectedYear.value = year
    }
}, { immediate: true })

watch(() => route.query.inscription_year, (value) => {
    if (!value) {
        return
    }

    const year = String(value)
    if (selectedYear.value !== year) {
        selectedYear.value = year
    }
})

watch(selectedYear, (year) => {
    router.replace({
        name: 'inscriptions',
        query: {
            ...route.query,
            inscription_year: String(year),
        },
    })

    reloadTable()
})

onMounted(async () => {
    await settings.getSettings()
})
</script>
