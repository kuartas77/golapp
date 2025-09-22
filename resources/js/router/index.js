import { createRouter, createWebHistory } from 'vue-router';
import store from '@/store';

const routes = [

    { path: '/', name: 'Main', component: () => import( '@/pages/home/Index.vue' )},
    { path: '/inicio', name: 'Tablero', component: () => import( '@/pages/home/Index.vue' )},
    { path: '/kpi', name: 'Tablero', component: () => import( '@/pages/home/Index.vue' )},
    {
        path: '/deportistas',
        name: 'Deportistas',
        component: () => import('@/pages/players/PlayersList.vue'),
    },
    {
        path: '/inscripciones',
        name: 'Inscripciones',
        component: () => import('@/pages/inscriptions/InscriptionsList.vue'),
    },
    {
        path: '/admin',
        name: 'Admin',
        children: [

            {path: 'escuela', name: 'Escuela', component: () => import('@/pages/admin/school/Index.vue')},
            {path: 'usuarios', name: 'Usuarios', component: () => import('@/pages/admin/users/UsersList.vue')},
            {path: 'g-entrenamiento', name: 'Grupos Entrenamiento', component: () => import('@/pages/admin/groups/trainingList.vue')},
            {path: 'g-competencia', name: 'Grupos Competencia', component: () => import('@/pages/admin/groups/competitionGList.vue')}
        ]
    }
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
    if (to.meta && to.meta.layout) {
        if (to.meta.layout == 'main') {
            store.commit('setLayout', 'main');
        } else if (to.meta.layout == 'auth') {
            store.commit('setLayout', 'auth');
        } else {
            store.commit('setLayout', 'app');
        }
    }
    next(true);
});

export default router;