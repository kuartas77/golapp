import { computed, nextTick, onBeforeUnmount, onMounted, ref, unref, watch } from 'vue'

export function useGuidedTour(options = {}) {
    const {
        steps = [],
        defaultPadding = 10,
        defaultScrollBehavior = 'smooth',
        onBeforeStepChange = null,
        onAfterOpen = null,
        onAfterClose = null,
    } = options

    const isOpen = ref(false)
    const currentIndex = ref(0)
    const targetRect = ref(null)

    const resolvedSteps = computed(() => unref(steps) || [])
    const totalSteps = computed(() => resolvedSteps.value.length)

    const currentStep = computed(() => {
        if (!totalSteps.value) return null
        return resolvedSteps.value[currentIndex.value] || null
    })

    const canGoPrev = computed(() => currentIndex.value > 0)
    const canGoNext = computed(() => currentIndex.value < totalSteps.value - 1)

    let rafId = null

    async function openTour(startIndex = 0) {
        if (!totalSteps.value) return

        currentIndex.value = normalizeIndex(startIndex)
        isOpen.value = true

        await nextTick()
        await syncStep()

        if (typeof onAfterOpen === 'function') {
            onAfterOpen(currentStep.value)
        }
    }

    function closeTour() {
        isOpen.value = false
        targetRect.value = null

        if (typeof onAfterClose === 'function') {
            onAfterClose()
        }
    }

    async function nextStep() {
        if (!canGoNext.value) {
            closeTour()
            return
        }

        await goToStep(currentIndex.value + 1)
    }

    async function prevStep() {
        if (!canGoPrev.value) return
        await goToStep(currentIndex.value - 1)
    }

    async function goToStep(index) {
        if (!totalSteps.value) return

        const nextIndex = normalizeIndex(index)
        const nextStepValue = resolvedSteps.value[nextIndex]

        if (typeof onBeforeStepChange === 'function') {
            await onBeforeStepChange(nextStepValue, nextIndex)
        }

        currentIndex.value = nextIndex

        await nextTick()
        await syncStep()
    }

    function normalizeIndex(index) {
        if (!totalSteps.value) return 0
        if (index < 0) return 0
        if (index > totalSteps.value - 1) return totalSteps.value - 1
        return index
    }

    async function syncStep() {
        const step = currentStep.value
        if (!step) {
            targetRect.value = null
            return
        }

        await nextTick()
        scrollToTarget(step)
        await waitForScroll()
        updateTargetRect()
    }

    function getTargetElement(step = currentStep.value) {
        if (!step?.selector) return null
        return document.querySelector(step.selector)
    }

    function scrollToTarget(step = currentStep.value) {
        const target = getTargetElement(step)
        if (!target) {
            targetRect.value = null
            return
        }

        target.scrollIntoView({
            behavior: step.scrollBehavior || defaultScrollBehavior,
            block: step.scrollBlock || 'center',
            inline: step.scrollInline || 'center',
        })
    }

    function updateTargetRect() {
        const step = currentStep.value
        const target = getTargetElement(step)

        if (!step || !target) {
            targetRect.value = null
            return
        }

        const rect = target.getBoundingClientRect()
        const padding = Number(step.padding ?? defaultPadding)

        targetRect.value = {
            top: Math.max(0, rect.top - padding),
            left: Math.max(0, rect.left - padding),
            width: rect.width + padding * 2,
            height: rect.height + padding * 2,
            bottom: rect.bottom + padding,
            right: rect.right + padding,
            centerX: rect.left + rect.width / 2,
            centerY: rect.top + rect.height / 2,
        }
    }

    function waitForScroll() {
        return new Promise((resolve) => {
            cancelAnimationFrame(rafId)
            rafId = requestAnimationFrame(() => {
                rafId = requestAnimationFrame(resolve)
            })
        })
    }

    function handleViewportChange() {
        if (!isOpen.value) return
        updateTargetRect()
    }

    watch(
        () => isOpen.value,
        (open) => {
            if (!open) {
                targetRect.value = null
            }
        }
    )

    onMounted(() => {
        window.addEventListener('resize', handleViewportChange)
        window.addEventListener('scroll', handleViewportChange, true)
    })

    onBeforeUnmount(() => {
        window.removeEventListener('resize', handleViewportChange)
        window.removeEventListener('scroll', handleViewportChange, true)
        cancelAnimationFrame(rafId)
    })

    return {
        isOpen,
        currentIndex,
        currentStep,
        totalSteps,
        targetRect,
        canGoPrev,
        canGoNext,
        openTour,
        closeTour,
        nextStep,
        prevStep,
        goToStep,
        updateTargetRect,
    }
}
