<template>
    <div v-if="canViewNotifications" class="dropdown nav-item notification-dropdown btn-group">
        <a
            :id="dropdownId"
            href="javascript:;"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            class="btn dropdown-toggle btn-icon-only nav-link"
            @click="refreshIfNeeded"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="feather feather-bell">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span v-if="hasPendingNotifications" class="badge badge-success"></span>
        </a>

        <ul class="dropdown-menu dropdown-menu-right m-0" :aria-labelledby="dropdownId">
            <li role="presentation">
                <span class="dropdown-item fw-semibold header-notifications-dropdown__title">
                    {{ notificationTitle }}
                </span>
            </li>

            <li v-if="isLoading && !hasLoaded" role="presentation">
                <span class="dropdown-item">
                    Cargando notificaciones...
                </span>
            </li>

            <li v-for="item in notificationItems" :key="item.key" role="presentation">
                <router-link :to="{ name: item.routeName }" class="dropdown-item">
                    <div class="media">
                        <div class="media-aside align-self-start">
                            <svg v-if="item.key === 'uniform_requests'" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-bag">
                                <path d="M6 2l1.5 4"></path>
                                <path d="M18 2l-1.5 4"></path>
                                <path d="M3 7h18l-1 13H4L3 7z"></path>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                        </div>

                        <div class="media-body">
                            <div class="data-info">
                                <h6 class="mb-1">{{ item.title }}</h6>
                                <p class="notification-meta-time mb-0">{{ item.description }}</p>
                            </div>
                        </div>

                        <div class="icon-status ms-2 align-self-center">
                            <span class="badge badge-info header-notifications-dropdown__count">{{ item.count }}</span>
                        </div>
                    </div>
                </router-link>
            </li>

            <li v-if="!isLoading && hasLoaded && !errorMessage && !notificationItems.length" role="presentation">
                <span class="dropdown-item">
                    No hay notificaciones
                </span>
            </li>

            <li v-if="errorMessage && !notificationItems.length" role="presentation">
                <button type="button" class="dropdown-item text-start" @click="fetchSummary({ force: true })">
                    {{ errorMessage }}
                </button>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'
import api from '@/utils/axios'

const REFRESH_INTERVAL_MS = 60000
const STALE_TIME_MS = 15000
const dropdownId = `ddlnotify-${Math.random().toString(36).slice(2, 8)}`

const { access } = useBackofficeAccess()

const canViewNotifications = computed(() => access.paymentRequests.value || access.uniformRequests.value)
const isLoading = ref(false)
const hasLoaded = ref(false)
const errorMessage = ref('')
const lastFetchedAt = ref(0)
const summary = ref({
    payment_requests: 0,
    uniform_requests: 0,
    total: 0,
})

let refreshIntervalId = null
let visibilityListenerBound = false

const notificationItems = computed(() => {
    const items = []

    if (access.uniformRequests.value && summary.value.uniform_requests > 0) {
        items.push({
            key: 'uniform_requests',
            title: 'Solicitudes de uniformes',
            description: 'Pendientes por revisar o facturar.',
            count: summary.value.uniform_requests,
            routeName: 'uniform-requests.index',
        })
    }

    if (access.paymentRequests.value && summary.value.payment_requests > 0) {
        items.push({
            key: 'payment_requests',
            title: 'Comprobantes de pago',
            description: 'Pendientes por validar.',
            count: summary.value.payment_requests,
            routeName: 'payment-requests.index',
        })
    }

    return items
})

const hasPendingNotifications = computed(() => summary.value.total > 0)
const notificationTitle = computed(() => {
    const total = summary.value.total ?? 0
    return total === 1 ? '1 Notificación' : `${total} Notificaciones`
})

const resetSummary = () => {
    summary.value = {
        payment_requests: 0,
        uniform_requests: 0,
        total: 0,
    }
    hasLoaded.value = false
    lastFetchedAt.value = 0
    errorMessage.value = ''
}

const fetchSummary = async ({ force = false } = {}) => {
    if (!canViewNotifications.value) {
        resetSummary()
        return
    }

    if (isLoading.value) {
        return
    }

    const isFresh = lastFetchedAt.value && (Date.now() - lastFetchedAt.value) < STALE_TIME_MS

    if (!force && isFresh) {
        return
    }

    isLoading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/api/v2/notifications/header-summary', {
            skipGlobalLoader: true,
        })
        summary.value = {
            payment_requests: Number(response.data?.payment_requests ?? 0),
            uniform_requests: Number(response.data?.uniform_requests ?? 0),
            total: Number(response.data?.total ?? 0),
        }
        hasLoaded.value = true
        lastFetchedAt.value = Date.now()
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible actualizar. Toca para reintentar.'
    } finally {
        isLoading.value = false
    }
}

const refreshIfNeeded = () => {
    const isStale = !lastFetchedAt.value || (Date.now() - lastFetchedAt.value) >= STALE_TIME_MS

    if (isStale) {
        fetchSummary({ force: true })
    }
}

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        fetchSummary({ force: true })
    }
}

const bindVisibilityListener = () => {
    if (visibilityListenerBound) {
        return
    }

    document.addEventListener('visibilitychange', handleVisibilityChange)
    visibilityListenerBound = true
}

const unbindVisibilityListener = () => {
    if (!visibilityListenerBound) {
        return
    }

    document.removeEventListener('visibilitychange', handleVisibilityChange)
    visibilityListenerBound = false
}

const startPolling = () => {
    if (refreshIntervalId) {
        return
    }

    refreshIntervalId = window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchSummary({ force: true })
        }
    }, REFRESH_INTERVAL_MS)
}

const stopPolling = () => {
    if (!refreshIntervalId) {
        return
    }

    window.clearInterval(refreshIntervalId)
    refreshIntervalId = null
}

watch(canViewNotifications, (canView) => {
    if (canView) {
        fetchSummary({ force: true })
        bindVisibilityListener()
        startPolling()
        return
    }

    stopPolling()
    unbindVisibilityListener()
    resetSummary()
}, { immediate: true })

onBeforeUnmount(() => {
    stopPolling()
    unbindVisibilityListener()
})
</script>

<style scoped lang="scss">
.dropdown-item {
        color: #000000;
}
.dark {
    .dropdown-item {
        color: #fff;
    }
}

</style>
