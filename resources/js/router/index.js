import { createRouter, createWebHistory } from 'vue-router';
import store from '@/store';

const routes = [

    { path: '/', name: 'Main', component: () => import( '@/pages/home/Index.vue' )},
    { path: '/inicio', name: 'Tablero', component: () => import( '@/pages/home/Index.vue' )},
    { path: '/kpi', name: 'Tablero', component: () => import( '@/pages/home/Index.vue' )},
    {
        path: '/deportistas',
        name: 'Deportistas',
        component: () => import('@/pages/players/Index.vue'),
    },
    {
        path: '/inscripciones',
        name: 'Inscripciones',
        component: () => import('@/pages/inscriptions/Index.vue'),
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