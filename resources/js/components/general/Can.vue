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
    // ======= PERMISO ÚNICO =======
    if (props.permission) {
        return auth.can(props.permission)
    }

    // ======= MÚLTIPLES PERMISOS =======
    if (props.permissions.length > 0) {
        if (props.any) {
            return auth.canAny(props.permissions) // OR
        }
        return props.permissions.every(p => auth.can(p)) // AND
    }

    // ======= ROL ÚNICO =======
    if (props.role) {
        return auth.hasRole(props.role)
    }

    // ======= MÚLTIPLES ROLES =======
    if (props.roles.length > 0) {
        if (props.anyRole) {
            return props.roles.some(r => auth.hasRole(r)) // OR
        }
        return props.roles.every(r => auth.hasRole(r)) // AND
    }

    // Nada definido = no mostrar
    return false
})
</script>

<template>
    <template v-if="visible">
        <slot />
    </template>
</template>