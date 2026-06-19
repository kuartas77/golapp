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

.dark .portal-public .logo-light-mode,
body.dark .portal-public .logo-light-mode {
    display: inline-block;
}

.dark .portal-public .logo-dark-mode,
body.dark .portal-public .logo-dark-mode {
    display: none;
}

.dark .portal-public__topbar,
body.dark .portal-public__topbar {
    background: rgba(255, 255, 255, 0.72);
    border-bottom-color: rgba(59, 63, 92, 0.12);
}

.dark .portal-light-surface,
body.dark .portal-light-surface {
    color: #3b3f5c;
    background: #ffffff;
}

.dark .portal-light-surface :is(h1, h2, h3, h4, h5, h6),
body.dark .portal-light-surface :is(h1, h2, h3, h4, h5, h6) {
    color: #3b3f5c;
}

.dark .portal-light-surface .card,
body.dark .portal-light-surface .card,
.dark .portal-light-surface .modal-content,
body.dark .portal-light-surface .modal-content,
.dark .portal-light-surface .list-group-item,
body.dark .portal-light-surface .list-group-item {
    color: #3b3f5c;
    background: #ffffff;
    border-color: rgba(59, 63, 92, 0.12);
}

.dark .portal-light-surface .text-muted,
body.dark .portal-light-surface .text-muted {
    color: #888ea8 !important;
}

.dark .portal-light-surface .form-control,
body.dark .portal-light-surface .form-control,
.dark .portal-light-surface .form-select,
body.dark .portal-light-surface .form-select {
    color: #3b3f5c;
    background-color: #ffffff;
    border-color: #e0e6ed;
}

.dark .portal-light-surface label,
body.dark .portal-light-surface label,
.dark .portal-light-surface .form-label,
body.dark .portal-light-surface .form-label {
    color: #3b3f5c;
}

.dark .portal-light-surface .form-control:disabled,
body.dark .portal-light-surface .form-control:disabled,
.dark .portal-light-surface .form-control[readonly],
body.dark .portal-light-surface .form-control[readonly] {
    color: #6c757d;
    background-color: #f8f9fa !important;
}

.dark .portal-light-surface .modal-header,
body.dark .portal-light-surface .modal-header,
.dark .portal-light-surface .modal-footer,
body.dark .portal-light-surface .modal-footer,
.dark .portal-light-surface .nav-tabs,
body.dark .portal-light-surface .nav-tabs {
    border-color: #e0e6ed !important;
}

.dark .portal-light-surface .nav-tabs .nav-link.active,
body.dark .portal-light-surface .nav-tabs .nav-link.active {
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
