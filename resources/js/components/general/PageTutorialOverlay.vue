<template>
    <TutorialOverLay
        :open="overlay.open"
        :current-index="overlay.currentIndex"
        :total-steps="overlay.totalSteps"
        :current-step="overlay.currentStep"
        :target-rect="overlay.targetRect"
        @close="tutorial.close"
        @skip="tutorial.close"
        @next="tutorial.next"
        @prev="tutorial.prev"
    />
</template>

<script setup>
import { computed, unref } from 'vue'

import TutorialOverLay from './TutorialOverLay.vue'

const props = defineProps({
    tutorial: {
        type: Object,
        required: true,
    },
})

const overlay = computed(() => {
    return unref(props.tutorial.overlayBindings) || {
        open: false,
        currentIndex: 0,
        totalSteps: 0,
        currentStep: null,
        targetRect: null,
    }
})
</script>
