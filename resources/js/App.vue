<template>
    <div :class="[appState.layout_style, appState.menu_style]">
        <router-view></router-view>
    </div>
</template>
<script setup>
import { useAppState } from '@/store/app-state'
import "@/assets/sass/app.scss";
import { useMeta } from "@/composables/use-meta";

const appState = useAppState()
useMeta({ title: "GOLAPP" });
</script>
