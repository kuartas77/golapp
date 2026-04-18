import { computed, unref } from 'vue'

import { useGuidedTour } from './useGuidedTour'

function normalizeIndex(index) {
    return Number.isInteger(index) ? index : 0
}

export function usePageTutorial(definition, context = {}) {
    const resolvedDefinition = computed(() => unref(definition) || {})
    const steps = computed(() => {
        const tutorialDefinition = resolvedDefinition.value

        if (typeof tutorialDefinition.getSteps === 'function') {
            return tutorialDefinition.getSteps(context) || []
        }

        return unref(tutorialDefinition.steps) || []
    })

    let tutorial = null

    function createPayload(step = null, index = 0) {
        return {
            context,
            tutorial,
            step,
            index,
        }
    }

    const guidedTour = useGuidedTour({
        steps,
        onBeforeStepChange: async (step, index) => {
            const tutorialDefinition = resolvedDefinition.value
            const payload = createPayload(step, index)

            if (typeof tutorialDefinition.onBeforeStepChange === 'function') {
                await tutorialDefinition.onBeforeStepChange(payload)
            }

            if (typeof step?.beforeEnter === 'function') {
                await step.beforeEnter(payload)
            }
        },
        onAfterOpen: async (step) => {
            const tutorialDefinition = resolvedDefinition.value
            const payload = createPayload(step, guidedTour.currentIndex.value)

            if (typeof tutorialDefinition.onAfterOpen === 'function') {
                await tutorialDefinition.onAfterOpen(payload)
            }
        },
        onAfterClose: async () => {
            const tutorialDefinition = resolvedDefinition.value
            const payload = createPayload(guidedTour.currentStep.value, guidedTour.currentIndex.value)

            if (typeof tutorialDefinition.onAfterClose === 'function') {
                await tutorialDefinition.onAfterClose(payload)
            }
        },
    })

    async function start(startIndex = 0) {
        const tutorialDefinition = resolvedDefinition.value
        const nextIndex = normalizeIndex(startIndex)
        const nextStep = steps.value[nextIndex] || null
        const payload = createPayload(nextStep, nextIndex)

        if (typeof tutorialDefinition.onBeforeOpen === 'function') {
            await tutorialDefinition.onBeforeOpen(payload)
        }

        await guidedTour.openTour(nextIndex)
    }

    async function close() {
        guidedTour.closeTour()
    }

    tutorial = {
        ...guidedTour,
        steps,
        start,
        close,
        next: guidedTour.nextStep,
        prev: guidedTour.prevStep,
        overlayBindings: computed(() => ({
            open: guidedTour.isOpen.value,
            currentIndex: guidedTour.currentIndex.value,
            currentStep: guidedTour.currentStep.value,
            totalSteps: guidedTour.totalSteps.value,
            targetRect: guidedTour.targetRect.value,
        })),
    }

    return tutorial
}
