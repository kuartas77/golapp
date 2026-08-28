<template>
    <PlayerReadonly v-if="isAssistant" />
    <PlayerDetail v-else />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'
import PlayerDetail from './PlayerDetail.vue'
import PlayerReadonly from './PlayerReadonly.vue'

const auth = useAuthUser()
const isAssistant = computed(() => auth.hasRole('assistant'))
</script>
