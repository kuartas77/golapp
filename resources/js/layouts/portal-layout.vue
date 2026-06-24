<template>
    <div class="portal-public portal-light-surface">
        <header class="portal-public__topbar">
            <div class="container portal-public__topbar-inner">
                <a class="portal-public__brand" aria-label="Logo GOLAPP">
                    <img src="/img/logo-light.svg" alt="Logo GOLAPP" class="portal-public__brand-logo logo-light-mode" />
                    <img src="/img/logo-dark.svg" alt="Logo GOLAPP" class="portal-public__brand-logo logo-dark-mode" />
                </a>

                <nav class="portal-public__nav">
                    <router-link :to="{ name: 'guardian-login' }" class="btn btn-outline-primary">
                        Ingreso Acudiente
                    </router-link>
                    <router-link :to="{ name: 'login' }" class="btn btn-primary">
                        Ingreso Escuela
                    </router-link>
                </nav>
            </div>
        </header>

        <main class="portal-public__main">
            <router-view />
        </main>
    </div>
</template>

<script setup>
</script>

<style>
.portal-public {
    min-height: 100vh;
    color: #3b3f5c;
    background: #ffffff;
}

.portal-public__topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.72);
    border-bottom: 1px solid rgba(59, 63, 92, 0.12);
}

.portal-public__topbar-inner {
    min-height: 76px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.portal-public__brand {
    display: inline-flex;
    align-items: center;
}

.portal-public__brand-logo {
    width: 148px;
    height: 33px;
    object-fit: contain;
}

.logo-dark-mode {
    display: none;
}

.portal-public__nav {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.portal-public__nav .btn {
    white-space: nowrap;
}

.portal-public__main {
    padding: 1.5rem 0 2.5rem;
}

@media (max-width: 767.98px) {
    .portal-public__topbar-inner {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        align-items: flex-start;
        flex-direction: column;
    }

    .portal-public__nav {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
