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

                    <div class="col-sm-6 col-md-4 col-lg-3 px-0 pe-sm-3">
                        <select
                            id="pre_inscription"
                            name="pre_inscription"
                            class="form-select form-select-sm"
                            aria-label="Filtrar por estado de inscripción"
                            @change="onPreInscriptionFilterChange"
                        >
                            <option value="">Todas</option>
                            <option value="1">Solo preinscritos</option>
                            <option value="0">Solo inscritos</option>
                        </select>
                    </div>

                    <div v-if="canExportInscriptions" class="col-sm-auto px-0 pe-sm-3" data-tour="inscription-export">
                        <a :href="exportExcelUrl" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                            <i class="far fa-file-excel me-2"></i>
                            Exportar Excel
                        </a>
                    </div>

                    <div v-if="canManageSelectedYear" class="col-sm-auto px-0">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            :disabled="inscriptionLimit.is_full"
                            @click="triggerCreateModal"
                        >
                            <i class="fa fa-plus me-2"></i>
                            Nueva inscripción
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-start justify-content-lg-end">
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>

            <div class="inscription-limit-banner mb-3" :class="{ 'is-full': inscriptionLimit.is_full }">
                <div>
                    <strong>
                        Inscripciones {{ inscriptionLimit.current }} / {{ inscriptionLimit.limit }}
                    </strong>
                    <span class="text-muted ms-2">
                        Año {{ inscriptionLimit.year || selectedYear }}
                    </span>
                </div>
                <span class="badge" :class="inscriptionLimit.is_full ? 'bg-danger' : 'bg-success'">
                    {{ inscriptionLimit.is_full ? 'Cupo completo' : `Disponibles ${inscriptionLimit.remaining}` }}
                </span>
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
                                        <option v-for="group in filterTrainingGroups" :value="group.id" :key="group.id">
                                            {{ group.name }}{{ group.is_complementary ? ' (Complementario)' : '' }}
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
                                <th></th>
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

    <ModalInscription
        :inscription_id="selectedInscriptionId"
        :create_open="isCreateModalOpen"
        :selected_year="selectedYear"
        @success="onSuccessModal"
        @cancel="onCancelModal"/>
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
import api from '@/utils/axios';
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue';
import AttendanceQrModal from '@/components/attendances/AttendanceQrModal.vue'
import { usePageTutorial } from '@/composables/usePageTutorial';
import { usePageTitle } from "@/composables/use-meta";
import { inscriptionsTutorial } from '@/tutorials/inscriptions';
import ModalInscription from './ModalInscription.vue';
import { SCHOOL_PERMISSION_KEYS } from '@/config/school-permissions';

usePageTitle('Inscripciones')

const route = useRoute()
const router = useRouter()
const settings = useSetting()
const auth = useAuthUser()
const currentYear = String(new Date().getFullYear())
const yearOptions = computed(() => settings.inscription_years || [])
const filterTrainingGroups = computed(() => {
    const groups = [
        ...(settings.normal_training_groups || []),
        ...(settings.complementary_training_groups || []),
    ]

    const source = groups.length ? groups : (settings.all_groups || [])
    const uniqueGroups = new Map()

    source.forEach((group) => {
        if (group?.id) {
            uniqueGroups.set(String(group.id), group)
        }
    })

    return Array.from(uniqueGroups.values())
})
const canExportInscriptions = computed(() => auth.hasAnyRole(['super-admin', 'school']))
const selectedYear = ref(String(route.query.inscription_year || currentYear))
const exportExcelUrl = computed(() => `/export/inscriptions/excel?inscription_year=${encodeURIComponent(selectedYear.value || currentYear)}`)
const canManageSelectedYear = computed(() => canExportInscriptions.value && Number(selectedYear.value || currentYear) >= Number(currentYear))
const canCreateInvoice = computed(() => canManageSelectedYear.value && auth.hasSchoolPermission(SCHOOL_PERMISSION_KEYS.billing))
const inscriptionLimit = ref({
    year: Number(selectedYear.value || currentYear),
    current: 0,
    limit: 200,
    remaining: 200,
    is_full: false,
})

const loadLimitSummary = async () => {
    try {
        const { data } = await api.get('/api/v2/inscriptions/limit-summary', {
            params: {
                year: selectedYear.value || currentYear,
            },
        })

        inscriptionLimit.value = {
            year: Number(data.year ?? selectedYear.value ?? currentYear),
            current: Number(data.current ?? 0),
            limit: Number(data.limit ?? 200),
            remaining: Number(data.remaining ?? 0),
            is_full: Boolean(data.is_full),
        }
    } catch (error) {
        inscriptionLimit.value = {
            year: Number(selectedYear.value || currentYear),
            current: 0,
            limit: 200,
            remaining: 200,
            is_full: false,
        }
    }
}
const {
    inscription_table,
    options,
    reloadTable,
    selectedInscriptionId,
    isCreateModalOpen,
    selectedAttendanceQrCode,
    triggerCreateModal,
    onGroupFilterChange,
    onCategoryFilterChange,
    onPreInscriptionFilterChange,
    resolveRouteFromClick,
    onAttendanceQrModalToggle,
    onCancelModal,
    onSuccessModal,
} = useInscriptionConfig(selectedYear, canExportInscriptions, loadLimitSummary, canCreateInvoice)
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
    loadLimitSummary()
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
    await loadLimitSummary()
})
</script>

<style scoped>
[data-tour="inscriptions-table"] {
    min-width: 0;
}

[data-tour="inscriptions-table"] :deep(.inscription-actions-menu.show) {
    inset: auto 0 auto auto !important;
    transform: none !important;
}

.inscription-limit-banner {
    align-items: center;
    background: #f4fbf7;
    border: 1px solid #bde7cd;
    border-radius: 8px;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 12px 16px;
}

.inscription-limit-banner.is-full {
    background: #fff5f5;
    border-color: #f3b8b8;
}

@media (max-width: 575.98px) {
    .inscription-limit-banner {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
