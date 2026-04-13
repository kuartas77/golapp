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
                                    <router-link :to="{ name: 'kpi' }" class="btn btn-outline-secondary">
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

                        <div class="list-group list-group-flush">
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

const authUser = useAuthUser()

const hasAdminAccess = computed(() => authUser.roles.some((role) => ['super-admin', 'school'].includes(role)))

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

const focusLabel = computed(() => (hasAdminAccess.value ? 'Gestion deportiva y administrativa' : 'Seguimiento deportivo'))
const moduleCountLabel = computed(() => `${featureCards.value.length} modulos disponibles`)

const featureCards = computed(() => {
    const baseCards = [
        {
            short: 'DP',
            kicker: 'Deportistas',
            title: 'Consolida perfiles y seguimiento individual',
            description: 'Centraliza la informacion principal de cada deportista para facilitar consultas, filtros y trazabilidad.',
            points: ['Ficha personal organizada', 'Consulta por codigo y categoria', 'Seguimiento desde detalle individual'],
            routeName: 'players',
            cta: 'Ir a deportistas',
        },
        {
            short: 'IN',
            kicker: 'Inscripciones',
            title: 'Administra el proceso de ingreso al programa',
            description: 'Controla registros, categorias y grupos para que el alta de cada deportista quede clara y ordenada.',
            points: ['Relacion entre categoria y grupo', 'Vista de inscripciones activas', 'Base util para cobros y control'],
            routeName: 'inscriptions',
            cta: 'Ver inscripciones',
        },
        {
            short: 'AS',
            kicker: 'Asistencias',
            title: 'Haz seguimiento al compromiso de entrenamiento',
            description: 'Consulta y registra asistencia para entender continuidad, presencia y ritmo de trabajo.',
            points: ['Control operativo por jornada', 'Apoyo al seguimiento mensual', 'Mejor lectura del proceso deportivo'],
            routeName: 'attendances',
            cta: 'Revisar asistencias',
        },
        {
            short: 'CP',
            kicker: 'Competencias',
            title: 'Organiza encuentros y control competitivo',
            description: 'Gestiona la operacion de competencias para mantener visibles los eventos y la preparacion del grupo.',
            points: ['Listado de competencias', 'Creacion y seguimiento de encuentros', 'Apoyo al control deportivo'],
            routeName: 'matches',
            cta: 'Ir a competencias',
        },
    ]

    if (!hasAdminAccess.value) {
        return baseCards
    }

    return [
        ...baseCards,
        {
            short: 'MN',
            kicker: 'Mensualidades',
            title: 'Controla recaudo y estado de pagos',
            description: 'Consulta movimientos por grupo y categoria para identificar cumplimiento, deudas y comportamiento financiero.',
            points: ['Vista operativa por deportista', 'Edicion y seguimiento mensual', 'Base clara para decisiones de cartera'],
            routeName: 'payments',
            cta: 'Abrir mensualidades',
        },
        {
            short: 'FC',
            kicker: 'Facturas',
            title: 'Genera soporte formal para el proceso de cobro',
            description: 'Mantiene organizada la emision de facturas y facilita el respaldo documental del recaudo.',
            points: ['Listado de facturas', 'Creacion desde inscripciones', 'Consulta detallada por registro'],
            routeName: 'invoices.index',
            cta: 'Ver facturas',
        },
        {
            short: 'AD',
            kicker: 'Administracion',
            title: 'Configura la escuela, usuarios y grupos',
            description: 'Ajusta la estructura operativa para que la plataforma refleje tu realidad organizacional.',
            points: ['Usuarios y permisos', 'Grupos de entrenamiento y competencia', 'Datos base de la escuela'],
            routeName: 'school',
            cta: 'Ir a administracion',
        },
    ]
})

const journeySteps = computed(() => {
    const steps = [
        {
            step: '01',
            title: 'Registrar y organizar deportistas',
            description: 'La base del proceso parte de tener la informacion personal y deportiva bien estructurada.',
        },
        {
            step: '02',
            title: 'Relacionar inscripciones y grupos',
            description: 'Esto permite ordenar el ingreso al programa y ubicar correctamente a cada deportista.',
        },
        {
            step: '03',
            title: 'Hacer seguimiento al proceso diario',
            description: 'Asistencias y competencias ayudan a observar continuidad, actividad y evolucion del grupo.',
        },
    ]

    if (!hasAdminAccess.value) {
        return [
            ...steps,
            {
                step: '04',
                title: 'Consultar indicadores generales',
                description: 'Los KPI complementan la lectura del proceso y ayudan a revisar el comportamiento global.',
            },
        ]
    }

    return [
        ...steps,
        {
            step: '04',
            title: 'Completar la gestion administrativa',
            description: 'Mensualidades, facturas y configuracion de la escuela cierran el circuito operativo.',
        },
    ]
})

const workflowSummary = computed(() => {
    if (hasAdminAccess.value) {
        return 'La plataforma conecta el seguimiento deportivo con la gestion administrativa para que la escuela trabaje con una sola fuente de informacion.'
    }

    return 'La plataforma te permite concentrarte en el seguimiento deportivo con una estructura clara de consulta y control.'
})

const quickLinks = computed(() => {
    const links = [
        {
            title: 'Deportistas',
            description: 'Consulta fichas, filtros y detalle individual.',
            routeName: 'players',
            badge: 'Consulta',
        },
        {
            title: 'Inscripciones',
            description: 'Revisa el ingreso y la organizacion por categoria.',
            routeName: 'inscriptions',
            badge: 'Proceso',
        },
        {
            title: 'Asistencias',
            description: 'Control diario del compromiso de entrenamiento.',
            routeName: 'attendances',
            badge: 'Control',
        },
        {
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
    ]

    if (!hasAdminAccess.value) {
        return links
    }

    return [
        ...links,
        {
            title: 'Mensualidades',
            description: 'Seguimiento de pagos y cartera por deportista.',
            routeName: 'payments',
            badge: 'Finanzas',
        },
        {
            title: 'Administracion',
            description: 'Usuarios, grupos y datos clave de la escuela.',
            routeName: 'school',
            badge: 'Configuracion',
        },
    ]
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
    if (hasAdminAccess.value) {
        return 'Si quieres empezar por una tarea concreta, lo mas practico es entrar al modulo de mensualidades o al de administracion, segun la necesidad del momento.'
    }

    return 'Si quieres empezar por una tarea concreta, lo mas practico es entrar al listado de deportistas y continuar desde alli con el seguimiento del proceso.'
})

const preferredLink = computed(() => {
    if (hasAdminAccess.value) {
        return {
            routeName: 'payments',
            cta: 'Ir a mensualidades',
        }
    }

    return {
        routeName: 'players',
        cta: 'Ir a deportistas',
    }
})
</script>
