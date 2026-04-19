<template>
    <div>
        <!--  BEGIN NAVBAR  -->
        <Header></Header>
        <!--  END NAVBAR  -->

        <!--  BEGIN MAIN CONTAINER  -->
        <div class="main-container" id="container"
            :class="[!appState.is_show_sidebar ? 'sidebar-closed sbar-open' : '', appState.menu_style === 'collapsible-vertical' ? 'collapsible-vertical-mobile' : '']">
            <!--  BEGIN OVERLAY  -->
            <div class="overlay" :class="{ show: !appState.is_show_sidebar }"
                @click="appState.toggleSideBar(!appState.is_show_sidebar)"></div>
            <div class="search-overlay" :class="{ show: appState.is_show_search }"
                @click="appState.toggleSearch(!appState.is_show_search)"></div>
            <!-- END OVERLAY -->

            <!--  BEGIN SIDEBAR  -->
            <Sidebar></Sidebar>
            <!--  END SIDEBAR  -->

            <!--  BEGIN CONTENT AREA  -->
            <div id="content" class="main-content">
                <router-view />

                <!-- BEGIN FOOTER -->
                <Footer></Footer>
                <!-- END FOOTER -->
            </div>
            <!--  END CONTENT AREA  -->

            <!-- BEGIN APP SETTING LAUNCHER -->
            <app-settings />
            <!-- END APP SETTING LAUNCHER -->
        </div>
    </div>
</template>

<script setup>
import Header from "@/components/layout/header.vue";
import Sidebar from "@/components/layout/sidebar.vue";
import Footer from "@/components/layout/footer.vue";
import appSettings from "@/components/app-settings.vue";
import { onMounted, onUnmounted } from 'vue'
import { useAppState } from '@/store/app-state'
import { USER_CONTEXT_REFRESH_INTERVAL_MS, useAuthUser } from '@/store/auth-user'
import { useRouter } from 'vue-router'
import { canAccessRoute } from '@/utils/routeAccess'
import useSettings from "@/composables/settingsComposable";
useSettings()
const appState = useAppState()
const authUser = useAuthUser()
const router = useRouter()
let syncUserContextIntervalId = null

const syncUserContext = async ({ force = false } = {}) => {
    if (!authUser.isAuthenticated) {
        return
    }

    await authUser.init({
        force,
        silent: true,
        preserveStateOnError: true,
    })

    if (!authUser.isAuthenticated) {
        return
    }

    if (!canAccessRoute(router.currentRoute.value, authUser)) {
        await router.replace({ name: 'dashboard' })
    }
}

const handleWindowFocus = () => {
    syncUserContext({ force: true })
}

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        syncUserContext({ force: true })
    }
}

onMounted(() => {
    window.addEventListener('focus', handleWindowFocus)
    document.addEventListener('visibilitychange', handleVisibilityChange)
    syncUserContextIntervalId = window.setInterval(() => {
        syncUserContext()
    }, USER_CONTEXT_REFRESH_INTERVAL_MS)
})

onUnmounted(() => {
    window.removeEventListener('focus', handleWindowFocus)
    document.removeEventListener('visibilitychange', handleVisibilityChange)

    if (syncUserContextIntervalId !== null) {
        window.clearInterval(syncUserContextIntervalId)
        syncUserContextIntervalId = null
    }
})

</script>
