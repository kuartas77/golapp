<template>
    <div class="wizard-content">
        <div class="validation-wizard wizard-circle clearfix" :class="[
            options.cssClass,
            options.clearFixCssClass,
            options.stepsOrientation === stepsOrientation.vertical ? 'vertical' : ''
        ]" role="application">
            <!-- Navigation -->
            <div class="steps clearfix" role="tablist">
                <ul>
                    <li v-for="(step, index) in stepList" :key="index" :class="{
                        current: index === state.currentIndex,
                        done: index < state.currentIndex,
                        disabled: !isStepEnabled(index),
                        first: index === 0,
                        last: index === state.stepCount - 1
                    }" role="tab">
                        <a href="#" @click.prevent="goToStep(index)" :aria-controls="'step-' + index">
                            <span class="step">{{ index + 1 }}</span>
                            {{ step.title }}
                        </a>
                    </li>
                </ul>
            </div>

            <slot name="info"></slot>
            <!-- Content with transitions -->
            <div class="content clearfix">

                <transition :name="transitionName" :duration="options.transitionEffectSpeed" mode="out-in">
                    <div v-if="currentStep" :key="state.currentIndex" :id="'step-' + state.currentIndex" class="body"
                        role="tabpanel">
                        <!-- If the step is a Vue component slot, render it -->
                        <component v-if="currentStep.component" :is="currentStep.component" />

                        <!-- Iframe -->
                        <iframe v-else-if="currentStep.contentMode === contentMode.iframe" :src="currentStep.contentUrl"
                            frameborder="0" scrolling="no" style="width: 100%"></iframe>

                        <!-- Async -->
                        <div v-else-if="currentStep.contentMode === contentMode.async">
                            <div v-if="!currentStep.contentLoaded">
                                <span class="spinner"></span> {{ options.labels.loading }}
                            </div>
                            <div v-else v-html="currentStep.content"></div>
                        </div>

                        <!-- Fallback: raw HTML if provided -->
                        <div v-else-if="currentStep.content" v-html="currentStep.content"></div>
                    </div>
                </transition>
            </div>

            <!-- Actions -->
            <div class="actions clearfix" v-if="options.enablePagination">
                <ul role="menu" aria-label="Pagination">
                    <li v-if="!options.forceMoveForward && state.currentIndex > 0">
                        <a href="#" role="menuitem" @click.prevent="previousStep">
                            {{ options.labels.previous }}
                        </a>
                    </li>
                    <li v-show="!isLastStep">
                        <a href="#" role="menuitem" @click.prevent="nextStep">
                            {{ options.labels.next }}
                        </a>
                    </li>
                    <li v-if="options.enableFinishButton" v-show="isLastStep">
                        <a href="#" role="menuitem" @click.prevent="finishWizard" type="submit">
                            {{ options.labels.finish }}
                        </a>
                    </li>
                    <li v-if="options.enableCancelButton">
                        <a href="#" role="menuitem" @click.prevent="cancelWizard">
                            {{ options.labels.cancel }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'Wizard'
}
/* Enums */
export const contentMode = { html: 0, iframe: 1, async: 2 }
export const stepsOrientation = { horizontal: 0, vertical: 1 }
export const transitionEffect = { none: 0, fade: 1, slide: 2, slideLeft: 3 }
</script>
<script setup>
import { reactive, shallowRef, computed, onMounted, useSlots } from 'vue'
import Step from './Step.vue'

const defaultOptions = {
    cssClass: 'wizard',
    clearFixCssClass: 'clearfix',
    stepsOrientation: stepsOrientation.horizontal,
    enableAllSteps: false,
    enablePagination: true,
    enableCancelButton: false,
    enableFinishButton: true,
    enableContentCache: true,
    forceMoveForward: false,
    saveState: false,
    showFinishButtonAlways: false,
    transitionEffect: transitionEffect.fade,
    transitionEffectSpeed: 300,
    labels: {
        cancel: 'Cancelar',
        finish: 'Finalizar',
        next: 'Siguiente',
        previous: 'Anterior',
        loading: 'Cargando...'
    },
    onStepChanging: async () => true,
    onStepChanged: () => { },
    onFinishing: async () => true,
    onFinished: async () => { },
    onCanceled: () => { },
    onInit: () => { }
}

/* Props */
const props = defineProps({
    modelValue: { type: Number, default: 0 },
    options: { type: Object, default: () => ({}) }
})

function deepMerge(target, source) {
    // mutating merge into new object (not in-place)
    const out = Array.isArray(target) ? target.slice() : { ...target }

    if (!source) return out

    Object.keys(source).forEach(key => {
        const sVal = source[key]
        const tVal = out[key]

        if (sVal && typeof sVal === 'object' && !Array.isArray(sVal)) {
            out[key] = deepMerge((tVal && typeof tVal === 'object') ? tVal : {}, sVal)
        } else {
            out[key] = sVal
        }
    })

    return out
}
const options = computed(() => deepMerge(defaultOptions, props.options || {}))

const emit = defineEmits([
    'update:modelValue',
    'step-change',
    'finish',
    'cancel',
    'init'
])

/* Slots */
const slots = useSlots()
const stepList = shallowRef([])

/* State */
const state = reactive({
    currentIndex: props.modelValue || 0,
    stepCount: 0
})

/* Initialize steps from slots */
onMounted(() => {
    const raw = slots.default ? slots.default() : []
    stepList.value = raw
        .filter(vnode => vnode.type && (vnode.type === Step || (vnode.type.name && vnode.type.name === 'Step')))
        .map(vnode => {
            return {
                title: vnode.props?.title || 'Step',
                contentMode: vnode.props?.contentMode ?? contentMode.html,
                contentUrl: vnode.props?.contentUrl ?? '',
                component: { render: () => vnode },
                contentLoaded: false,
                content: vnode.props?.content || ''
            }
        })

    state.stepCount = stepList.value.length
    loadState()
    loadAsyncContent()
    options.value.onInit(state.currentIndex)
    emit('init', state.currentIndex)
})

/* Computeds */
const currentStep = computed(() => stepList.value[state.currentIndex])
const isLastStep = computed(() => state.currentIndex === state.stepCount - 1)
const transitionName = computed(() => {
    switch (options.value.transitionEffect) {
        case transitionEffect.fade: return 'fade'
        case transitionEffect.slide: return 'slide'
        case transitionEffect.slideLeft: return 'slide-left'
        default: return ''
    }
})

/* Methods */
function isStepEnabled(index) {
    return options.value.enableAllSteps || index <= state.currentIndex
}
async function goToStep(index) {
    if (index < 0 || index >= state.stepCount) return
    if (options.value.forceMoveForward && index < state.currentIndex) return

    const canChange = await Promise.resolve(
        options.value.onStepChanging(state.currentIndex, index)
    )
    if (canChange === false) return

    const oldIndex = state.currentIndex
    state.currentIndex = index
    emit('update:modelValue', state.currentIndex)
    emit('step-change', state.currentIndex)

    if (typeof options.value.onStepChanged === 'function') {
        options.value.onStepChanged(state.currentIndex, oldIndex)
    }

    saveState()
    await loadAsyncContent()
}
function nextStep() { goToStep(state.currentIndex + 1) }
function previousStep() { goToStep(state.currentIndex - 1) }
async function finishWizard() {
    const canFinish = await Promise.resolve(
        options.value.onFinishing(state.currentIndex)
    )
    if (canFinish !== false) {
        await Promise.resolve(options.value.onFinished(state.currentIndex))
        emit('finish')
    }
}
function cancelWizard() {
    if (typeof options.value.onCanceled === 'function') {
        options.value.onCanceled()
    }
    emit('cancel')
}

/* State & async */
function saveState() {
    if (options.value.saveState && typeof document !== 'undefined') {
        document.cookie = `wizard_state=${state.currentIndex}; path=/`
    }
}

function loadState() {
    if (options.value.saveState && typeof document !== 'undefined') {
        const match = document.cookie.match(/wizard_state=(\\d+)/)
        if (match) {
            const saved = parseInt(match[1])
            if (saved < stepList.value.length) state.currentIndex = saved
        }
    }
}
/* ===== ASYNC CONTENT ===== */
async function loadAsyncContent() {
    const step = stepList.value[state.currentIndex]
    if (step && step.contentMode === contentMode.async && !step.contentLoaded) {
        try {
            const res = await fetch(step.contentUrl)
            const html = await res.text()
            step.content = html
            step.contentLoaded = true
        } catch {
            step.content = '<p>Error al cargar el contenido.</p>'
        }
    }
}
</script>

<style scoped></style>