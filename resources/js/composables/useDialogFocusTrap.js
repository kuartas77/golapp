import { nextTick, onBeforeUnmount, watch } from 'vue'

const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',')

export function useDialogFocusTrap(dialogRef, isOpen, { onEscape } = {}) {
    let focusOrigin = null

    const focusableElements = () => Array.from(dialogRef.value?.querySelectorAll(FOCUSABLE_SELECTOR) ?? [])
        .filter((element) => element.getAttribute('aria-hidden') !== 'true')

    const handleKeydown = (event) => {
        if (event.key === 'Escape' && onEscape) {
            event.preventDefault()
            onEscape()
            return
        }

        if (event.key !== 'Tab') {
            return
        }

        const elements = focusableElements()
        if (!elements.length) {
            event.preventDefault()
            dialogRef.value?.focus()
            return
        }

        const first = elements[0]
        const last = elements[elements.length - 1]

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault()
            last.focus()
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault()
            first.focus()
        }
    }

    const deactivate = () => {
        dialogRef.value?.removeEventListener('keydown', handleKeydown)

        if (focusOrigin?.isConnected) {
            focusOrigin.focus({ preventScroll: true })
        }

        focusOrigin = null
    }

    watch(isOpen, async (open) => {
        if (!open) {
            deactivate()
            return
        }

        focusOrigin = document.activeElement instanceof HTMLElement ? document.activeElement : null
        await nextTick()

        const dialog = dialogRef.value
        if (!dialog) {
            return
        }

        dialog.addEventListener('keydown', handleKeydown)
        const [first] = focusableElements()
        const focusTarget = first ?? dialog
        focusTarget.focus({ preventScroll: true })
    })

    onBeforeUnmount(deactivate)
}
