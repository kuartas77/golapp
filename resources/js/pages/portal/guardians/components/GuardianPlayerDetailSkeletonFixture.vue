<template>
    <div class="row g-4 guardian-player-detail-fixture">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-2 p-lg-3 guardian-player-detail-fixture__hero">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <img src="/img/user.png" alt="Jugador" class="guardian-player-detail-fixture__photo">
                                <div>
                                    <p class="text-uppercase fw-semibold small mb-2">Jugador vigente</p>
                                    <span class="h2 mb-1">Mateo Andrés Rojas</span>
                                    <p class="mb-0">Escuela deportiva · Sub 13 - Mañana</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-auto">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary">Volver</button>
                                <button type="button" class="btn btn-outline-secondary">Ver QR</button>
                                <button type="button" class="btn btn-secondary">Descargar inscripción PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav guardian-player-detail-fixture__tabs">
                        <li v-for="tab in tabs" :key="tab" class="nav-item">
                            <button type="button" class="nav-link" :class="{ active: tab === 'Datos del deportista' }">
                                {{ tab }}
                            </button>
                        </li>
                    </ul>
                </div>
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
                        <span class="badge guardian-player-detail-fixture__badge">Código GL-1024</span>
                    </div>

                    <div class="guardian-player-detail-fixture__photo-editor mb-3">
                        <div class="guardian-player-detail-fixture__photo-preview">
                            <img src="/img/user.png" alt="Foto del deportista" class="guardian-player-detail-fixture__photo-image">
                        </div>
                        <div>
                            <label class="form-label mb-1">Foto del deportista</label>
                            <p class="text-muted small mb-3">Puedes cambiar la foto y corregir la orientación antes de guardar.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm">Cambiar foto</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm">Rotar izquierda</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm">Rotar derecha</button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div v-for="field in fields" :key="field" class="col-12 col-md-6">
                            <label class="form-label">{{ field }}</label>
                            <input type="text" class="form-control form-control-sm" :value="fieldValue(field)" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Antecedentes médicos</label>
                            <textarea rows="4" class="form-control form-control-sm" readonly>Sin antecedentes registrados.</textarea>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="d-flex flex-column gap-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 mb-3">Resumen deportivo actual</h2>
                        <div class="row g-3">
                            <div v-for="item in stats" :key="item.label" class="col-6">
                                <div class="guardian-player-detail-fixture__stat">
                                    <span>{{ item.label }}: <strong>{{ item.value }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
const fields = [
    'Nombres',
    'Apellidos',
    'Fecha de nacimiento',
    'Lugar de nacimiento',
    'Tipo de documento',
    'Documento',
    'Género',
    'Correo electrónico',
    'Celular',
    'Teléfonos',
    'Institución educativa',
    'Grado',
    'Jornada',
    'Seguro estudiantil',
    'Dirección',
    'Municipio',
    'Barrio',
    'RH',
    'EPS',
];

const stats = [
    { label: 'Partidos', value: 12 },
    { label: 'Asistencias', value: 9 },
    { label: 'Titular', value: 7 },
    { label: 'Minutos', value: 640 },
    { label: 'Goles', value: 4 },
    { label: 'Calificación', value: 4.5 },
];

const tabs = ['Datos del deportista', 'Pagos', 'Asistencias', 'Retroalimentación', 'Evaluaciones'];

const fieldValue = (field) => ({
    Nombres: 'Mateo Andrés',
    Apellidos: 'Rojas Martínez',
    Documento: '1020304050',
    Género: 'Masculino',
    Grado: '7',
    Jornada: 'Mañana',
    RH: 'O+',
}[field] ?? 'Dato registrado');
</script>

<style scoped>
.guardian-player-detail-fixture__hero {
    background:
        linear-gradient(135deg, rgba(15, 28, 70, 0.95), rgba(49, 82, 158, 0.88)),
        #0f1c46;
    color: #fff;
}

.guardian-player-detail-fixture__hero .text-muted,
.guardian-player-detail-fixture__hero .small {
    color: rgba(255, 255, 255, 0.8) !important;
}

.guardian-player-detail-fixture__tabs {
    flex-wrap: nowrap;
    gap: 0.35rem;
    overflow-x: auto;
}

.guardian-player-detail-fixture__tabs .nav-link {
    white-space: nowrap;
    border-radius: 0.85rem;
    color: #5f6b85;
    padding: 0.7rem 0.9rem;
}

.guardian-player-detail-fixture__tabs .nav-link.active {
    color: #fff;
    background: #0f1c46;
    font-weight: 700;
}

.guardian-player-detail-fixture__photo {
    width: 96px;
    height: 96px;
    border-radius: 24px;
    object-fit: cover;
    background: #f3f5fa;
}

.guardian-player-detail-fixture__photo-editor {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    border: 1px solid rgba(15, 28, 70, 0.08);
    border-radius: 8px;
    background: #f8f9fc;
}

.guardian-player-detail-fixture__photo-preview {
    width: 112px;
    height: 112px;
    flex: 0 0 112px;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}

.guardian-player-detail-fixture__photo-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.guardian-player-detail-fixture__badge {
    color: #27314f;
    background: #edf2ff;
}

.guardian-player-detail-fixture__stat,
.guardian-player-detail-fixture__payment-month {
    padding: 0.75rem;
    border-radius: 8px;
    background: #f7f9fd;
}

.guardian-player-detail-fixture__payment-panel {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fc;
}

@media (max-width: 575.98px) {
    .guardian-player-detail-fixture__photo-editor {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
