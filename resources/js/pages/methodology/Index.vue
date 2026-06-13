<template>
    <panel>
        <template #header>
            <div class="row g-3 align-items-center">
                <div class="col-md-auto">
                    <button type="button" class="btn btn-primary" @click="openCreate">
                        Nuevo registro
                    </button>
                </div>
                <div class="col">
                    <p class="mb-0">
                        Gestiona formatos metodológicos, planificaciones e informes por escuela.
                    </p>
                </div>
            </div>
        </template>

        <template #body>
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li v-for="tab in methodologyTabs" :key="tab.type" class="nav-item" role="presentation">
                    <button
                        type="button"
                        class="nav-link"
                        :class="{ active: activeType === tab.type }"
                        @click="selectType(tab.type)"
                    >
                        {{ tab.label }}
                    </button>
                </li>
            </ul>

            <DatatableTemplate
                ref="table"
                id="methodology-records-table"
                :options="options"
            >
                <template #actions="props">
                    <div class="d-flex justify-content-center gap-1">
                        <a
                            :href="props.rowData.export_pdf_url"
                            target="_blank"
                            class="btn btn-info btn-sm"
                            title="Exportar PDF"
                        >
                            <i class="fa-solid fa-file-pdf fa-width-auto me-2" aria-hidden="true"></i>
                        </a>
                        <button
                            type="button"
                            class="btn btn-warning btn-sm"
                            title="Editar"
                            @click="openEdit(props.rowData.id)"
                        >
                            <i class="fa fa-edit fa-width-auto me-2" aria-hidden="true"></i>
                        </button>
                    </div>
                </template>
            </DatatableTemplate>
        </template>
    </panel>

    <div
        ref="modalRef"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <form class="modal-content" @submit.prevent="saveRecord">
                <div class="modal-header">
                    <h5 class="modal-title">{{ selectedId ? 'Editar' : 'Crear' }} {{ activeTab.label }}</h5>
                    <button type="button" class="btn-close" aria-label="Cerrar" @click="closeModal"></button>
                </div>

                <div class="modal-body">
                    <div v-if="formError" class="alert alert-danger" role="alert">
                        {{ formError }}
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label" for="methodology-title">Título</label>
                            <input
                                id="methodology-title"
                                v-model.trim="form.title"
                                type="text"
                                class="form-control"
                                required
                            >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="methodology-group">Grupo de entrenamiento</label>
                            <select id="methodology-group" v-model="form.training_group_id" class="form-select">
                                <option :value="null">Sin grupo</option>
                                <option v-for="group in groupOptions" :key="group.id" :value="group.id">
                                    {{ group.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <section v-if="isCharacterization" class="methodology-section">
                        <h6>Ficha técnica de caracterización</h6>
                        <div class="characterization-form">
                            <div class="characterization-grid two-cols">
                                <label>Categoría</label>
                                <input v-model="form.fields.category" type="text" class="form-control">
                                <label>Año-semestre</label>
                                <input v-model="form.fields.year_semester" type="text" class="form-control">
                                <label>Grupo etario</label>
                                <input v-model="form.fields.age_group" type="text" class="form-control">
                                <label>Competencias 2026</label>
                                <input v-model="form.fields.competitions" type="text" class="form-control">
                            </div>

                            <div class="characterization-grid objective-grid">
                                <label>Objetivos deportivos 2026 (entrenador)</label>
                                <textarea v-model="form.fields.sport_objectives" class="form-control" rows="3"></textarea>
                                <label>Objetivos formativos de la categoría año 2026</label>
                                <textarea v-model="form.fields.formative_objectives" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="characterization-heading">Valores constitutivos de la categoría</div>
                            <textarea v-model="form.fields.constitutive_values" class="form-control" rows="3"></textarea>

                            <div class="characterization-heading">Idiosincracia de la categoría</div>
                            <div class="row g-3">
                                <div v-for="field in characterizationIdentityFields" :key="field.key" class="col-md-3">
                                    <label class="form-label">{{ field.label }}</label>
                                    <textarea v-model="form.fields[field.key]" class="form-control" rows="4"></textarea>
                                </div>
                            </div>

                            <div class="characterization-heading">Reglamento interno de la categoría</div>
                            <textarea v-model="form.fields.internal_rules" class="form-control" rows="3"></textarea>

                            <div class="characterization-heading">Prescripción médica de jugadores</div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle methodology-inline-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Nombre del jugador</th>
                                            <th>Condición</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in 3" :key="`medical-${row}`">
                                            <td>{{ row }}</td>
                                            <td>
                                                <input v-model="form.fields[`medical_prescription_player_${row}_name`]" type="text" class="form-control">
                                            </td>
                                            <td>
                                                <input v-model="form.fields[`medical_prescription_player_${row}_condition`]" type="text" class="form-control">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="characterization-heading">Jugadores con proyección</div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle methodology-inline-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Nombre del jugador</th>
                                            <th>Cualidades</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in 3" :key="`projection-${row}`">
                                            <td>{{ row }}</td>
                                            <td>
                                                <input v-model="form.fields[`projection_player_${row}_name`]" type="text" class="form-control">
                                            </td>
                                            <td>
                                                <input v-model="form.fields[`projection_player_${row}_qualities`]" type="text" class="form-control">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section v-if="isCategoryMonthlyReport" class="methodology-section">
                        <h6>Informe mensual categoría</h6>
                        <div class="category-report-form">
                            <div class="characterization-grid objective-grid">
                                <label>Entrenador</label>
                                <input v-model="form.fields.coach" type="text" class="form-control">
                                <label>Categoría</label>
                                <input v-model="form.fields.category" type="text" class="form-control">
                                <label>Mes correspondiente al informe</label>
                                <input v-model="form.fields.report_month" type="text" class="form-control">
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle methodology-inline-table category-report-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Reporte</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in categoryReportRows" :key="row.number">
                                            <td>{{ row.number }}</td>
                                            <td>{{ row.report }}</td>
                                            <td>
                                                <textarea v-model="form.fields[row.key]" class="form-control" rows="4"></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </section>

                    <section v-if="isMonthlyReport" class="methodology-section">
                        <h6>Informe mensual</h6>
                        <div class="category-report-form">
                            <div class="characterization-grid objective-grid">
                                <label>Entrenador</label>
                                <input v-model="form.fields.coach" type="text" class="form-control">
                                <label>Categoría</label>
                                <input v-model="form.fields.category" type="text" class="form-control">
                                <label>Mes correspondiente al informe</label>
                                <input v-model="form.fields.report_month" type="text" class="form-control">
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle methodology-inline-table monthly-report-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Obligaciones del entrenador</th>
                                            <th>Actividad realizada</th>
                                            <th>Soporte</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in monthlyReportRows" :key="row.number">
                                            <td>{{ row.number }}</td>
                                            <td>{{ row.obligation }}</td>
                                            <td>
                                                <textarea v-model="form.fields[`coach_obligation_${row.number}_activity`]" class="form-control" rows="4"></textarea>
                                            </td>
                                            <td>
                                                <textarea v-model="form.fields[`coach_obligation_${row.number}_support`]" class="form-control" rows="4"></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </section>

                    <section v-if="isPlanning" class="methodology-section">
                        <h6>Formato de planificación</h6>
                        <div class="planning-form">
                            <div class="characterization-grid planning-header-grid">
                                <template v-for="field in planningHeaderFields" :key="field.key">
                                    <label :for="`field-${field.key}`">{{ field.label }}</label>
                                    <input
                                        :id="`field-${field.key}`"
                                        v-model="form.fields[field.key]"
                                        type="text"
                                        class="form-control"
                                    >
                                </template>
                                <label for="field-objective">Objetivo</label>
                                <textarea
                                    id="field-objective"
                                    v-model="form.fields.objective"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle methodology-inline-table planning-structures-table">
                                    <thead>
                                        <tr>
                                            <th>Estructura preferente</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="field in planningStructureFields" :key="field.key">
                                            <td>{{ field.label }}</td>
                                            <td>
                                                <textarea
                                                    v-model="form.fields[field.key]"
                                                    class="form-control"
                                                    rows="3"
                                                ></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section v-for="group in fieldGroups" :key="group.title" class="methodology-section">
                        <h6>{{ group.title }}</h6>
                        <div class="row g-3">
                            <div
                                v-for="field in group.fields"
                                :key="field.key"
                                :class="field.wide ? 'col-12' : 'col-md-6'"
                            >
                                <label class="form-label" :for="`field-${field.key}`">{{ field.label }}</label>
                                <textarea
                                    v-if="field.type === 'textarea'"
                                    :id="`field-${field.key}`"
                                    v-model="form.fields[field.key]"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                                <input
                                    v-else
                                    :id="`field-${field.key}`"
                                    v-model="form.fields[field.key]"
                                    type="text"
                                    class="form-control"
                                >
                            </div>
                        </div>
                    </section>

                    <section v-if="isPlanning" class="methodology-section">
                        <h6>Fases de planificación</h6>
                        <div class="planning-phase-list">
                            <div v-for="phase in planningFieldPhaseSections" :key="phase.key" class="planning-phase-row">
                                <div class="planning-phase-title">{{ phase.label }}</div>
                                <div class="planning-phase-field">
                                    <SoccerFieldDiagramEditor v-model="form.diagrams[phase.key]" />
                                </div>
                                <div class="planning-phase-fields">
                                    <div class="planning-cell-grid planning-time-grid">
                                        <label :for="`field-${phase.timeKey}`">Tiempo</label>
                                        <input
                                            :id="`field-${phase.timeKey}`"
                                            v-model="form.fields[phase.timeKey]"
                                            type="text"
                                            class="form-control"
                                        >
                                    </div>
                                    <div class="planning-cell-grid">
                                        <label :for="`field-${phase.dosageKey}`">Dosificación</label>
                                        <textarea
                                            :id="`field-${phase.dosageKey}`"
                                            v-model="form.fields[phase.dosageKey]"
                                            class="form-control"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                    <div class="planning-cell-grid">
                                        <label :for="`field-${phase.descriptionKey}`">Descripción</label>
                                        <textarea
                                            :id="`field-${phase.descriptionKey}`"
                                            v-model="form.fields[phase.descriptionKey]"
                                            class="form-control"
                                            rows="7"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="planning-final-phase-row">
                                <div class="planning-phase-title">Fase final</div>
                                <div class="planning-final-fields">
                                    <div class="planning-cell-grid planning-time-grid">
                                        <label for="field-final_phase_time">Tiempo</label>
                                        <input
                                            id="field-final_phase_time"
                                            v-model="form.fields.final_phase_time"
                                            type="text"
                                            class="form-control"
                                        >
                                    </div>
                                    <div class="planning-cell-grid">
                                        <label for="field-final_phase_dosage">Dosificación</label>
                                        <textarea
                                            id="field-final_phase_dosage"
                                            v-model="form.fields.final_phase_dosage"
                                            class="form-control"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                    <div class="planning-cell-grid">
                                        <label for="field-final_phase_description">Descripción</label>
                                        <textarea
                                            id="field-final_phase_description"
                                            v-model="form.fields.final_phase_description"
                                            class="form-control"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="isPlanning" class="methodology-section">
                        <h6>Cierre</h6>
                        <div class="planning-closing-grid">
                            <div v-for="field in closingFields" :key="field.key" class="planning-closing-cell">
                                <label :for="`field-${field.key}`">{{ field.label }}</label>
                                <textarea
                                    :id="`field-${field.key}`"
                                    v-model="form.fields[field.key]"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="closeModal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="isSaving">
                        {{ isSaving ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <breadcrumb :parent="'Plataforma'" :current="'Metodología'" />
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, useTemplateRef } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import SoccerFieldDiagramEditor from './SoccerFieldDiagramEditor.vue'
import {
    METHODOLOGY_TYPES,
    createBlankDiagrams,
    createBlankFields,
    getTabByType,
    methodologyFieldGroups,
    methodologyTabs,
} from './methodology-form-definitions'

usePageTitle('Metodología')

const modalRef = ref(null)
const modalInstance = ref(null)
const table = useTemplateRef('table')
const activeType = ref(METHODOLOGY_TYPES.planning)
const groupOptions = ref([])
const isSaving = ref(false)
const selectedId = ref(null)
const formError = ref('')

const form = reactive({
    title: '',
    training_group_id: null,
    fields: createBlankFields(activeType.value),
    diagrams: createBlankDiagrams(),
})

const activeTab = computed(() => getTabByType(activeType.value))
const fieldGroups = computed(() => {
    const groups = methodologyFieldGroups[activeType.value] ?? []

    if (isPlanning.value) {
        return []
    }

    return isCharacterization.value || isCategoryMonthlyReport.value || isMonthlyReport.value ? [] : groups
})
const isPlanning = computed(() => activeType.value === METHODOLOGY_TYPES.planning)
const isCharacterization = computed(() => activeType.value === METHODOLOGY_TYPES.characterizationSheet)
const isMonthlyReport = computed(() => activeType.value === METHODOLOGY_TYPES.monthlyReport)
const isCategoryMonthlyReport = computed(() => activeType.value === METHODOLOGY_TYPES.categoryMonthlyReport)

const planningHeaderFields = [
    { key: 'category', label: 'Categoría' },
    { key: 'coach', label: 'Entrenador' },
    { key: 'session', label: 'Sesión' },
]

const planningStructureFields = [
    { key: 'coordinative', label: 'Coordinativa' },
    { key: 'cognitive', label: 'Cognitiva' },
    { key: 'conditional', label: 'Condicional' },
    { key: 'emotional_volitional', label: 'Emotivo-volitiva' },
]

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const columns = [
    { data: 'title', title: 'Título', name: 'title' },
    { data: 'creator_name', title: 'Creado por', name: 'creator_name' },
    { data: 'training_group_name', title: 'Grupo', name: 'training_group_name' },
    { data: 'created_at', title: 'Creado', name: 'created_at' },
    { data: 'id', title: 'Opciones', searchable: false, orderable: false, render: '#actions' },
]

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
    pageLength: 10,
    processing: true,
    serverSide: true,
    deferRender: true,
    searchDelay: 400,
    order: [[3, 'desc']],
    ajax: async (data, callback) => {
        try {
            const response = await api.get('/api/v2/datatables/methodology_records', {
                params: {
                    ...data,
                    type: activeType.value,
                },
            })

            callback({
                draw: data.draw,
                data: response.data.data ?? [],
                recordsTotal: response.data.recordsTotal ?? 0,
                recordsFiltered: response.data.recordsFiltered ?? 0,
            })
        } catch {
            callback(emptyDataTableResponse(data.draw))
        }
    },
    columns,
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        {
            targets: '_all',
            className: 'dt-head-center dt-body-center',
        },
    ],
}

const closingFields = computed(() => (
    methodologyFieldGroups[METHODOLOGY_TYPES.planning]
        .find((group) => group.title === 'Cierre')
        ?.fields ?? []
))

const characterizationIdentityFields = [
    { key: 'tactical_schemes', label: 'Esquemas tácticos habituales' },
    { key: 'game_model', label: 'Modelo de juego' },
    { key: 'offensive_defensive_principles', label: 'Principios ofensivos y defensivos trabajados' },
    { key: 'priority_technical_elements', label: 'Elementos técnicos prioritarios' },
]

const categoryReportRows = [
    {
        number: 1,
        report: 'Objetivos planteados en el mes en curso.',
        key: 'monthly_objectives_description',
    },
    {
        number: 2,
        report: 'Logros obtenidos en el mes en curso.',
        key: 'monthly_achievements_description',
    },
    {
        number: 3,
        report: 'Dificultades presentadas en el mes en curso.',
        key: 'monthly_difficulties_description',
    },
    {
        number: 4,
        report: 'Valores deportivos abordados',
        key: 'sport_values_description',
    },
    {
        number: 5,
        report: 'Situaciones o novedades específicas con jugadores. (enfermedad, incapacidad, lesión, evolución deportiva o entre otras).',
        key: 'specific_player_news_description',
    },
    {
        number: 6,
        report: 'Seguimiento y/o control que se llevó o se está llevando a cabo con el jugador.',
        key: 'player_follow_up_description',
    },
]

const monthlyReportRows = [
    {
        number: 1,
        obligation: 'Planear las sesiones de entrenamiento del mes en curso (plazo específico).',
    },
    {
        number: 2,
        obligation: 'Registrar diariamente la asistencia de la categoría a cargo.',
    },
    {
        number: 3,
        obligation: 'Realizar el debido seguimiento a los jugadores que no asistieron en el mes.',
    },
    {
        number: 4,
        obligation: 'Realizar seguimiento al jugador que ingresa a la categoría desde la clase de cortesía hasta llevar a cabo el proceso de inscripción.',
    },
    {
        number: 5,
        obligation: 'Actualización diaria de la base de datos de los jugadores.',
    },
    {
        number: 6,
        obligation: 'Actualización constante de los grupos de whatsapp, jugador que ingrese y jugador que se retire.',
    },
    {
        number: 7,
        obligation: 'Asistencia a las reuniones y capacitaciones programadas.',
    },
]

const planningFieldPhaseSections = [
    {
        key: 'initial_phase',
        label: 'Fase inicial',
        timeKey: 'initial_phase_time',
        dosageKey: 'initial_phase_dosage',
        descriptionKey: 'initial_phase_description',
    },
    {
        key: 'central_phase_one',
        label: 'Fase central 1',
        timeKey: 'central_phase_one_time',
        dosageKey: 'central_phase_one_dosage',
        descriptionKey: 'central_phase_one_description',
    },
    {
        key: 'central_phase_two',
        label: 'Fase central 2',
        timeKey: 'central_phase_two_time',
        dosageKey: 'central_phase_two_dosage',
        descriptionKey: 'central_phase_two_description',
    },
    {
        key: 'central_phase_three',
        label: 'Fase central 3',
        timeKey: 'central_phase_three_time',
        dosageKey: 'central_phase_three_dosage',
        descriptionKey: 'central_phase_three_description',
    },
]

function resetForm(record = null) {
    const tab = getTabByType(activeType.value)

    form.title = record?.title ?? tab.title
    form.training_group_id = record?.training_group_id ?? null
    form.fields = {
        ...createBlankFields(activeType.value),
        ...(record?.fields ?? {}),
    }
    form.diagrams = {
        ...createBlankDiagrams(),
        ...(record?.diagrams ?? {}),
    }
}

async function loadGroups() {
    try {
        const response = await api.get('/api/v2/training_groups')
        groupOptions.value = (response.data.data ?? []).map((group) => ({
            id: group.id,
            name: group.name ?? group.full_group ?? `Grupo ${group.id}`,
        }))
    } catch {
        groupOptions.value = []
    }
}

async function selectType(type) {
    activeType.value = type
    selectedId.value = null
    resetForm()
    await nextTick()
    reloadTable(true)
}

async function openCreate() {
    selectedId.value = null
    formError.value = ''
    resetForm()
    await nextTick()
    modalInstance.value?.show()
}

async function openEdit(id) {
    selectedId.value = id
    formError.value = ''

    try {
        const response = await api.get(`/api/v2/methodology-records/${id}`)
        resetForm(response.data.data)
        await nextTick()
        modalInstance.value?.show()
    } catch (error) {
        formError.value = error.response?.data?.message || 'No fue posible cargar el registro.'
    }
}

function closeModal() {
    modalInstance.value?.hide()
}

function normalizeFields(fields) {
    return Object.fromEntries(
        Object.entries(fields).map(([key, value]) => [key, value === null || value === undefined ? null : String(value).trim()])
    )
}

async function saveRecord() {
    isSaving.value = true
    formError.value = ''

    const payload = {
        training_group_id: form.training_group_id ? Number(form.training_group_id) : null,
        type: activeType.value,
        title: form.title,
        fields: normalizeFields(form.fields),
        diagrams: isPlanning.value ? form.diagrams : null,
    }

    try {
        if (selectedId.value) {
            await api.put(`/api/v2/methodology-records/${selectedId.value}`, payload)
        } else {
            await api.post('/api/v2/methodology-records', payload)
        }

        closeModal()
        reloadTable()
    } catch (error) {
        const errors = error.response?.data?.errors
        formError.value = errors
            ? Object.values(errors).flat().join(' ')
            : error.response?.data?.message || 'No fue posible guardar el registro.'
    } finally {
        isSaving.value = false
    }
}

onMounted(async () => {
    modalInstance.value = new window.bootstrap.Modal(modalRef.value, {
        backdrop: 'static',
        keyboard: false,
    })

    resetForm()
    await loadGroups()
})

function reloadTable(resetPaging = false) {
    const dt = table.value?.table?.dt

    if (dt) {
        dt.ajax.reload(null, resetPaging)
    }
}
</script>

<style scoped>
.methodology-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    color: #64748b;
    padding: 2rem;
    text-align: center;
}

.methodology-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
    margin-top: 1rem;
}

.methodology-section > h6,
.planning-phase-title {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.planning-form {
    display: grid;
    gap: 1rem;
}

.planning-header-grid {
    grid-template-columns: 150px 1fr 150px 1fr;
}

.planning-header-grid label[for="field-objective"] {
    grid-column: 1;
}

.planning-header-grid textarea#field-objective {
    grid-column: 2 / -1;
}

.planning-structures-table th:first-child,
.planning-structures-table td:first-child {
    width: 220px;
}

.planning-phase-list {
    display: grid;
    gap: 1rem;
}

.planning-phase-row {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    display: grid;
    grid-template-columns: minmax(300px, 0.95fr) minmax(280px, 1.05fr);
    overflow: hidden;
}

.planning-final-phase-row {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
}

.planning-phase-title {
    background: #d9dee7;
    border-bottom: 1px solid #e2e8f0;
    grid-column: 1 / -1;
    font-weight: 700;
    margin-bottom: 0;
    padding: 0.6rem 0.75rem;
    text-align: center;
}

.planning-phase-field {
    border-right: 1px solid #e2e8f0;
    padding: 0.75rem;
}

.planning-phase-fields {
    display: grid;
    grid-template-rows: auto 1fr 1.4fr;
}

.planning-final-fields {
    display: grid;
    grid-template-columns: minmax(160px, 0.5fr) minmax(240px, 1fr) minmax(260px, 1.2fr);
}

.planning-cell-grid {
    display: grid;
    grid-template-rows: auto 1fr;
}

.planning-cell-grid label,
.planning-closing-cell label {
    background: #d9dee7;
    border-bottom: 1px solid #d8dee8;
    font-weight: 700;
    padding: 0.55rem;
    text-align: center;
}

.planning-phase-fields .planning-cell-grid:not(:last-child) {
    border-bottom: 1px solid #d8dee8;
}

.planning-final-fields .planning-cell-grid:not(:last-child),
.planning-closing-cell:not(:last-child) {
    border-right: 1px solid #d8dee8;
}

.planning-cell-grid .form-control,
.planning-closing-cell .form-control {
    border: 0;
    border-radius: 0;
}

.planning-time-grid .form-control {
    min-height: 44px;
}

.planning-closing-grid {
    border: 1px solid #d8dee8;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

.planning-closing-cell textarea {
    min-height: 110px;
}

.characterization-form {
    display: grid;
    gap: 1rem;
}

.category-report-form {
    display: grid;
    gap: 1rem;
}

.characterization-grid {
    border: 1px solid #d8dee8;
    display: grid;
}

.characterization-grid.two-cols {
    grid-template-columns: 170px 1fr 170px 1fr;
}

.characterization-grid.objective-grid {
    grid-template-columns: 230px 1fr;
}

.characterization-grid.signature-grid {
    grid-template-columns: 140px 1fr;
}

.characterization-grid label,
.characterization-heading {
    background: #d9dee7;
    font-weight: 700;
}

.characterization-grid label,
.characterization-grid input,
.characterization-grid textarea,
.characterization-heading {
    border: 0;
    border-bottom: 1px solid #d8dee8;
    border-radius: 0;
    padding: 0.55rem;
}

.characterization-grid label,
.characterization-grid input,
.characterization-grid textarea {
    border-right: 1px solid #d8dee8;
}

.characterization-heading {
    text-align: center;
}

.methodology-inline-table th {
    background: #d9dee7;
    text-align: center;
}

.methodology-inline-table td:first-child,
.methodology-inline-table th:first-child {
    text-align: center;
    width: 60px;
}

.category-report-table th:nth-child(2) {
    width: 45%;
}

.category-report-table th:nth-child(3) {
    width: 50%;
}

.monthly-report-table th:nth-child(2) {
    width: 38%;
}

.monthly-report-table th:nth-child(3) {
    width: 32%;
}

.monthly-report-table th:nth-child(4) {
    width: 25%;
}

@media (max-width: 991.98px) {
    .planning-phase-row {
        grid-template-columns: 1fr;
    }

    .planning-final-fields,
    .planning-closing-grid,
    .planning-header-grid,
    .characterization-grid.two-cols,
    .characterization-grid.objective-grid,
    .characterization-grid.signature-grid {
        grid-template-columns: 1fr;
    }

    .planning-header-grid label[for="field-objective"],
    .planning-header-grid textarea#field-objective {
        grid-column: auto;
    }

    .planning-phase-field,
    .planning-final-fields .planning-cell-grid:not(:last-child),
    .planning-closing-cell:not(:last-child) {
        border-right: 0;
    }

    .planning-final-fields .planning-cell-grid:not(:last-child),
    .planning-closing-cell:not(:last-child) {
        border-bottom: 1px solid #d8dee8;
    }
}
</style>
