import { createRouter, createWebHistory } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'

const checkRequiresRoles = (to, from, next)  => {
    const userStore = useAuthUser()
    const userRoleName = userStore.user.role.name
    if(!to.meta.requiresRole.includes(userRoleName)){
        return next({ name: 'dashboard' })
    }
    return next()
}

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
            { path: 'deportistas', name: 'players', component: () => import('@/pages/players/PlayersList.vue') },
            { path: 'inscripciones', name: 'inscriptions', component: () => import('@/pages/inscriptions/InscriptionsList.vue') },
            { path: 'asistencias', name: 'attendances', component: () => import('@/pages/attendances/attendance-list.vue') },
            { path: 'mensualidades', name: 'payments', component: () => import('@/pages/payments/monthly-payment-list.vue') },
            {
                path: '/administracion',
                name: 'admin',
                meta: { requiresRole: ['super-admin', 'school'] },
                beforeEnter:[ checkRequiresRoles ],
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

router.beforeEach((to, from, next) => {
    const userStore = useAuthUser()
    const isAuth = userStore.isAuthenticated

    if (to.matched.some(record => record.meta.requiresAuth) && !isAuth) {
        next({ name: 'login', query: { redirect: to.fullPath } })
    } else if (to.matched.some(record => record.meta.guest) && isAuth) {
        next({ name: 'dashboard' })
    } else {
        next()
    }
});

export default router;