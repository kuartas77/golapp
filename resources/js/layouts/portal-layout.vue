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
                    <a href="/login" class="btn btn-primary">
                        Ingreso Escuela
                    </a>
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

<style scoped>
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

:global(.dark) .portal-public .logo-light-mode,
:global(body.dark) .portal-public .logo-light-mode {
    display: inline-block;
}

:global(.dark) .portal-public .logo-dark-mode,
:global(body.dark) .portal-public .logo-dark-mode {
    display: none;
}

:global(.dark) .portal-public__topbar,
:global(body.dark) .portal-public__topbar {
    background: rgba(255, 255, 255, 0.72);
    border-bottom-color: rgba(59, 63, 92, 0.12);
}

:global(.dark) .portal-light-surface,
:global(body.dark) .portal-light-surface {
    color: #3b3f5c;
    background: #ffffff;
}

:global(.dark) .portal-light-surface :is(h1, h2, h3, h4, h5, h6),
:global(body.dark) .portal-light-surface :is(h1, h2, h3, h4, h5, h6) {
    color: #3b3f5c;
}

:global(.dark) .portal-light-surface .card,
:global(body.dark) .portal-light-surface .card,
:global(.dark) .portal-light-surface .modal-content,
:global(body.dark) .portal-light-surface .modal-content,
:global(.dark) .portal-light-surface .list-group-item,
:global(body.dark) .portal-light-surface .list-group-item {
    color: #3b3f5c;
    background: #ffffff;
    border-color: rgba(59, 63, 92, 0.12);
}

:global(.dark) .portal-light-surface .text-muted,
:global(body.dark) .portal-light-surface .text-muted {
    color: #888ea8 !important;
}

:global(.dark) .portal-light-surface .form-control,
:global(body.dark) .portal-light-surface .form-control,
:global(.dark) .portal-light-surface .form-select,
:global(body.dark) .portal-light-surface .form-select {
    color: #3b3f5c;
    background-color: #ffffff;
    border-color: #e0e6ed;
}

:global(.dark) .portal-light-surface label,
:global(body.dark) .portal-light-surface label,
:global(.dark) .portal-light-surface .form-label,
:global(body.dark) .portal-light-surface .form-label {
    color: #3b3f5c;
}

:global(.dark) .portal-light-surface .form-control:disabled,
:global(body.dark) .portal-light-surface .form-control:disabled,
:global(.dark) .portal-light-surface .form-control[readonly],
:global(body.dark) .portal-light-surface .form-control[readonly] {
    color: #6c757d;
    background-color: #f8f9fa !important;
}

:global(.dark) .portal-light-surface .modal-header,
:global(body.dark) .portal-light-surface .modal-header,
:global(.dark) .portal-light-surface .modal-footer,
:global(body.dark) .portal-light-surface .modal-footer,
:global(.dark) .portal-light-surface .nav-tabs,
:global(body.dark) .portal-light-surface .nav-tabs {
    border-color: #e0e6ed !important;
}

:global(.dark) .portal-light-surface .nav-tabs .nav-link.active,
:global(body.dark) .portal-light-surface .nav-tabs .nav-link.active {
    color: #4361ee;
    background-color: #ffffff;
    border-color: #e0e6ed #e0e6ed #ffffff;
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
