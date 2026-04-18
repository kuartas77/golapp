import { createRouter, createWebHistory, RouterView } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'
import { useGuardianAuth } from '@/store/guardian-auth'
import { SCHOOL_PERMISSION_KEYS } from '@/config/school-permissions'
import { h } from 'vue'

const routes = [
    {
        path: '/portal/acudientes',
        component: () => import('@/layouts/guardian-layout.vue'),
        meta: { guardianArea: true },
        children: [
            { path: '', name: 'guardian-dashboard', component: () => import('@/pages/portal/guardians/GuardianDashboard.vue'), meta: { requiresGuardian: true, guardianArea: true } },
            { path: 'login', name: 'guardian-login', component: () => import('@/pages/portal/guardians/GuardianLogin.vue'), meta: { guardianGuest: true, guardianArea: true } },
            { path: 'restablecer', name: 'guardian-reset-password', component: () => import('@/pages/portal/guardians/GuardianResetPassword.vue'), meta: { guardianGuest: true, guardianArea: true } },
            { path: 'perfil', name: 'guardian-profile', component: () => import('@/pages/portal/guardians/GuardianProfile.vue'), meta: { requiresGuardian: true, guardianArea: true } },
            { path: 'jugadores/:id', name: 'guardian-player-detail', component: () => import('@/pages/portal/guardians/GuardianPlayerDetail.vue'), meta: { requiresGuardian: true, guardianArea: true } },
        ]
    },
    {
        path: '/portal',
        component: () => import('@/layouts/portal-layout.vue'),
        meta: { public: true },
        children: [
            { path: '', redirect: { name: 'guardian-dashboard' } },
            // { path: 'escuelas', name: 'portal-school-index', component: () => import('@/pages/portal/PortalSchoolsIndex.vue'), meta: { public: true } },
            { path: 'escuelas/:slug', name: 'portal-school-show', component: () => import('@/pages/portal/PortalSchoolShow.vue'), meta: { public: true } },
        ]
    },
    {
        path: '/login',
        component: () => import('@/layouts/auth-layout.vue'),
        meta: { guest: true },
        children: [
            { path: '', name: 'login', component: () => import('@/pages/auth/Login.vue') },
        ]
    },
    {
        path: '/',
        name: 'platform',
        meta: { requiresAuth: true },
        component: () => import('@/layouts/app-layout.vue'),
        children: [
            { path: '', redirect: '/inicio', name: 'redirect' },
            { path: 'inicio', name: 'dashboard', component: () => import('@/pages/home/Index.vue'), },
            { path: 'kpi', name: 'kpi', component: () => import('@/pages/kpi/Index.vue'), },
            { path: 'player-stats', name: 'player-stats.index', component: () => import('@/pages/player-stats/Ranking.vue') },
            { path: 'top-players', name: 'player-stats.top', component: () => import('@/pages/player-stats/TopPlayers.vue') },
            { path: 'player/:id/detail', name: 'player-stats.detail', component: () => import('@/pages/player-stats/Detail.vue') },
            {
                path: 'player-evaluations',
                meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.evaluations] },
                component: { render: () => h(RouterView) },
                children: [
                    { path: '', name: 'player-evaluations.index', component: () => import('@/pages/player-evaluations/Index.vue') },
                    { path: 'comparison', name: 'player-evaluations.comparison', component: () => import('@/pages/player-evaluations/Comparison.vue') },
                    { path: 'create', name: 'player-evaluations.create', component: () => import('@/pages/player-evaluations/Editor.vue') },
                    { path: ':id/edit', name: 'player-evaluations.edit', component: () => import('@/pages/player-evaluations/Editor.vue') },
                    { path: ':id', name: 'player-evaluations.show', component: () => import('@/pages/player-evaluations/Show.vue') },
                ]
            },
            { path: 'perfil/usuario', name: 'user-profile', component: () => import('@/pages/home/Index.vue'), },
            {
                path: 'deportistas',
                meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.players] },
                component: { render: () => h(RouterView) },
                children: [
                    { path: '', name: 'players', component: () => import('@/pages/players/PlayersList.vue') },
                    { path: ':unique_code', name: 'player-detail', component: () => import('@/pages/players/PlayerDetail.vue') },
                ]
            },
            {
                path: 'inscripciones',
                name: 'inscriptions',
                meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.inscriptions] },
                component: () => import('@/pages/inscriptions/InscriptionsList.vue')
            },
            {
                path: 'asistencias',
                name: 'attendances',
                meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.attendances] },
                component: () => import('@/pages/attendances/attendance-list.vue')
            },

            {
                path: 'mensualidades',
                name: 'payments',
                meta: {
                    requiresRole: ['super-admin', 'school'],
                    requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.payments],
                },
                component: () => import('@/pages/payments/monthly-payment-list.vue')
            },
            {
                path: 'control-competencias',
                meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.matches] },
                component: { render: () => h(RouterView) },
                children: [
                    { path: '', name: 'matches', component: () => import('@/pages/matches/MatchesList.vue') },
                    { path: 'nuevo/:grupo_competencia', name: 'matches-create', component: () => import('@/pages/matches/NewMatch.vue') },
                    { path: ':id', name: 'matches-edit', component: () => import('@/pages/matches/EditMatch.vue') },
                ]
            },
            {
                path: '/administracion',
                name: 'admin',
                meta: { requiresRole: ['super-admin', 'school'] },
                children: [
                    {
                        path: 'escuela',
                        name: 'school',
                        component: () => import('@/pages/admin/school/UpdateSchool.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.schoolProfile] }
                    },
                    {
                        path: 'usuarios',
                        name: 'users',
                        component: () => import('@/pages/admin/users/UsersList.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.userManagement] }
                    },
                    {
                        path: 'g-entrenamiento',
                        name: 'training-groups',
                        component: () => import('@/pages/admin/groups/training/trainingList.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.trainingGroups] }
                    },
                    {
                        path: 'g-entrenamiento/admin',
                        name: 'training-groups-admin',
                        component: () => import('@/pages/admin/groups/training/AdminTrainingGroup.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.trainingGroups] }
                    },
                    {
                        path: 'g-competencia',
                        name: 'competition-groups',
                        component: () => import('@/pages/admin/groups/competition/competitionGList.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.competitionGroups] }
                    },
                    {
                        path: 'plantillas-evaluacion',
                        name: 'evaluation-templates.index',
                        component: () => import('@/pages/admin/evaluation-templates/Index.vue'),
                        meta: { requiresRole: ['super-admin'] }
                    },
                    {
                        path: 'plantillas-evaluacion/crear',
                        name: 'evaluation-templates.create',
                        component: () => import('@/pages/admin/evaluation-templates/Editor.vue'),
                        meta: { requiresRole: ['super-admin'] }
                    },
                    {
                        path: 'plantillas-evaluacion/:id/editar',
                        name: 'evaluation-templates.edit',
                        component: () => import('@/pages/admin/evaluation-templates/Editor.vue'),
                        meta: { requiresRole: ['super-admin'] }
                    },

                    { path: 'schools', name: 'schools', component: () => import('@/pages/admin/school/list-schools.vue'), meta: { requiresRole: ['super-admin'] } },
                    { path: 'schools/create', name: 'schools.create', component: () => import('@/pages/admin/school/CreateSchool.vue'), meta: { requiresRole: ['super-admin'] } },
                    { path: 'schools/:slug/edit', name: 'schools.edit', component: () => import('@/pages/admin/school/EditSchool.vue'), meta: { requiresRole: ['super-admin'] } },
                    { path: 'schools-info', name: 'schools-info', component: () => import('@/pages/admin/school/list-info.vue'), meta: { requiresRole: ['super-admin'] } },
                ]
            },
            {
                path: '/facturas',
                meta: {
                    requiresRole: ['super-admin', 'school'],
                    requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.billing],
                },
                name: 'invoices',
                component: { render: () => h(RouterView) },
                children: [
                    { path: '', name: 'invoices.index', component: () => import('@/pages/invoices/Invoices.vue') },
                    {
                        path: 'comprobantes-pago',
                        name: 'payment-requests.index',
                        component: () => import('@/pages/notifications/PaymentRequests.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.systemNotify] }
                    },
                    {
                        path: 'solicitudes-uniformes',
                        name: 'uniform-requests.index',
                        component: () => import('@/pages/notifications/UniformRequests.vue'),
                        meta: { requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.systemNotify] }
                    },
                    { path: 'crear/:inscription', name: 'invoices.create', component: () => import('@/pages/invoices/InvoiceCreate.vue') },
                    { path: ':id', name: 'invoices.show', component: () => import('@/pages/invoices/InvoiceShow.vue') },
                ]
            },
            {
                path: 'notificaciones',
                name: 'topic-notifications.index',
                meta: {
                    requiresRole: ['super-admin', 'school'],
                    requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.systemNotify],
                },
                component: () => import('@/pages/notifications/TopicNotifications.vue')
            },
            {
                path: 'informes',
                meta: {
                    requiresRole: ['super-admin', 'school'],
                    requiresSchoolPermission: [SCHOOL_PERMISSION_KEYS.reports],
                },
                component: { render: () => h(RouterView) },
                children: [
                    {
                        path: 'asistencias',
                        name: 'reports.assists',
                        component: () => import('@/pages/reports/assists/Index.vue')
                    },
                    {
                        path: 'pagos',
                        name: 'reports.payments',
                        component: () => import('@/pages/reports/payments/Index.vue')
                    },
                ]
            },
        ]
    },

];

const router = new createRouter({
    // mode: 'history',
    history: createWebHistory(),
    linkExactActiveClass: 'active',
    routes: routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { left: 0, top: 0 };
        }
    },
});

router.beforeEach(async (to, from, next) => {
    const userStore = useAuthUser();
    const guardianStore = useGuardianAuth();
    const isGuardianRoute = to.matched.some(r => r.meta.guardianArea);

    if (isGuardianRoute) {
        const requiresGuardian = to.matched.some(r => r.meta.requiresGuardian);
        const isGuardianGuestRoute = to.matched.some(r => r.meta.guardianGuest);
        const isGuardianAuth = await guardianStore.init();

        if (requiresGuardian && !isGuardianAuth) {
            guardianStore.clearState();
            return next({ name: 'guardian-login', query: { redirect: to.fullPath } });
        }

        if (isGuardianGuestRoute && isGuardianAuth) {
            return next({ name: 'guardian-dashboard' });
        }

        return next();
    }

    const isGuestRoute = to.matched.some(r => r.meta.guest || r.meta.public);

    if (isGuestRoute) return next();

    const isAuth = await userStore.init();

    if (!isAuth) {
        next({ name: 'login', query: { redirect: to.fullPath } });
        userStore.logout(); // Limpia estado y token
        return
    }

    const userRoles = userStore.roles || [];

    for (const routeRecord of to.matched) {
        const requiredRoles = routeRecord.meta?.requiresRole || [];
        if (requiredRoles.length > 0) {
            const hasRole = requiredRoles.some((role) => userRoles.includes(role));
            if (!hasRole) {
                return next({ name: 'dashboard' });
            }
        }

        const requiredRolesAll = routeRecord.meta?.requiresRoleAll || [];
        if (requiredRolesAll.length > 0) {
            const hasAllRoles = requiredRolesAll.every((role) => userRoles.includes(role));
            if (!hasAllRoles) {
                return next({ name: 'dashboard' });
            }
        }

        const requiredSchoolPermissions = routeRecord.meta?.requiresSchoolPermission || [];
        if (requiredSchoolPermissions.length > 0) {
            const hasAnySchoolPermission = requiredSchoolPermissions.some((permission) => userStore.hasSchoolPermission(permission));
            if (!hasAnySchoolPermission) {
                return next({ name: 'dashboard' });
            }
        }

        const requiredSchoolPermissionsAll = routeRecord.meta?.requiresSchoolPermissionAll || [];
        if (requiredSchoolPermissionsAll.length > 0) {
            const hasAllSchoolPermissions = requiredSchoolPermissionsAll.every((permission) => userStore.hasSchoolPermission(permission));
            if (!hasAllSchoolPermissions) {
                return next({ name: 'dashboard' });
            }
        }
    }

    return next();
});

export default router;
