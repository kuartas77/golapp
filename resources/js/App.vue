<template>
    <div :class="[$store.state.appState.layout_style, $store.state.appState.menu_style]">
        <router-view></router-view>
    </div>
</template>
<script setup>
    // import { computed } from "vue";

    import "@/assets/sass/app.scss";

    import { useMeta } from "@/composables/use-meta";
    // import { useStore } from "vuex";

    useMeta({ title: "GOLAPP" });

    // const store = useStore();

    // const layout = computed(() => {
    //     return store.getters.layout;
    // });
</script>
