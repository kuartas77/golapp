<script setup>
import { computed } from 'vue'
import { useAuthUser } from '@/store/auth-user'

const auth = useAuthUser()

const props = defineProps({
    // ---- Permisos ----
    permission: { type: String, default: null },
    permissions: { type: Array, default: () => [] },
    any: { type: Boolean, default: false },

    // ---- Roles ----
    role: { type: String, default: null },
    roles: { type: Array, default: () => [] },
    anyRole: { type: Boolean, default: false }
})

// ---- Lógica de visibilidad ----
const visible = computed(() => {
    const checks = []

    if (props.permission) {
        checks.push(auth.can(props.permission))
    }

    if (props.permissions.length > 0) {
        if (props.any) {
            checks.push(auth.canAny(props.permissions))
        } else {
            checks.push(props.permissions.every(p => auth.can(p)))
        }
    }

    if (props.role) {
        checks.push(auth.hasRole(props.role))
    }

    if (props.roles.length > 0) {
        if (props.anyRole) {
            checks.push(props.roles.some(r => auth.hasRole(r)))
        } else {
            checks.push(props.roles.every(r => auth.hasRole(r)))
        }
    }

    return checks.length > 0 && checks.every(Boolean)
})
</script>

<template>
    <template v-if="visible">
        <slot />
    </template>
    <template v-else>
        <slot name="else" />
    </template>
</template>
