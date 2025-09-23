import { createRouter, createWebHistory } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'

const routes = [
    {
        path: '',
        component: () => import(/* webpackChunkName: "auth-layout" */ '@/layouts/auth-layout.vue'),
        meta: { guest: true },
        children: [
            { path: '', name: 'login', component: () => import(/* webpackChunkName: "Login" */ '@/pages/auth/Login.vue') },
        ]
    },

    {
        path: '/',
        name: 'platform',
        meta: { requiresAuth: true },
        component: () => import(/* webpackChunkName: "app-layout" */ '@/layouts/app-layout.vue'),
        children: [
            { path: 'inicio', name: 'dashboard', component: () => import('@/pages/home/Index.vue'), },
            { path: 'kpi', name: 'kpi', component: () => import('@/pages/home/Index.vue'), },
            { path: 'perfil/usuario', name: 'user-profile', component: () => import('@/pages/home/Index.vue'), },
            { path: 'deportistas', name: 'players', component: () => import('@/pages/players/PlayersList.vue') },
            { path: 'inscripciones', name: 'inscriptions', component: () => import('@/pages/inscriptions/InscriptionsList.vue') },
            {
                path: '/administracion',
                name: 'admin',
                children: [
                    { path: 'escuela', name: 'school', component: () => import('@/pages/admin/school/UpdateSchool.vue') },
                    { path: 'usuarios', name: 'users', component: () => import('@/pages/admin/users/UsersList.vue') },
                    { path: 'g-entrenamiento', name: 'training-groups', component: () => import('@/pages/admin/groups/trainingList.vue') },
                    { path: 'g-competencia', name: 'competition-groups', component: () => import('@/pages/admin/groups/competitionGList.vue') }
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
    const store = useAuthUser()
    const isAuth = store.isAuthenticated
    if (to.matched.some(record => record.meta.requiresAuth) && !isAuth) {
        next({ name: 'login', query: { redirect: to.fullPath } })
    } else if (to.matched.some(record => record.meta.guest) && isAuth) {
        next({ name: 'dashboard' })
    } else {
        next()
    }
});

export default router;