import { onBeforeUnmount, onMounted, unref } from 'vue'
import { onBeforeRouteLeave } from 'vue-router'

const readSource = (source) => typeof source === 'function' ? source() : unref(source)

export function useUnsavedChangesGuard({
    isDirty,
    isSaving = false,
    title = '¿Salir sin guardar?',
    message = 'Los cambios que realizaste se perderán si sales de esta pantalla.',
} = {}) {
    let skipNextNavigation = false
    let confirmationPromise = null

    const hasUnsavedChanges = () => Boolean(readSource(isDirty))
    const saveInProgress = () => Boolean(readSource(isSaving))

    const confirmDiscardChanges = async () => {
        if (!hasUnsavedChanges()) {
            return true
        }

        if (saveInProgress()) {
            return false
        }

        if (confirmationPromise) {
            return confirmationPromise
        }

        confirmationPromise = (async () => {
            if (window.Swal?.fire) {
                const result = await window.Swal.fire({
                    title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Salir sin guardar',
                    cancelButtonText: 'Seguir editando',
                    focusCancel: true,
                })

                return Boolean(result.isConfirmed)
            }

            return window.confirm(message)
        })()

        try {
            return await confirmationPromise
        } finally {
            confirmationPromise = null
        }
    }

    const skipGuardOnce = () => {
        skipNextNavigation = true
    }

    const beforeUnload = (event) => {
        if (!hasUnsavedChanges()) {
            return
        }

        event.preventDefault()
        event.returnValue = ''
    }

    onBeforeRouteLeave(async () => {
        if (skipNextNavigation) {
            skipNextNavigation = false
            return true
        }

        return confirmDiscardChanges()
    })

    onMounted(() => window.addEventListener('beforeunload', beforeUnload))
    onBeforeUnmount(() => window.removeEventListener('beforeunload', beforeUnload))

    return {
        confirmDiscardChanges,
        hasUnsavedChanges,
        skipGuardOnce,
    }
}
