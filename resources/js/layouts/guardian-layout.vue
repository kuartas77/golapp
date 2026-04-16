<template>
    <div class="guardian-portal">
        <header class="guardian-portal__topbar">
            <div class="container guardian-portal__topbar-inner">
                <a class="guardian-portal__brand" aria-label="Logo GOLAPP">
                    <img src="/img/logo-light.svg" alt="Logo GOLAPP" class="guardian-portal__brand-logo logo-light-mode" />
                    <img src="/img/logo-dark.svg" alt="Logo GOLAPP" class="guardian-portal__brand-logo logo-dark-mode" />
                </a>

                <div class="guardian-portal__actions">
                    <div v-if="guardian" class="guardian-portal__welcome">
                        <span class="guardian-portal__welcome-label">Acudiente</span>
                        <strong>{{ guardian.names }}</strong>
                    </div>

                    <nav class="guardian-portal__nav">

                        <template v-if="guardianStore.isAuthenticated">
                            <router-link :to="{ name: 'guardian-dashboard' }" class="btn btn-outline-primary">
                                Mis jugadores
                            </router-link>
                            <router-link :to="{ name: 'guardian-profile' }" class="btn btn-outline-primary">
                                Mi perfil
                            </router-link>
                            <button type="button" class="btn btn-primary" @click="handleLogout">
                                Cerrar sesión
                            </button>
                        </template>

                        <template v-else>
                            <router-link :to="{ name: 'guardian-login' }" class="btn btn-outline-primary">
                                Ingreso acudiente
                            </router-link>
                            <a href="/login" class="btn btn-primary">
                                Ingreso escuela
                            </a>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <main class="guardian-portal__main">
            <div class="container">
                <router-view />
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useGuardianAuth } from '@/store/guardian-auth';

const router = useRouter();
const guardianStore = useGuardianAuth();
const guardian = computed(() => guardianStore.user);

const handleLogout = async () => {
    await guardianStore.logout();
    await router.push({ name: 'guardian-login' });
};
</script>

<style scoped>
.guardian-portal {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(15, 28, 70, 0.12), transparent 35%),
        linear-gradient(180deg, #f7f9fd 0%, #eef3fb 100%);
}

.guardian-portal__topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.82);
    border-bottom: 1px solid rgba(59, 63, 92, 0.12);
}

.guardian-portal__topbar-inner {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.guardian-portal__brand {
    display: inline-flex;
    align-items: center;
}

.guardian-portal__brand-logo {
    width: 148px;
    height: 33px;
    object-fit: contain;
}

.logo-dark-mode {
    display: none;
}

.guardian-portal__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.guardian-portal__welcome {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.1;
    color: #0f1c46;
}

.guardian-portal__welcome-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #5f6b85;
}

.guardian-portal__nav {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.guardian-portal__main {
    padding: 2rem 0 3rem;
}

:global(.dark) .guardian-portal .logo-light-mode,
:global(body.dark) .guardian-portal .logo-light-mode {
    display: none;
}

:global(.dark) .guardian-portal .logo-dark-mode,
:global(body.dark) .guardian-portal .logo-dark-mode {
    display: block;
}

:global(.dark) .guardian-portal__topbar,
:global(body.dark) .guardian-portal__topbar {
    background: rgba(14, 23, 38, 0.84);
    border-bottom-color: rgba(136, 142, 168, 0.18);
}

:global(.dark) .guardian-portal__welcome,
:global(body.dark) .guardian-portal__welcome {
    color: #f5f7ff;
}

:global(.dark) .guardian-portal__welcome-label,
:global(body.dark) .guardian-portal__welcome-label {
    color: #b9c2db;
}

@media (max-width: 767.98px) {
    .guardian-portal__topbar-inner {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        align-items: flex-start;
        flex-direction: column;
    }

    .guardian-portal__actions,
    .guardian-portal__nav {
        width: 100%;
        justify-content: flex-start;
    }

    .guardian-portal__welcome {
        align-items: flex-start;
    }
}
</style>
