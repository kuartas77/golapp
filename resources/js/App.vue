<template>
    <div :class="[appState.layout_style, appState.menu_style]">
        <router-view></router-view>
    </div>
</template>
<script setup>
import "@/assets/sass/app.scss"
import { useAppState } from "@/store/app-state"
import { useMeta } from "@/composables/use-meta"

const appState = useAppState()
useMeta({ title: "GOLAPP" })
</script>
