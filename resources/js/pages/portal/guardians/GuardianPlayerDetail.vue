<template>
    <section class="position-relative guardian-player-detail">
        <Loader :is-loading="loading" loading-text="Cargando información del deportista..." />

        <div v-if="errorMessage && !player" class="alert alert-danger" role="alert">
            {{ errorMessage }}
        </div>

        <div v-else-if="player" class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 p-lg-5 guardian-player-detail__hero">
                        <div class="row align-items-center g-4">
                            <div class="col-12 col-lg">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <img :src="player.photo_url" :alt="player.full_names" class="guardian-player-detail__photo">
                                    <div>
                                        <p class="text-uppercase fw-semibold small mb-2">Jugador vigente</p>
                                        <h1 class="h2 mb-1">{{ player.full_names }}</h1>
                                        <p class="mb-0">
                                            {{ player.school_data?.name || 'Escuela' }}
                                            <span v-if="currentInscription?.training_group?.name">· {{ currentInscription.training_group.name }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-auto">
                                <div class="d-flex flex-wrap gap-2">
                                    <router-link :to="{ name: 'guardian-dashboard' }" class="btn btn-light">
                                        Volver
                                    </router-link>
                                    <button
                                        v-if="currentInscription?.report_url"
                                        type="button"
                                        class="btn btn-primary"
                                        @click="openUrl(currentInscription.report_url)"
                                    >
                                        Descargar inscripción
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="errorMessage" class="col-12">
                <div class="alert alert-danger mb-0" role="alert">
                    {{ errorMessage }}
                </div>
            </div>

            <div v-if="successMessage" class="col-12">
                <div class="alert alert-success mb-0" role="alert">
                    {{ successMessage }}
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
                            <div>
                                <h2 class="h4 mb-1">Datos del deportista</h2>
                                <p class="text-muted mb-0">Actualiza la información básica y de contacto.</p>
                            </div>
                            <span class="badge guardian-player-detail__badge guardian-player-detail__badge--neutral">Código {{ player.unique_code }}</span>
                        </div>

                        <form class="row g-3" @submit.prevent="submitPlayer">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nombres</label>
                                <input v-model.trim="form.names" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.names }" required>
                                <div v-if="fieldErrors.names" class="invalid-feedback d-block">{{ fieldErrors.names }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input v-model.trim="form.last_names" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.last_names }" required>
                                <div v-if="fieldErrors.last_names" class="invalid-feedback d-block">{{ fieldErrors.last_names }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input v-model="form.date_birth" type="date" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.date_birth }" required>
                                <div v-if="fieldErrors.date_birth" class="invalid-feedback d-block">{{ fieldErrors.date_birth }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Lugar de nacimiento</label>
                                <input v-model.trim="form.place_birth" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.place_birth }" required>
                                <div v-if="fieldErrors.place_birth" class="invalid-feedback d-block">{{ fieldErrors.place_birth }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Tipo de documento</label>
                                <select v-model="form.document_type" class="form-select form-select-sm" :class="{ 'is-invalid': fieldErrors.document_type }" required>
                                    <option value="">Selecciona...</option>
                                    <option v-for="option in documentTypeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <div v-if="fieldErrors.document_type" class="invalid-feedback d-block">{{ fieldErrors.document_type }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Documento</label>
                                <input :value="player.identification_document" type="text" class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Género</label>
                                <select v-model="form.gender" class="form-select form-select-sm" :class="{ 'is-invalid': fieldErrors.gender }" required>
                                    <option value="">Selecciona...</option>
                                    <option v-for="option in genderOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <div v-if="fieldErrors.gender" class="invalid-feedback d-block">{{ fieldErrors.gender }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input v-model.trim="form.email" type="email" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.email }">
                                <div v-if="fieldErrors.email" class="invalid-feedback d-block">{{ fieldErrors.email }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Celular</label>
                                <input v-model.trim="form.mobile" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.mobile }">
                                <div v-if="fieldErrors.mobile" class="invalid-feedback d-block">{{ fieldErrors.mobile }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Teléfonos</label>
                                <input v-model.trim="form.phones" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.phones }" required>
                                <div v-if="fieldErrors.phones" class="invalid-feedback d-block">{{ fieldErrors.phones }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Institución educativa</label>
                                <input v-model.trim="form.school" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.school }" required>
                                <div v-if="fieldErrors.school" class="invalid-feedback d-block">{{ fieldErrors.school }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Grado</label>
                                <select v-model="form.degree" class="form-select form-select-sm" :class="{ 'is-invalid': fieldErrors.degree }" required>
                                    <option value="">Selecciona...</option>
                                    <option v-for="option in degreeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <div v-if="fieldErrors.degree" class="invalid-feedback d-block">{{ fieldErrors.degree }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Jornada</label>
                                <select v-model="form.jornada" class="form-select form-select-sm" :class="{ 'is-invalid': fieldErrors.jornada }" required>
                                    <option value="">Selecciona...</option>
                                    <option v-for="option in jornadaOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <div v-if="fieldErrors.jornada" class="invalid-feedback d-block">{{ fieldErrors.jornada }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Seguro estudiantil</label>
                                <input v-model.trim="form.student_insurance" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.student_insurance }">
                                <div v-if="fieldErrors.student_insurance" class="invalid-feedback d-block">{{ fieldErrors.student_insurance }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Dirección</label>
                                <input v-model.trim="form.address" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.address }" required>
                                <div v-if="fieldErrors.address" class="invalid-feedback d-block">{{ fieldErrors.address }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Municipio</label>
                                <input v-model.trim="form.municipality" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.municipality }" required>
                                <div v-if="fieldErrors.municipality" class="invalid-feedback d-block">{{ fieldErrors.municipality }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Barrio</label>
                                <input v-model.trim="form.neighborhood" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.neighborhood }" required>
                                <div v-if="fieldErrors.neighborhood" class="invalid-feedback d-block">{{ fieldErrors.neighborhood }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">RH</label>
                                <select v-model="form.rh" class="form-select form-select-sm" :class="{ 'is-invalid': fieldErrors.rh }" required>
                                    <option value="">Selecciona...</option>
                                    <option v-for="option in bloodTypeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <div v-if="fieldErrors.rh" class="invalid-feedback d-block">{{ fieldErrors.rh }}</div>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label">EPS</label>
                                <input v-model.trim="form.eps" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.eps }" required>
                                <div v-if="fieldErrors.eps" class="invalid-feedback d-block">{{ fieldErrors.eps }}</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Antecedentes médicos</label>
                                <textarea v-model.trim="form.medical_history" rows="4" class="form-control form-control-sm" :class="{ 'is-invalid': fieldErrors.medical_history }"></textarea>
                                <div v-if="fieldErrors.medical_history" class="invalid-feedback d-block">{{ fieldErrors.medical_history }}</div>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="btn btn-primary" :disabled="saving">
                                    {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="d-flex flex-column gap-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Resumen actual</h2>
                            <div class="row g-3">
                                <div class="col-6" v-for="item in statsEntries" :key="item.key">
                                    <div class="guardian-player-detail__stat">
                                        <span class="guardian-player-detail__stat-label">{{ item.label }}</span>
                                        <strong>{{ item.value }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" v-if="currentInscription?.payments?.length">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Pagos</h2>

                            <div
                                v-if="currentInscription.payments.length > 1"
                                class="guardian-player-detail__payment-tabs mb-4"
                                role="tablist"
                                aria-label="Años de pagos"
                            >
                                <button
                                    v-for="payment in currentInscription.payments"
                                    :key="`payment-tab-${payment.id}`"
                                    type="button"
                                    class="guardian-player-detail__payment-tab"
                                    :class="{ 'guardian-player-detail__payment-tab--active': payment.id === activePaymentId }"
                                    @click="activePaymentId = payment.id"
                                >
                                    Año {{ payment.year }}
                                </button>
                            </div>

                            <div v-if="activePayment" class="guardian-player-detail__payment-panel">
                                <div class="d-flex justify-content-between gap-2 flex-wrap mb-3">
                                    <strong>Año {{ activePayment.year }}</strong>
                                    <span class="badge guardian-player-detail__badge guardian-player-detail__badge--secondary">
                                        {{ paidMonthsCount(activePayment) }} mes(es) al día
                                    </span>
                                </div>

                                <div class="row g-2">
                                    <div
                                        v-for="month in activePayment.months"
                                        :key="`${activePayment.id}-${month.field}`"
                                        class="col-12 col-sm-6"
                                    >
                                        <div
                                            class="guardian-player-detail__payment-month"
                                            :class="paymentMonthClass(month.value)"
                                        >
                                            <span>{{ month.label }}</span>
                                            <strong>{{ month.display }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" v-if="currentInscription?.attendance?.length">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Asistencias</h2>

                            <div class="guardian-player-detail__attendance-tabs mb-4" role="tablist" aria-label="Meses de asistencia">
                                <button
                                    v-for="assist in currentInscription.attendance"
                                    :key="`tab-${assist.id}`"
                                    type="button"
                                    class="guardian-player-detail__attendance-tab"
                                    :class="{ 'guardian-player-detail__attendance-tab--active': assist.id === activeAttendanceId }"
                                    @click="activeAttendanceId = assist.id"
                                >
                                    <span class="guardian-player-detail__attendance-tab-title">
                                        {{ assist.month }} {{ assist.year }}
                                    </span>
                                    <span class="badge guardian-player-detail__badge guardian-player-detail__badge--primary">
                                        {{ assist.percentage }}
                                    </span>
                                </button>
                            </div>

                            <div v-if="activeAttendance" class="guardian-player-detail__attendance-panel">
                                <div class="d-flex justify-content-between gap-2 flex-wrap mb-3">
                                    <strong>{{ activeAttendance.month }} {{ activeAttendance.year }}</strong>
                                    <span class="badge guardian-player-detail__badge guardian-player-detail__badge--primary">
                                        {{ activeAttendance.percentage }}
                                    </span>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <span
                                        v-for="register in activeAttendance.registers"
                                        :key="`${activeAttendance.id}-${register.class_number}`"
                                        class="badge rounded-pill guardian-player-detail__badge"
                                        :class="attendanceBadgeClass(register.status)"
                                        v-html="`${register.date} : ${register.label || 'Sin registro'}`"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" v-if="currentInscription?.evaluations?.length">
                        <div class="card-body">
                            <h2 class="h4 mb-3">Evaluaciones</h2>
                            <div v-for="evaluation in currentInscription.evaluations" :key="evaluation.id" class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between gap-2 flex-wrap">
                                    <div>
                                        <strong>{{ evaluation.period?.name || 'Período' }}</strong>
                                        <div class="text-muted small">
                                            {{ evaluation.evaluation_type || 'Evaluación' }}
                                            <span v-if="evaluation.overall_score !== null">· Puntaje {{ evaluation.overall_score }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="openUrl(evaluation.pdf_url)">
                                        Descargar PDF
                                    </button>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h3 class="h5 mb-3">Comparar períodos</h3>

                                <div v-if="currentInscription.comparison_periods.length < 2" class="text-muted mb-0">
                                    Se necesitan al menos dos evaluaciones para realizar el comparativo.
                                </div>

                                <template v-else>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">Período A</label>
                                            <select v-model="comparisonForm.period_a_id" class="form-select form-select-sm">
                                                <option value="">Selecciona...</option>
                                                <option v-for="period in currentInscription.comparison_periods" :key="`a-${period.id}`" :value="String(period.id)">
                                                    {{ period.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">Período B</label>
                                            <select v-model="comparisonForm.period_b_id" class="form-select form-select-sm">
                                                <option value="">Selecciona...</option>
                                                <option v-for="period in currentInscription.comparison_periods" :key="`b-${period.id}`" :value="String(period.id)">
                                                    {{ period.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2 d-grid">
                                            <button type="button" class="btn btn-primary" :disabled="comparisonLoading" @click="loadComparison">
                                                {{ comparisonLoading ? '...' : 'Comparar' }}
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="comparisonError" class="alert alert-danger mt-3 mb-0" role="alert">
                                        {{ comparisonError }}
                                    </div>

                                    <div v-if="comparison" class="mt-4">
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between gap-2 flex-wrap">
                                                    <div>
                                                        <div class="small text-muted">{{ comparison.period_a?.period_name }}</div>
                                                        <strong>{{ displayScore(comparison.overall?.period_a_score) }}</strong>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="small text-muted">Variación</div>
                                                            <span class="badge rounded-pill guardian-player-detail__badge" :class="trendBadgeClass(comparison.overall?.trend)">
                                                                {{ displayDelta(comparison.overall?.delta) }}
                                                            </span>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="small text-muted">{{ comparison.period_b?.period_name }}</div>
                                                        <strong>{{ displayScore(comparison.overall?.period_b_score) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="comparison.dimensions?.length" class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Dimensión</th>
                                                        <th>{{ comparison.period_a?.period_code || 'A' }}</th>
                                                        <th>{{ comparison.period_b?.period_code || 'B' }}</th>
                                                        <th>Variación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="dimension in comparison.dimensions" :key="dimension.dimension">
                                                        <td>{{ dimension.dimension }}</td>
                                                        <td>{{ displayScore(dimension.period_a_score) }}</td>
                                                        <td>{{ displayScore(dimension.period_b_score) }}</td>
                                                        <td>
                                                            <span class="badge rounded-pill guardian-player-detail__badge" :class="trendBadgeClass(dimension.trend)">
                                                                {{ displayDelta(dimension.delta) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import Loader from '@/components/general/Loader.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';

const route = useRoute();
const loading = ref(true);
const saving = ref(false);
const comparisonLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const comparisonError = ref('');
const fieldErrors = ref({});
const activePaymentId = ref(null);
const activeAttendanceId = ref(null);
const player = ref(null);
const comparison = ref(null);

const form = reactive({
    names: '',
    last_names: '',
    date_birth: '',
    place_birth: '',
    document_type: '',
    gender: '',
    email: '',
    mobile: '',
    phones: '',
    medical_history: '',
    school: '',
    degree: '',
    jornada: '',
    address: '',
    municipality: '',
    neighborhood: '',
    rh: '',
    eps: '',
    student_insurance: '',
});

const comparisonForm = reactive({
    period_a_id: '',
    period_b_id: '',
});

const genderOptions = [
    { value: 'M', label: 'Masculino' },
    { value: 'F', label: 'Femenino' },
];

const documentTypeOptions = [
    { value: 'Registro Civil', label: 'Registro Civil' },
    { value: 'Tarjeta de Indentidad', label: 'Tarjeta de identidad' },
    { value: 'Otro Documento', label: 'Otro documento' },
];

const bloodTypeOptions = [
    { value: 'O-', label: 'O-' },
    { value: 'O+', label: 'O+' },
    { value: 'A-', label: 'A-' },
    { value: 'A+', label: 'A+' },
    { value: 'B-', label: 'B-' },
    { value: 'B+', label: 'B+' },
    { value: 'AB-', label: 'AB-' },
    { value: 'AB+', label: 'AB+' },
];

const jornadaOptions = [
    { value: 'Mañana', label: 'Mañana' },
    { value: 'Tarde', label: 'Tarde' },
];

const degreeOptions = Array.from({ length: 12 }, (_, index) => ({
    value: String(index),
    label: String(index),
}));

const currentInscription = computed(() => player.value?.current_inscription ?? null);
const activePayment = computed(() => {
    const payments = currentInscription.value?.payments ?? [];

    if (!payments.length) {
        return null;
    }

    return payments.find((payment) => payment.id === activePaymentId.value) ?? payments[0];
});

const activeAttendance = computed(() => {
    const attendance = currentInscription.value?.attendance ?? [];

    if (!attendance.length) {
        return null;
    }

    return attendance.find((assist) => assist.id === activeAttendanceId.value) ?? attendance[0];
});

const statsEntries = computed(() => {
    const stats = currentInscription.value?.stats ?? {};
    const labels = {
        total_matches: 'Partidos',
        assistance: 'Asistencias',
        titular: 'Titular',
        played_approx: 'Minutos',
        played_approx_avg: 'Prom. minutos',
        goals: 'Goles',
        goals_avg: 'Prom. goles',
        yellow_cards: 'Amarillas',
        red_cards: 'Rojas',
        qualification: 'Calificación',
    };

    return Object.entries(labels).map(([key, label]) => ({
        key,
        label,
        value: stats[key] ?? 0,
    }));
});

usePageTitle(computed(() => player.value?.full_names ?? 'Detalle del jugador'));

const applyPlayer = (playerData) => {
    player.value = playerData;
    form.names = playerData?.names ?? '';
    form.last_names = playerData?.last_names ?? '';
    form.date_birth = playerData?.date_birth ?? '';
    form.place_birth = playerData?.place_birth ?? '';
    form.document_type = playerData?.document_type ?? '';
    form.gender = playerData?.gender ?? '';
    form.email = playerData?.email ?? '';
    form.mobile = playerData?.mobile ?? '';
    form.phones = playerData?.phones ?? '';
    form.medical_history = playerData?.medical_history ?? '';
    form.school = playerData?.school ?? '';
    form.degree = playerData?.degree ?? '';
    form.jornada = playerData?.jornada ?? '';
    form.address = playerData?.address ?? '';
    form.municipality = playerData?.municipality ?? '';
    form.neighborhood = playerData?.neighborhood ?? '';
    form.rh = playerData?.rh ?? '';
    form.eps = playerData?.eps ?? '';
    form.student_insurance = playerData?.student_insurance ?? '';

    const periods = playerData?.current_inscription?.comparison_periods ?? [];
    if (periods.length >= 2 && !comparisonForm.period_a_id && !comparisonForm.period_b_id) {
        comparisonForm.period_a_id = String(periods[0].id);
        comparisonForm.period_b_id = String(periods[1].id);
    }

    const payments = playerData?.current_inscription?.payments ?? [];

    if (!payments.length) {
        activePaymentId.value = null;
    } else {
        const hasCurrentPayment = payments.some((payment) => payment.id === activePaymentId.value);

        if (!hasCurrentPayment) {
            activePaymentId.value = payments[0].id;
        }
    }

    const attendance = playerData?.current_inscription?.attendance ?? [];

    if (!attendance.length) {
        activeAttendanceId.value = null;
        return;
    }

    const hasCurrentAttendance = attendance.some((assist) => assist.id === activeAttendanceId.value);

    if (!hasCurrentAttendance) {
        activeAttendanceId.value = attendance[0].id;
    }
};

const normalizeErrors = (error) => {
    const errors = error.response?.data?.errors ?? {};
    fieldErrors.value = Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
    );

    return error.response?.data?.message
        ?? Object.values(fieldErrors.value)[0]
        ?? 'No fue posible guardar los cambios.';
};

const fetchPlayer = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await api.get(`/api/v2/portal/acudientes/players/${route.params.id}`);
        applyPlayer(response.data?.data ?? response.data);
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar el detalle del jugador.';
    } finally {
        loading.value = false;
    }
};

const submitPlayer = async () => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    fieldErrors.value = {};

    try {
        const response = await api.put(`/api/v2/portal/acudientes/players/${route.params.id}`, { ...form });
        applyPlayer(response.data?.data ?? response.data);
        successMessage.value = response.data?.message || 'Datos del deportista actualizados correctamente.';
    } catch (error) {
        errorMessage.value = normalizeErrors(error);
    } finally {
        saving.value = false;
    }
};

const loadComparison = async () => {
    if (!currentInscription.value?.id || !comparisonForm.period_a_id || !comparisonForm.period_b_id) {
        comparisonError.value = 'Selecciona dos períodos distintos para comparar.';
        return;
    }

    comparisonLoading.value = true;
    comparisonError.value = '';

    try {
        const response = await api.get(`/api/v2/portal/acudientes/inscriptions/${currentInscription.value.id}/comparison`, {
            params: {
                period_a_id: comparisonForm.period_a_id,
                period_b_id: comparisonForm.period_b_id,
            },
        });

        comparison.value = response.data?.data ?? null;
    } catch (error) {
        comparisonError.value = error.response?.data?.message || 'No fue posible generar el comparativo.';
    } finally {
        comparisonLoading.value = false;
    }
};

const openUrl = (url) => {
    if (!url) {
        return;
    }

    window.open(url, '_blank', 'noopener');
};

const paidMonthsCount = (payment) => payment.months.filter((month) =>
    [1, 9, 10, 11, 12].includes(Number(month.value))
).length;

const paymentMonthClass = (value) => {
    const status = Number(value);

    if ([1, 9, 10, 11, 12, 8].includes(status)) {
        return 'guardian-player-detail__payment-month--success';
    }

    if ([3, 13].includes(status)) {
        return 'guardian-player-detail__payment-month--warning';
    }

    if (status === 2) {
        return 'guardian-player-detail__payment-month--danger';
    }

    if ([4, 5, 6, 7, 14].includes(status)) {
        return 'guardian-player-detail__payment-month--secondary';
    }

    return 'guardian-player-detail__payment-month--neutral';
};

const attendanceBadgeClass = (status) => {
    if (status === 'as') {
        return 'guardian-player-detail__badge--success';
    }

    if (status === 'fa' || status === 'fr') {
        return 'guardian-player-detail__badge--danger';
    }

    if (status === 'ex') {
        return 'guardian-player-detail__badge--warning';
    }

    return 'guardian-player-detail__badge--neutral';
};

const trendBadgeClass = (trend) => {
    if (trend === 'up') {
        return 'guardian-player-detail__badge--success';
    }

    if (trend === 'down') {
        return 'guardian-player-detail__badge--danger';
    }

    return 'guardian-player-detail__badge--secondary';
};

const displayScore = (value) => value ?? 'N/A';
const displayDelta = (value) => {
    if (value === null || value === undefined) {
        return 'Sin cambio';
    }

    const numericValue = Number(value);
    return `${numericValue > 0 ? '+' : ''}${numericValue}`;
};

onMounted(fetchPlayer);
</script>

<style scoped>
.guardian-player-detail__hero {
    background:
        linear-gradient(135deg, rgba(15, 28, 70, 0.96), rgba(35, 66, 138, 0.88)),
        #0f1c46;
    color: #fff;
}

.guardian-player-detail__hero p,
.guardian-player-detail__hero .small {
    color: rgba(255, 255, 255, 0.82);
}

.guardian-player-detail__photo {
    width: 96px;
    height: 96px;
    border-radius: 24px;
    object-fit: cover;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.16);
}

.guardian-player-detail__stat {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    background: #f6f8fc;
}

.guardian-player-detail__stat-label {
    font-size: 0.78rem;
    color: #5f6b85;
}

.guardian-player-detail__attendance-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.guardian-player-detail__attendance-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    border: 1px solid rgba(49, 82, 158, 0.16);
    background: #f7f9fd;
    color: #23304d;
    border-radius: 999px;
    padding: 0.55rem 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.guardian-player-detail__attendance-tab:hover {
    background: #eef3fb;
    border-color: rgba(49, 82, 158, 0.28);
}

.guardian-player-detail__attendance-tab--active {
    background: #0f1c46;
    color: #fff;
    border-color: #0f1c46;
}

.guardian-player-detail__attendance-tab--active .guardian-player-detail__badge--primary {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.18);
    color: #fff;
}

.guardian-player-detail__attendance-tab-title {
    white-space: nowrap;
}

.guardian-player-detail__attendance-panel {
    padding: 1rem;
    border-radius: 1rem;
    background: #f7f9fd;
    border: 1px solid rgba(35, 48, 77, 0.08);
}

.guardian-player-detail__payment-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.guardian-player-detail__payment-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid rgba(49, 82, 158, 0.16);
    background: #f7f9fd;
    color: #23304d;
    border-radius: 999px;
    padding: 0.55rem 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.guardian-player-detail__payment-tab:hover {
    background: #eef3fb;
    border-color: rgba(49, 82, 158, 0.28);
}

.guardian-player-detail__payment-tab--active {
    background: #0f1c46;
    color: #fff;
    border-color: #0f1c46;
}

.guardian-player-detail__payment-panel {
    padding: 1rem;
    border-radius: 1rem;
    background: #f7f9fd;
    border: 1px solid rgba(35, 48, 77, 0.08);
}

.guardian-player-detail__payment-month {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 52px;
    padding: 0.8rem 0.95rem;
    border-radius: 0.9rem;
    border: 1px solid transparent;
}

.guardian-player-detail__payment-month span {
    font-weight: 600;
}

.guardian-player-detail__payment-month strong {
    text-align: right;
}

.guardian-player-detail__payment-month--neutral {
    color: #23304d;
    background: #edf2f9;
    border-color: rgba(35, 48, 77, 0.08);
}

.guardian-player-detail__payment-month--success {
    color: #0f5132;
    background: #d1e7dd;
    border-color: #badbcc;
}

.guardian-player-detail__payment-month--warning {
    color: #664d03;
    background: #fff3cd;
    border-color: #ffecb5;
}

.guardian-player-detail__payment-month--danger {
    color: #842029;
    background: #f8d7da;
    border-color: #f1aeb5;
}

.guardian-player-detail__payment-month--secondary {
    color: #41464b;
    background: #e2e3e5;
    border-color: #d3d6d8;
}

.guardian-player-detail__badge {
    border: 1px solid transparent;
    font-weight: 600;
}

.guardian-player-detail__badge--neutral {
    color: #23304d;
    background: #edf2f9;
    border-color: rgba(35, 48, 77, 0.08);
}

.guardian-player-detail__badge--primary {
    color: #fff;
    background: #31529e;
    border-color: #31529e;
}

.guardian-player-detail__badge--success {
    color: #0f5132;
    background: #d1e7dd;
    border-color: #badbcc;
}

.guardian-player-detail__badge--danger {
    color: #842029;
    background: #f8d7da;
    border-color: #f1aeb5;
}

.guardian-player-detail__badge--warning {
    color: #664d03;
    background: #fff3cd;
    border-color: #ffecb5;
}

.guardian-player-detail__badge--secondary {
    color: #41464b;
    background: #e2e3e5;
    border-color: #d3d6d8;
}
</style>
