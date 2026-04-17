<template>
    <!--  BEGIN SIDEBAR  -->
    <div class="sidebar-wrapper sidebar-theme" id="sidebar">
        <nav ref="menu" id="sidebar">
            <div class="shadow-bottom"></div>

            <perfect-scrollbar class="list-unstyled menu-categories" tag="ul"
                :options="{ wheelSpeed: 0.5, swipeEasing: !0, minScrollbarLength: 40, maxScrollbarLength: 300, suppressScrollX: true }">
                <li class="menu">
                    <a class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#dashboard"
                        aria-controls="dashboard" aria-expanded="false">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-home">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span>{{ $t('dashboard') }}</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>

                    <ul id="dashboard" class="collapse submenu list-unstyled" data-bs-parent="#sidebar">
                        <li>
                            <router-link :to="{ name: 'dashboard' }" @click="toggleMobileMenu">
                                Inicio
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{ name: 'kpi' }" @click="toggleMobileMenu">
                                KPI
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{ name: 'player-stats.index' }" @click="toggleMobileMenu">
                                Estadísticas Jugador
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li v-if="showAdministrationMenu" class="menu">
                    <a class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#apps" aria-controls="apps"
                        aria-expanded="false">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-cpu">
                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                <rect x="9" y="9" width="6" height="6"></rect>
                                <line x1="9" y1="1" x2="9" y2="4"></line>
                                <line x1="15" y1="1" x2="15" y2="4"></line>
                                <line x1="9" y1="20" x2="9" y2="23"></line>
                                <line x1="15" y1="20" x2="15" y2="23"></line>
                                <line x1="20" y1="9" x2="23" y2="9"></line>
                                <line x1="20" y1="14" x2="23" y2="14"></line>
                                <line x1="1" y1="9" x2="4" y2="9"></line>
                                <line x1="1" y1="14" x2="4" y2="14"></line>
                            </svg>
                            <span>Administración</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul id="apps" class="collapse submenu list-unstyled" data-bs-parent="#sidebar">
                        <li v-if="canEvaluationTemplates">
                            <router-link :to="{ name: 'schools' }" @click="toggleMobileMenu">Listado
                                Escuelas</router-link>
                        </li>
                        <li v-if="canEvaluationTemplates">
                            <router-link :to="{ name: 'schools-info' }" @click="toggleMobileMenu">Información
                                Escuelas</router-link>
                        </li>
                        <li v-if="canEvaluationTemplates">
                            <router-link :to="{ name: 'evaluation-templates.index' }" @click="toggleMobileMenu">
                                Plantillas evaluación
                            </router-link>
                        </li>
                        <li v-if="canSchoolProfile">
                            <router-link :to="{ name: 'school' }" @click="toggleMobileMenu">Escuela</router-link>
                        </li>
                        <li v-if="canUserManagement">
                            <router-link :to="{ name: 'users' }" @click="toggleMobileMenu">Usuarios</router-link>
                        </li>
                        <li v-if="canTrainingGroups">
                            <router-link :to="{ name: 'training-groups' }" @click="toggleMobileMenu">G.
                                Entrenamiento</router-link>
                        </li>
                        <li v-if="canCompetitionGroups">
                            <router-link :to="{ name: 'competition-groups' }" @click="toggleMobileMenu">G.
                                Competencia</router-link>
                        </li>
                    </ul>
                </li>

                <li v-if="canPlayers" class="menu">
                    <router-link :to="{ name: 'players' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-list">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                            <span>Deportistas</span>
                        </div>
                    </router-link>
                </li>

                <li v-if="canInscriptions" class="menu">
                    <router-link :to="{ name: 'inscriptions' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-check-square">
                                <polyline points="9 11 12 14 22 4"></polyline>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                            <span>Inscripciones</span>
                        </div>
                    </router-link>
                </li>

                <li v-if="canEvaluations" class="menu">
                    <router-link :to="{ name: 'player-evaluations.index' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-clipboard">
                                <path d="M9 2H15A2 2 0 0 1 17 4V6H7V4A2 2 0 0 1 9 2z"></path>
                                <path d="M9 2V4H15V2"></path>
                                <path d="M9 12H15"></path>
                                <path d="M9 16H13"></path>
                                <rect x="5" y="4" width="14" height="18" rx="2" ry="2"></rect>
                            </svg>
                            <span>Evaluaciones</span>
                        </div>
                    </router-link>
                </li>

                <li v-if="canAttendances" class="menu">
                    <router-link :to="{ name: 'attendances' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-user-check">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17 11 19 13 23 9"></polyline>
                            </svg>

                            <span>Asistencias</span>
                        </div>
                    </router-link>
                </li>
                <li v-if="canMatches" class="menu">
                    <router-link :to="{ name: 'matches' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" class="main-grid-item-icon">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>

                            <span>Competencias</span>
                        </div>
                    </router-link>
                </li>
                <li v-if="canPayments" class="menu">
                    <router-link :to="{ name: 'payments' }" class="dropdown-toggle" @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-dollar-sign">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <span>Mensualidades</span>
                        </div>
                    </router-link>
                </li>
                <li v-if="canReports" class="menu">
                    <a class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#reports"
                        aria-controls="reports" aria-expanded="false">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-flag">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                            <span>Informes</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul id="reports" class="collapse submenu list-unstyled" data-bs-parent="#sidebar">
                        <li>
                            <router-link :to="{ name: 'reports.assists' }" @click="toggleMobileMenu">
                                Asistencias
                            </router-link>
                        </li>
                        <li>
                            <router-link :to="{ name: 'reports.payments' }" @click="toggleMobileMenu">
                                Mensualidades
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li v-if="canBilling" class="menu">
                    <a class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#billing"
                        aria-controls="billing" aria-expanded="false">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-dollar-sign">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <span>Facturación</span>
                        </div>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-chevron-right">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                    </a>
                    <ul id="billing" class="collapse submenu list-unstyled" data-bs-parent="#sidebar">
                        <li>
                            <router-link :to="{ name: 'invoices.index' }" @click="toggleMobileMenu">
                                Facturas
                            </router-link>
                        </li>
                        <li v-if="canPaymentRequests">
                            <router-link :to="{ name: 'payment-requests.index' }" @click="toggleMobileMenu">
                                Comprobantes de Pago
                            </router-link>
                        </li>
                        <li v-if="canUniformRequests">
                            <router-link :to="{ name: 'uniform-requests.index' }" @click="toggleMobileMenu">
                                Solicitudes de Uniformes
                            </router-link>
                        </li>
                    </ul>
                </li>
                <li v-if="canTopicNotifications" class="menu">
                    <router-link :to="{ name: 'topic-notifications.index' }" class="dropdown-toggle"
                        @click="toggleMobileMenu">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-bell">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span>Notificaciones</span>
                        </div>
                    </router-link>
                </li>

            </perfect-scrollbar>
        </nav>
    </div>
    <!--  END SIDEBAR  -->
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useAppState } from '@/store/app-state'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'

const appState = useAppState();
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
const canPaymentRequests = access.paymentRequests
const canUniformRequests = access.uniformRequests
const canEvaluationTemplates = access.evaluationTemplates

const showAdministrationMenu = computed(() => (
    canEvaluationTemplates.value
    || canSchoolProfile.value
    || canUserManagement.value
    || canTrainingGroups.value
    || canCompetitionGroups.value
))

const toggleMobileMenu = () => {
    if (window.innerWidth < 991) {
        appState.toggleSideBar(!appState.is_show_sidebar);
    }
};

onMounted(() => {
    const selector = document.querySelector('#sidebar a[href="' + window.location.pathname + '"]');
    if (selector) {
        const ul = selector.closest('ul.collapse');
        if (ul) {
            let ele = ul.closest('li.menu').querySelectorAll('.dropdown-toggle');
            if (ele) {
                ele = ele[0];
                setTimeout(() => {
                    ele.click();
                });
            }
        } else {
            selector.click();
        }
    }
});
</script>
