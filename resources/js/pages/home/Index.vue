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
                                    La idea de esta pantalla es darte una vista general de lo que hace el sistema:
                                    registrar deportistas, controlar inscripciones, hacer seguimiento a asistencias,
                                    gestionar competencias y, cuando aplica, apoyar procesos de cobro, facturacion y
                                    configuracion de la escuela.
                                </p>
                                <p class="text-muted mb-4">
                                    Segun tu perfil, tienes acceso a {{ moduleCountLabel.toLowerCase() }} y puedes
                                    entrar directamente a los procesos mas usados desde los accesos rapidos de esta
                                    misma pagina.
                                </p>

                                <div class="d-flex flex-wrap gap-2">
                                    <router-link :to="{ name: preferredLink.routeName }" class="btn btn-primary">
                                        {{ preferredLink.cta }}
                                    </router-link>
                                    <router-link :to="{ name: 'kpi' }" class="btn btn-secondary">
                                        Ver indicadores
                                    </router-link>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="border rounded p-3 h-100">
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

            <div class="col-xl-8 col-lg-7 col-12">
                <div class="panel br-6 h-100">
                    <div class="panel-body">
                        <div class="mb-4">
                            <h4 class="mb-2">Modulos principales</h4>
                            <p class="text-muted mb-0">
                                Estos son los espacios de trabajo que estructuran el funcionamiento general de la
                                plataforma.
                            </p>
                        </div>

                        <div v-if="featureCards.length" class="list-group list-group-flush">
                            <div
                                v-for="feature in featureCards"
                                :key="feature.title"
                                class="list-group-item px-0 py-3 bg-transparent"
                            >
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap p-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h5 class="mb-0">{{ feature.kicker }}</h5>
                                            <small class="text-muted">{{ feature.short }}</small>
                                        </div>
                                        <p class="mb-2"><strong>{{ feature.title }}</strong></p>
                                        <p class="text-muted mb-3">{{ feature.description }}</p>

                                        <ul class="ps-3 mb-0 small text-muted">
                                            <li v-for="point in feature.points" :key="point" class="mb-1">
                                                {{ point }}
                                            </li>
                                        </ul>
                                    </div>

                                    <router-link
                                        :to="{ name: feature.routeName }"
                                        class="btn btn-primary btn-sm"
                                    >
                                        {{ feature.cta }}
                                    </router-link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="border rounded p-4">
                            <h5 class="mb-2">Sin módulos habilitados</h5>
                            <p class="text-muted mb-0">
                                Esta escuela no tiene módulos activos para tu perfil en este momento. Puedes seguir consultando el dashboard y los indicadores generales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 col-12">
                <div class="panel br-6 h-100">
                    <div class="panel-body">
                        <div class="mb-4">
                            <h4 class="mb-2">Como se usa normalmente</h4>
                            <p class="text-muted mb-0">
                                Un recorrido sugerido para entender el flujo general dentro del sistema.
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
                    </div>
                </div>
            </div>

            <div class="col-xl-5 col-lg-6 col-12">
                <div class="panel br-6 h-100">
                    <div class="panel-body">
                        <div class="mb-4">
                            <h4 class="mb-2">Accesos rapidos</h4>
                            <p class="text-muted mb-0">
                                Enlaces directos a los modulos que suelen consultarse con mayor frecuencia.
                            </p>
                        </div>

                        <div class="list-group">
                            <router-link
                                v-for="link in quickLinks"
                                :key="link.title"
                                :to="{ name: link.routeName }"
                                class="list-group-item list-group-item-action"
                            >
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ link.title }}</div>
                                        <small class="text-muted">{{ link.description }}</small>
                                    </div>
                                    <small class="text-muted">{{ link.badge }}</small>
                                </div>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-lg-6 col-12">
                <div class="panel br-6 h-100">
                    <div class="panel-body">
                        <div class="mb-4">
                            <h4 class="mb-2">Que aporta la plataforma</h4>
                            <p class="text-muted mb-0">
                                Mas que almacenar informacion, GolApp ayuda a mantener ordenado el proceso para que los
                                datos sirvan de apoyo en la operacion diaria.
                            </p>
                        </div>

                        <div class="row g-3 mb-4">
                            <div v-for="benefit in benefits" :key="benefit.title" class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2">{{ benefit.title }}</h6>
                                    <p class="text-muted mb-0">{{ benefit.description }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-4">
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
</template>

<script setup>
import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'

const authUser = useAuthUser()
const { access } = useBackofficeAccess()

const canPlayers = access.players
const canInscriptions = access.inscriptions
const canEvaluations = access.evaluations
const canAttendances = access.attendances
const canMatches = access.matches
const canPayments = access.payments
const canReports = access.reports
const canBilling = access.billing
const canSchoolProfile = access.schoolProfile
const canUserManagement = access.userManagement
const canTrainingGroups = access.trainingGroups
const canCompetitionGroups = access.competitionGroups
const canTopicNotifications = access.topicNotifications

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

    if (canEvaluations.value || canAttendances.value || canMatches.value) {
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
                title: 'Consultar indicadores generales',
                description: 'Los KPI y el dashboard sirven como punto de partida mientras se habilitan modulos adicionales.',
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

    if (canEvaluations.value || canAttendances.value || canMatches.value) {
        return 'La plataforma te permite concentrarte en el seguimiento deportivo con una estructura clara de consulta y control.'
    }

    return 'La plataforma organiza la operacion disponible en un solo lugar para que el equipo trabaje con contexto compartido.'
})

const quickLinks = computed(() => {
    const links = [
        canPlayers.value && {
            title: 'Deportistas',
            description: 'Consulta fichas, filtros y detalle individual.',
            routeName: 'players',
            badge: 'Consulta',
        },
        canInscriptions.value && {
            title: 'Inscripciones',
            description: 'Revisa el ingreso y la organizacion por categoria.',
            routeName: 'inscriptions',
            badge: 'Proceso',
        },
        canEvaluations.value && {
            title: 'Evaluaciones',
            description: 'Consulta comparativos y periodos de seguimiento.',
            routeName: 'player-evaluations.index',
            badge: 'Seguimiento',
        },
        canAttendances.value && {
            title: 'Asistencias',
            description: 'Control diario del compromiso de entrenamiento.',
            routeName: 'attendances',
            badge: 'Control',
        },
        canMatches.value && {
            title: 'Competencias',
            description: 'Gestion de encuentros y seguimiento deportivo.',
            routeName: 'matches',
            badge: 'Operacion',
        },
        {
            title: 'KPI',
            description: 'Visualiza informacion resumida para decisiones.',
            routeName: 'kpi',
            badge: 'Analitica',
        },
        canPayments.value && {
            title: 'Mensualidades',
            description: 'Seguimiento de pagos y cartera por deportista.',
            routeName: 'payments',
            badge: 'Finanzas',
        },
        (canSchoolProfile.value || canUserManagement.value || canTrainingGroups.value || canCompetitionGroups.value) && {
            title: 'Administracion',
            description: 'Usuarios, grupos y datos clave de la escuela.',
            routeName: canSchoolProfile.value
                ? 'school'
                : canUserManagement.value
                    ? 'users'
                    : canTrainingGroups.value
                        ? 'training-groups'
                        : 'competition-groups',
            badge: 'Configuracion',
        },
        canBilling.value && {
            title: 'Facturacion',
            description: 'Facturas y seguimiento documental de cobro.',
            routeName: 'invoices.index',
            badge: 'Cobro',
        },
        canTopicNotifications.value && {
            title: 'Notificaciones',
            description: 'Envios generales y gestion de solicitudes.',
            routeName: 'topic-notifications.index',
            badge: 'Comunicacion',
        },
    ].filter(Boolean)

    return links
})

const benefits = computed(() => [
    {
        title: 'Informacion unificada',
        description: 'Los datos del proceso quedan concentrados en un mismo entorno y son mas faciles de consultar.',
    },
    {
        title: 'Orden operativo',
        description: 'Cada modulo aporta estructura para que el trabajo diario sea mas claro y menos disperso.',
    },
    {
        title: 'Mejor seguimiento',
        description: 'La informacion organizada ayuda a revisar avances, pendientes y decisiones con mayor contexto.',
    },
])

const recommendationText = computed(() => {
    return `Si quieres empezar por una tarea concreta, lo mas practico es entrar a ${preferredLink.value.cta.toLowerCase()} y continuar desde ahi con el flujo disponible para tu escuela.`
})

const preferredLink = computed(() => {
    const priority = [
        canPayments.value && { routeName: 'payments', cta: 'Ir a mensualidades' },
        canPlayers.value && { routeName: 'players', cta: 'Ir a deportistas' },
        canInscriptions.value && { routeName: 'inscriptions', cta: 'Ir a inscripciones' },
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

    return {
        routeName: 'kpi',
        cta: 'Ver indicadores',
    }
})
</script>
