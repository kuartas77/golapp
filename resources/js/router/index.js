import { createRouter, createWebHistory, RouterView } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'
import { h } from 'vue'

const routes = [
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
            { path: 'kpi', name: 'kpi', component: () => import('@/pages/home/Index.vue'), },
            { path: 'perfil/usuario', name: 'user-profile', component: () => import('@/pages/home/Index.vue'), },
            {
                path: 'deportistas', component: { render: () => h(RouterView) },
                children: [
                    { path: '', name: 'players', component: () => import('@/pages/players/PlayersList.vue') },
                    { path: ':unique_code', name: 'player-detail', component: () => import('@/pages/players/PlayerDetail.vue') },
                ]
            },
            { path: 'inscripciones', name: 'inscriptions', component: () => import('@/pages/inscriptions/InscriptionsList.vue') },
            { path: 'asistencias', name: 'attendances', component: () => import('@/pages/attendances/attendance-list.vue') },
            { path: 'mensualidades', name: 'payments', component: () => import('@/pages/payments/monthly-payment-list.vue') },
            {
                path: 'control-competencias', component: { render: () => h(RouterView) },
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
                    { path: 'escuela', name: 'school', component: () => import('@/pages/admin/school/UpdateSchool.vue') },
                    { path: 'usuarios', name: 'users', component: () => import('@/pages/admin/users/UsersList.vue') },
                    { path: 'g-entrenamiento', name: 'training-groups', component: () => import('@/pages/admin/groups/training/trainingList.vue') },
                    { path: 'g-entrenamiento/admin', name: 'training-groups-admin', component: () => import('@/pages/admin/groups/training/AdminTrainingGroup.vue') },
                    { path: 'g-competencia', name: 'competition-groups', component: () => import('@/pages/admin/groups/competition/competitionGList.vue') },

                    { path: 'schools', name: 'schools', component: () => import('@/pages/admin/school/list-schools.vue'), meta: { requiresRole: ['super-admin'] } },
                    { path: 'schools-info', name: 'schools-info', component: () => import('@/pages/admin/school/list-info.vue'), meta: { requiresRole: ['super-admin'] } },
                ]
            }
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
    const isGuestRoute = to.matched.some(r => r.meta.guest);

    if (isGuestRoute) return next();

    const isAuth = await userStore.init();

    if (!isAuth) {
        next({ name: 'login', query: { redirect: to.fullPath } });
        userStore.logout(); // Limpia estado y token
        return
    }


    const requiredRole = to.matched.find(r => r.meta?.requiresRole)?.meta?.requiresRole;
    const role = userStore.user?.role?.name;

    if (requiredRole && !requiredRole.includes(role)) {
        return next({ name: 'dashboard' });
    }

    return next();
});

export default router;