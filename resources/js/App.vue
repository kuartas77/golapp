<template>
    <div :class="[appState.layout_style, appState.menu_style]">
        <router-view></router-view>
        <Loader :is-loading="appState.isGlobalLoading" loading-text="Cargando..." fullscreen />
    </div>
</template>
<script setup>
import "@/assets/sass/app.scss"
import { useAppState } from "@/store/app-state"
import { useMeta } from "@/composables/use-meta"
import Loader from '@/components/general/Loader.vue'

const appState = useAppState()
useMeta({ title: "GOLAPP" })
</script>
