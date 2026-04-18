<template>
    <div>
        <!--  BEGIN NAVBAR  -->
        <div class="header-container fixed-top" id="header">
            <header class="header navbar navbar-expand-sm">
                <ul class="navbar-item theme-brand flex-row text-center">
                    <li class="nav-item theme-logo">
                        <router-link :to="{ name: 'dashboard' }">
                            <img src="/img/ball-dark.webp" class="navbar-logo" alt="logo" />
                        </router-link>
                    </li>
                    <li class="nav-item theme-text">
                        <router-link :to="{ name: 'dashboard' }" class="nav-link"> GOLAPP </router-link>
                    </li>
                </ul>
                <div class="d-none horizontal-menu">
                    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"
                        @click="appState.toggleSideBar(!appState.is_show_sidebar)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-menu">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </a>
                </div>
                <!-- <ul class="navbar-item flex-row ms-md-0 ms-auto">
                    <li class="nav-item align-self-center search-animated" :class="{ 'show-search': appState.is_show_search }">
                        <svg
                            @click="appState.toggleSearch(!appState.is_show_search)"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-search toggle-search"
                        >
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <form class="form-inline search-full form-inline search" :class="{ 'input-focused': appState.is_show_search }">
                            <div class="search-bar">
                                <input type="text" class="form-control search-form-control ms-lg-auto" placeholder="Search..." />
                            </div>
                        </form>
                    </li>
                </ul> -->

                <div class="navbar-item flex-row ms-md-auto">
                    <SchoolSelecter></SchoolSelecter>
                    <div class="dark-mode d-flex align-items-center">
                        <a v-if="appState.dark_mode == 'light'" href="javascript:;" class="d-flex align-items-center"
                            @click="toggleMode('dark')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-sun">
                                <circle cx="12" cy="12" r="5"></circle>
                                <line x1="12" y1="1" x2="12" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="23"></line>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                <line x1="1" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="12" x2="23" y2="12"></line>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                            </svg>
                            <span class="ms-2">Claro</span>
                        </a>
                        <a v-if="appState.dark_mode == 'dark'" href="javascript:;" class="d-flex align-items-center"
                            @click="toggleMode('system')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-moon">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                            <span class="ms-2">Oscuro</span>
                        </a>
                        <a v-if="appState.dark_mode == 'system'" href="javascript:;" class="d-flex align-items-center"
                            @click="toggleMode('light')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-airplay">
                                <path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1">
                                </path>
                                <polygon points="12 15 17 21 7 21 12 15"></polygon>
                            </svg>
                            <span class="ms-2">Sistema</span>
                        </a>
                    </div>

                    <HeaderNotificationsDropdown />

                    <div class="dropdown nav-item user-profile-dropdown btn-group">
                        <a href="javascript:;" id="ddluser" data-bs-toggle="dropdown" aria-expanded="false"
                            class="btn dropdown-toggle btn-icon-only user nav-link">
                            <img :src="urlImgAvatar" alt="avatar" />
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right m-0" aria-labelledby="ddluser">
                            <li role="presentation">
                                <router-link :to="{ name: 'user-profile' }" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-user">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Profile
                                </router-link>
                            </li>
                            <li role="presentation" v-if="userState.isAuthenticated">
                                <a href="javascript:void(0);" @click="logout" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-log-out">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Salir
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
        </div>
        <!--  END NAVBAR  -->
        <!--  BEGIN NAVBAR  -->
        <div class="sub-header-container" id="sub-header-container">
            <header class="header navbar navbar-expand-sm">
                <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom" @click="toggleSideBar()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-menu">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </a>

                <!-- Portal vue/Teleport for Breadcrumb -->
                <div id="breadcrumb" class="vue-portal-target"></div>
            </header>
        </div>
        <!--  END NAVBAR  -->
    </div>
</template>

<script setup>
import { useAppState } from '@/store/app-state'
import { useAuthUser } from '@/store/auth-user'
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router'
import HeaderNotificationsDropdown from './HeaderNotificationsDropdown.vue';
import SchoolSelecter from '../general/SchoolSelecter.vue';

const appState = useAppState()
const userState = useAuthUser()
const router = useRouter()
const selectedLang = ref(null);
const userName = userState.user?.name?.replace(' ', '+')
const urlImgAvatar = ref(`https://ui-avatars.com/api/?name=${userName}`)

onMounted(() => {
    selectedLang.value = window.$appSetting.toggleLanguage();
    toggleMode();
});

const toggleMode = (mode) => {
    window.$appSetting.toggleMode(mode);
};

const logout = async () => {
    await userState.logout()
    router.push({ name: 'login' })
}

const toggleSideBar = () => {
    appState.toggleSideBar(!appState.is_show_sidebar)
}

</script>
