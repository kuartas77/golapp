<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-3">
                <div class="row g-3 align-items-end flex-grow-1 m-0">
                    <div class="col-sm-6 col-md-4 col-lg-3 px-0 pe-sm-3" data-tour="inscription-year-filter">
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
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
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
                                <th>
                                    <select
                                        id="groups"
                                        name="groups"
                                        class="form-select form-select-sm form-select-custom"
                                        data-tour="inscription-group-filter"
                                        @change="onGroupFilterChange"
                                        @click.stop
                                    >
                                        <option value="">Grupos...</option>
                                        <option v-for="group in settings.all_groups" :value="group.id" :key="group.id">
                                            {{ group.name }}
                                        </option>
                                    </select>
                                </th>
                                <th>
                                    <select
                                        id="categories"
                                        name="categories"
                                        class="form-select form-select-sm form-select-custom"
                                        data-tour="inscription-category-filter"
                                        @change="onCategoryFilterChange"
                                        @click.stop
                                    >
                                        <option value="">Categorias...</option>
                                        <option v-for="category in settings.categories" :value="category.category" :key="category.category">
                                            {{ category.category }}
                                        </option>
                                    </select>
                                </th>
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

    <ModalInscription :inscription_id="selectedInscriptionId" @success="onSuccessModal" @cancel="onCancelModal"/>
    <AttendanceQrModal
        v-if="selectedAttendanceQrCode"
        :model-value="Boolean(selectedAttendanceQrCode)"
        :unique-code="selectedAttendanceQrCode"
        title="QR de asistencia"
        subtitle="Compártelo o descárgalo para la toma rápida desde el celular."
        @update:model-value="onAttendanceQrModalToggle"
    />
    <PageTutorialOverlay :tutorial="tutorial" />

    <breadcrumb :parent="'Plataforma'" :current="'Inscripciones'" />
</template>
<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import DatatableTemplate from '@/components/general/DatatableTemplate.vue';
import { useRoute, useRouter } from 'vue-router';
import { useSetting } from '@/store/settings-store';
import { useAuthUser } from '@/store/auth-user';
import useInscriptionConfig from '@/composables/inscription/inscriptionList';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue';
import AttendanceQrModal from '@/components/attendances/AttendanceQrModal.vue'
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
const {
    inscription_table,
    options,
    reloadTable,
    selectedInscriptionId,
    selectedAttendanceQrCode,
    onGroupFilterChange,
    onCategoryFilterChange,
    resolveRouteFromClick,
    onAttendanceQrModalToggle,
    onCancelModal,
    onSuccessModal,
} = useInscriptionConfig(selectedYear, canExportInscriptions)
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

watch(() => route.query.inscription_year, async (value) => {
    const year = value ? String(value) : resolveDefaultYear()

    if (selectedYear.value !== year) {
        selectedYear.value = year
        await nextTick()
    }

    reloadTable()
}, { flush: 'post' })

watch(selectedYear, async (year) => {
    const normalizedYear = String(year)

    if (String(route.query.inscription_year || '') === normalizedYear) {
        return
    }

    await router.replace({
        name: 'inscriptions',
        query: {
            ...route.query,
            inscription_year: normalizedYear,
        },
    })
})

onMounted(async () => {
    await settings.getSettings()
})
</script>

<style scoped>
[data-tour="inscriptions-table"] :deep(.inscription-actions-menu.show) {
    inset: auto 0 auto auto !important;
    transform: none !important;
}
</style>
