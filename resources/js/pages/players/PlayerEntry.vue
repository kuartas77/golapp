<template>
    <PlayerReadonly v-if="isReadOnly" />
    <PlayerDetail v-else />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import PlayerDetail from './PlayerDetail.vue'
import PlayerReadonly from './PlayerReadonly.vue'

const auth = useAuthUser()
const isReadOnly = computed(() => auth.hasAnyRole(['assistant', 'viewer']))
</script>
