<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="panel br-6">
                    <div class="panel-body">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <h2 class="mb-3">Bienvenido a GolApp</h2>
                                <p class="mb-3">
                                    GolApp es la plataforma desde la que {{ schoolDisplayName }} puede organizar la
                                    informacion principal de su operacion deportiva, administrativa y de seguimiento.
                                </p>
                                <p class="text-muted mb-3">
                                    El flujo recomendado es sencillo: primero organiza la base del proceso, luego haz
                                    seguimiento al trabajo diario del grupo y, cuando aplique, cierra la gestion
                                    administrativa desde los modulos habilitados para tu perfil.
                                </p>
                                <p class="text-muted mb-4">
                                    En este inicio solo veras el contexto actual, el orden sugerido de trabajo y los
                                    accesos directos a {{ moduleCountLabel.toLowerCase() }}.
                                </p>

                                <div class="d-flex flex-wrap gap-2" data-tour="home-welcome">
                                    <router-link :to="{ name: preferredLink.routeName }" class="btn btn-primary">
                                        {{ preferredLink.cta }}
                                    </router-link>
                                    <router-link v-if="canKpi" :to="{ name: 'kpi' }" class="btn btn-secondary">
                                        Ver indicadores
                                    </router-link>
                                    <button type="button" class="btn btn-info" @click="tutorial.start()">
                                        <i class="fa-regular fa-circle-question me-2"></i>
                                        Guia
                                    </button>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="border rounded p-3 h-100" data-tour="home-context">
                                    <h5 class="mb-3">Contexto actual</h5>
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-5 text-muted fw-normal">Usuario</dt>
                                        <dd class="col-sm-7 mb-2">{{ userName }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">Perfil</dt>
                                        <dd class="col-sm-7 mb-2">{{ roleLabel }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">Escuela</dt>
                                        <dd class="col-sm-7 mb-2">{{ schoolDisplayName }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">Alcance</dt>
                                        <dd class="col-sm-7 mb-2">{{ focusLabel }}</dd>

                                        <dt class="col-sm-5 text-muted fw-normal">Modulos</dt>
                                        <dd class="col-sm-7 mb-0">{{ moduleCountLabel }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-4">
                    <div class="col-xl-7 col-lg-7 col-12">
                        <div class="panel br-6" data-tour="home-modules">
                            <div class="panel-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
                                    <div>
                                        <h4 class="mb-2">Modulos disponibles</h4>
                                        <p class="text-muted mb-0">
                                            Entradas directas a cada espacio habilitado. Usa el icono de ayuda para ver
                                            una descripcion breve del modulo.
                                        </p>
                                    </div>
                                    <span v-if="featureCards.length" class="badge border text-muted px-3 py-2">
                                        {{ moduleCountLabel }}
                                    </span>
                                </div>

                                <div v-if="featureCards.length" class="row g-3">
                                    <div
                                        v-for="feature in featureCards"
                                        :key="feature.key"
                                        class="col-xl-6 col-md-6 col-12"
                                    >
                                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="mb-0">{{ feature.kicker }}</h5>
                                                <button
                                                    type="button"
                                                    class="btn btn-link btn-sm text-muted p-0 text-decoration-none"
                                                    :aria-label="`Ver descripcion de ${feature.kicker}`"
                                                    v-tooltip.top="feature.description"
                                                >
                                                    <i class="fa-regular fa-circle-question"></i>
                                                </button>
                                            </div>

                                            <router-link
                                                :to="{ name: feature.routeName }"
                                                class="btn btn-outline-primary btn-sm mt-3 align-self-start"
                                            >
                                                {{ feature.cta }}
                                            </router-link>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="border rounded p-4">
                                    <h5 class="mb-2">Sin modulos habilitados</h5>
                                    <p class="text-muted mb-0">
                                        Esta escuela no tiene modulos activos para tu perfil en este momento. Puedes seguir consultando el dashboard y los indicadores generales.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5 col-lg-5 col-12">
                        <div class="panel br-6" data-tour="home-journey">
                            <div class="panel-body">
                                <div class="mb-4">
                                    <h4 class="mb-2">Flujo recomendado</h4>
                                    <p class="text-muted mb-0">
                                        Este recorrido breve te ayuda a usar la plataforma con un orden claro desde el
                                        primer ingreso.
                                    </p>
                                </div>

                                <ol class="list-group list-group-numbered">
                                    <li
                                        v-for="step in journeySteps"
                                        :key="step.step"
                                        class="list-group-item bg-transparent"
                                    >
                                        <div class="ms-2">
                                            <div class="fw-semibold mb-1">{{ step.title }}</div>
                                            <small class="text-muted">{{ step.description }}</small>
                                        </div>
                                    </li>
                                </ol>

                                <div class="mt-4 border rounded p-3">
                                    <h6 class="mb-2">Lectura rapida</h6>
                                    <p class="text-muted mb-0">
                                        {{ workflowSummary }}
                                    </p>
                                </div>

                                <div class="mt-4 border rounded p-4" data-tour="home-next-step">
                                    <h5 class="mb-2">Siguiente paso recomendado</h5>
                                    <p class="text-muted mb-3">
                                        {{ recommendationText }}
                                    </p>
                                    <router-link :to="{ name: preferredLink.routeName }" class="btn btn-primary">
                                        {{ preferredLink.cta }}
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { homeTutorial } from '@/tutorials/dashboard'

const authUser = useAuthUser()
const { access } = useBackofficeAccess()
const tutorial = usePageTutorial(homeTutorial)

const canPlayers = access.players
const canInscriptions = access.inscriptions
const canEvaluations = access.evaluations
const canAttendances = access.attendances
const canTrainingSessions = access.trainingSessions
const canMatches = access.matches
const canPayments = access.payments
const canReports = access.reports
const canBilling = access.billing
const canSchoolProfile = access.schoolProfile
const canUserManagement = access.userManagement
const canTrainingGroups = access.trainingGroups
const canCompetitionGroups = access.competitionGroups
const canTopicNotifications = access.topicNotifications

const canKpi = computed(() => authUser.hasAnyRole(['super-admin', 'school']))

const userName = computed(() => {
    const fullName = authUser.user?.name || 'Equipo'
    return fullName.split(' ')[0]
})

const schoolDisplayName = computed(() => authUser.user?.school_name || 'tu escuela')

const roleLabel = computed(() => {
    if (authUser.roles.includes('super-admin')) {
        return 'Super administrador'
    }

    if (authUser.roles.includes('school')) {
        return 'Administrador de escuela'
    }

    return 'Usuario operativo'
})

const hasAdministrativeReach = computed(() => (
    canPayments.value
    || canReports.value
    || canBilling.value
    || canSchoolProfile.value
    || canUserManagement.value
    || canTrainingGroups.value
    || canCompetitionGroups.value
    || canTopicNotifications.value
))

const focusLabel = computed(() => {
    if (hasAdministrativeReach.value) {
        return 'Gestion deportiva y administrativa'
    }

    if (canEvaluations.value || canAttendances.value || canTrainingSessions.value || canMatches.value) {
        return 'Seguimiento deportivo'
    }

    return 'Consulta operativa'
})

const featureDefinitions = [
    {
        key: 'players',
        isAvailable: () => canPlayers.value,
        short: 'DP',
        kicker: 'Deportistas',
        title: 'Consolida perfiles y seguimiento individual',
        description: 'Centraliza la informacion principal de cada deportista para facilitar consultas, filtros y trazabilidad.',
        points: ['Ficha personal organizada', 'Consulta por codigo y categoria', 'Seguimiento desde detalle individual'],
        routeName: 'players',
        cta: 'Ir a deportistas',
    },
    {
        key: 'inscriptions',
        isAvailable: () => canInscriptions.value,
        short: 'IN',
        kicker: 'Inscripciones',
        title: 'Administra el proceso de ingreso al programa',
        description: 'Controla registros, categorias y grupos para que el alta de cada deportista quede clara y ordenada.',
        points: ['Relacion entre categoria y grupo', 'Vista de inscripciones activas', 'Base util para cobros y control'],
        routeName: 'inscriptions',
        cta: 'Ver inscripciones',
    },
    {
        key: 'trainingSessions',
        isAvailable: () => canTrainingSessions.value,
        short: 'SE',
        kicker: 'Sesiones',
        title: 'Planifica sesiones y estructura ejercicios por paso',
        description: 'Organiza el contenido de cada entrenamiento para dejar visible el objetivo, el orden de trabajo y su historico.',
        points: ['Planeacion por grupo y periodo', 'Ejercicios organizados por pasos', 'Historico listo para consulta y edicion'],
        routeName: 'training-sessions',
        cta: 'Ir a sesiones',
    },
    {
        key: 'attendances',
        isAvailable: () => canAttendances.value,
        short: 'AS',
        kicker: 'Asistencias',
        title: 'Haz seguimiento al compromiso de entrenamiento',
        description: 'Consulta y registra asistencia para entender continuidad, presencia y ritmo de trabajo.',
        points: ['Control operativo por jornada', 'Apoyo al seguimiento mensual', 'Mejor lectura del proceso deportivo'],
        routeName: 'attendances',
        cta: 'Revisar asistencias',
    },
    {
        key: 'evaluations',
        isAvailable: () => canEvaluations.value,
        short: 'EV',
        kicker: 'Evaluaciones',
        title: 'Documenta avances y comparativos por jugador',
        description: 'Permite registrar evaluaciones, revisar periodos y comparar resultados para apoyar el seguimiento deportivo.',
        points: ['Evaluaciones por periodo', 'Comparativos historicos', 'Soporte al proceso formativo'],
        routeName: 'player-evaluations.index',
        cta: 'Ir a evaluaciones',
    },
    {
        key: 'matches',
        isAvailable: () => canMatches.value,
        short: 'CP',
        kicker: 'Competencias',
        title: 'Organiza encuentros y control competitivo',
        description: 'Gestiona la operacion de competencias para mantener visibles los eventos y la preparacion del grupo.',
        points: ['Listado de competencias', 'Creacion y seguimiento de encuentros', 'Apoyo al control deportivo'],
        routeName: 'matches',
        cta: 'Ir a competencias',
    },
    {
        key: 'payments',
        isAvailable: () => canPayments.value,
        short: 'MN',
        kicker: 'Mensualidades',
        title: 'Controla recaudo y estado de pagos',
        description: 'Consulta movimientos por grupo y categoria para identificar cumplimiento, deudas y comportamiento financiero.',
        points: ['Vista operativa por deportista', 'Edicion y seguimiento mensual', 'Base clara para decisiones de cartera'],
        routeName: 'payments',
        cta: 'Abrir mensualidades',
    },
    {
        key: 'reports',
        isAvailable: () => canReports.value,
        short: 'RP',
        kicker: 'Informes',
        title: 'Consulta reportes listos para analizar',
        description: 'Reune reportes operativos y financieros para revisar asistencias, pagos y consolidar decisiones.',
        points: ['Exportes directos', 'Lectura por periodo', 'Soporte para seguimiento y control'],
        routeName: 'reports.assists',
        cta: 'Ver informes',
    },
    {
        key: 'billing',
        isAvailable: () => canBilling.value,
        short: 'FC',
        kicker: 'Facturas',
        title: 'Genera soporte formal para el proceso de cobro',
        description: 'Mantiene organizada la emision de facturas y facilita el respaldo documental del recaudo.',
        points: ['Listado de facturas', 'Creacion desde inscripciones', 'Consulta detallada por registro'],
        routeName: 'invoices.index',
        cta: 'Ver facturas',
    },
    {
        key: 'admin',
        isAvailable: () => (
            canSchoolProfile.value
            || canUserManagement.value
            || canTrainingGroups.value
            || canCompetitionGroups.value
        ),
        short: 'AD',
        kicker: 'Administracion',
        title: 'Configura la escuela, usuarios y grupos',
        description: 'Ajusta la estructura operativa para que la plataforma refleje tu realidad organizacional.',
        points: ['Usuarios y permisos', 'Grupos de entrenamiento y competencia', 'Datos base de la escuela'],
        routeName: () => (canSchoolProfile.value
            ? 'school'
            : canUserManagement.value
                ? 'users'
                : canTrainingGroups.value
                    ? 'training-groups'
                    : 'competition-groups'),
        cta: 'Ir a administracion',
    },
    {
        key: 'notifications',
        isAvailable: () => canTopicNotifications.value,
        short: 'NT',
        kicker: 'Notificaciones',
        title: 'Gestiona envios y solicitudes del ecosistema',
        description: 'Centraliza notificaciones generales y procesos vinculados a comprobantes o solicitudes con GOLAPPLINK.',
        points: ['Envios por temas', 'Seguimiento a solicitudes', 'Operacion integrada con facturacion'],
        routeName: 'topic-notifications.index',
        cta: 'Abrir notificaciones',
    },
]

const featureCards = computed(() => (
    featureDefinitions
        .filter((feature) => feature.isAvailable())
        .map((feature) => ({
            ...feature,
            routeName: typeof feature.routeName === 'function' ? feature.routeName() : feature.routeName,
        }))
))

const moduleCountLabel = computed(() => {
    const total = featureCards.value.length
    return `${total} ${total === 1 ? 'modulo disponible' : 'modulos disponibles'}`
})

const journeySteps = computed(() => {
    const steps = []

    if (canPlayers.value) {
        steps.push({
            title: 'Registrar y organizar deportistas',
            description: 'La base del proceso parte de tener la informacion personal y deportiva bien estructurada.',
        })
    }

    if (canInscriptions.value) {
        steps.push({
            title: 'Relacionar inscripciones y grupos',
            description: 'Esto permite ordenar el ingreso al programa y ubicar correctamente a cada deportista.',
        })
    }

    if (canTrainingSessions.value) {
        steps.push({
            title: 'Preparar sesiones de entrenamiento',
            description: 'La planeacion por periodos y ejercicios te ayuda a dar continuidad metodologica al trabajo del grupo.',
        })
    }

    if (canAttendances.value || canEvaluations.value || canMatches.value) {
        steps.push({
            title: 'Hacer seguimiento al proceso diario',
            description: 'Asistencias, evaluaciones y competencias ayudan a observar continuidad, actividad y evolucion del grupo.',
        })
    }

    if (canPayments.value || canBilling.value || canReports.value) {
        steps.push({
            title: 'Completar la gestion administrativa',
            description: 'Mensualidades, facturas e informes ayudan a cerrar el circuito operativo y financiero.',
        })
    }

    if (steps.length === 0) {
        return [
            {
                step: '01',
                title: canKpi.value ? 'Consultar indicadores generales' : 'Ubicar el contexto de trabajo',
                description: canKpi.value
                    ? 'Los KPI y el dashboard sirven como punto de partida mientras se habilitan modulos adicionales.'
                    : 'El dashboard resume el contexto disponible mientras se habilitan modulos adicionales para tu perfil.',
            },
        ]
    }

    return steps.map((step, index) => ({
        step: String(index + 1).padStart(2, '0'),
        ...step,
    }))
})

const workflowSummary = computed(() => {
    if (hasAdministrativeReach.value) {
        return 'La plataforma conecta el seguimiento deportivo con la gestion administrativa para que la escuela trabaje con una sola fuente de informacion.'
    }

    if (canTrainingSessions.value || canEvaluations.value || canAttendances.value || canMatches.value) {
        return 'La plataforma te permite planear sesiones y concentrarte en el seguimiento deportivo con una estructura clara de consulta y control.'
    }

    return 'La plataforma organiza la operacion disponible en un solo lugar para que el equipo trabaje con contexto compartido.'
})

const recommendationText = computed(() => {
    return `Si quieres empezar por una tarea concreta, lo mas practico es entrar a ${preferredLink.value.cta.toLowerCase()} y continuar desde ahi con el flujo disponible para tu escuela.`
})

const preferredLink = computed(() => {
    const priority = [
        canPayments.value && { routeName: 'payments', cta: 'Ir a mensualidades' },
        canPlayers.value && { routeName: 'players', cta: 'Ir a deportistas' },
        canInscriptions.value && { routeName: 'inscriptions', cta: 'Ir a inscripciones' },
        canTrainingSessions.value && { routeName: 'training-sessions', cta: 'Ir a sesiones de entrenamiento' },
        canEvaluations.value && { routeName: 'player-evaluations.index', cta: 'Ir a evaluaciones' },
        canAttendances.value && { routeName: 'attendances', cta: 'Ir a asistencias' },
        canMatches.value && { routeName: 'matches', cta: 'Ir a competencias' },
        canBilling.value && { routeName: 'invoices.index', cta: 'Ir a facturacion' },
        canReports.value && { routeName: 'reports.assists', cta: 'Ir a informes' },
        canSchoolProfile.value && { routeName: 'school', cta: 'Ir a administracion' },
        canUserManagement.value && { routeName: 'users', cta: 'Ir a usuarios' },
        canTrainingGroups.value && { routeName: 'training-groups', cta: 'Ir a grupos' },
        canCompetitionGroups.value && { routeName: 'competition-groups', cta: 'Ir a grupos de competencia' },
        canTopicNotifications.value && { routeName: 'topic-notifications.index', cta: 'Ir a notificaciones' },
    ].filter(Boolean)

    if (priority.length > 0) {
        return priority[0]
    }

    if (canKpi.value) {
        return {
            routeName: 'kpi',
            cta: 'Ver indicadores',
        }
    }

    return {
        routeName: 'dashboard',
        cta: 'Ir al inicio',
    }
})
</script>
