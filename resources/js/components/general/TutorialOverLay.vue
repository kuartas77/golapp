<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="tutorial-overlay position-fixed top-0 start-0 w-100"
      role="dialog"
      aria-modal="true"
      aria-label="Tutorial guiado"
    >
      <div class="tutorial-overlay__backdrop position-absolute top-0 start-0 w-100 h-100" />

      <div
        v-if="hasTarget"
        class="tutorial-overlay__highlight position-absolute"
        :style="highlightStyle"
      />

      <div
        ref="cardElement"
        class="tutorial-overlay__card card border-0 shadow-lg position-absolute"
        :style="cardStyle"
      >
        <div class="card-body p-4">
          <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
              <span class="badge rounded-pill bg-primary tutorial-overlay__badge">
                Guia
              </span>
              <h3 class="h5 mb-0 mt-2 tutorial-overlay__title">
                {{ currentStep?.title || 'Tutorial' }}
              </h3>
            </div>

            <button
              type="button"
              class="btn-close tutorial-overlay__close"
              aria-label="Cerrar tutorial"
              @click="$emit('close')"
            />
          </div>

          <p class="mb-0 text-muted tutorial-overlay__description">
            {{ currentStep?.text || '' }}
          </p>

          <div
            v-if="currentStep?.tips?.length"
            class="tutorial-overlay__tips rounded-3 p-3 mt-3"
          >
            <p class="small fw-semibold text-uppercase text-muted mb-2">
              Puntos clave
            </p>

            <ul class="mb-0 ps-3 small tutorial-overlay__tips-list">
              <li
                v-for="tip in currentStep.tips"
                :key="tip"
                class="mb-1"
              >
                {{ tip }}
              </li>
            </ul>
          </div>

          <div class="tutorial-overlay__footer mt-4 pt-3 border-top">
            <div class="small text-muted fw-semibold mb-3">
              Paso {{ currentIndex + 1 }} de {{ totalSteps }}
            </div>

            <div class="tutorial-overlay__actions d-flex flex-wrap justify-content-end gap-2">
                <AppButton
                    variant="secondary"
                    @click="$emit('skip')"
                >
                    Omitir
                </AppButton>
                    <AppButton
                    v-if="currentIndex > 0"
                    variant="secondary"
                    @click="$emit('prev')"
                >
                    Anterior
                </AppButton>

                <AppButton @click="$emit('next')">
                    {{ isLastStep ? 'Finalizar' : 'Siguiente' }}
                </AppButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import AppButton from './AppButton.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  currentIndex: { type: Number, default: 0 },
  totalSteps: { type: Number, default: 0 },
  currentStep: {
    type: Object,
    default: null,
  },
  targetRect: {
    type: Object,
    default: null,
  },
})

defineEmits(['close', 'prev', 'next', 'skip'])

const cardElement = ref(null)
const cardMetrics = ref({
  width: 420,
  height: 320,
})

function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max)
}

function getOverlapArea(candidate, target) {
  const overlapWidth = Math.max(
    0,
    Math.min(candidate.left + candidate.width, target.right) - Math.max(candidate.left, target.left),
  )
  const overlapHeight = Math.max(
    0,
    Math.min(candidate.top + candidate.height, target.bottom) - Math.max(candidate.top, target.top),
  )

  return overlapWidth * overlapHeight
}

async function updateCardMetrics() {
  await nextTick()

  if (!cardElement.value) return

  const { offsetWidth, offsetHeight } = cardElement.value
  if (!offsetWidth || !offsetHeight) return

  cardMetrics.value = {
    width: offsetWidth,
    height: offsetHeight,
  }
}

const hasTarget = computed(() => {
  return !!props.targetRect?.width && !!props.targetRect?.height
})

const isLastStep = computed(() => {
  return props.currentIndex >= props.totalSteps - 1
})

const highlightStyle = computed(() => {
  if (!hasTarget.value) return {}

  return {
    top: `${props.targetRect.top}px`,
    left: `${props.targetRect.left}px`,
    width: `${props.targetRect.width}px`,
    height: `${props.targetRect.height}px`,
  }
})

const cardStyle = computed(() => {
  const fallbackTop = 24
  const fallbackLeft = 16
  const viewportWidth = typeof window !== 'undefined' ? window.innerWidth : 1280
  const viewportHeight = typeof window !== 'undefined' ? window.innerHeight : 720
  const margin = 16
  const gap = 16

  if (!hasTarget.value) {
    return {
      top: `${fallbackTop}px`,
      left: '50%',
      width: `min(calc(100vw - ${fallbackLeft * 2}px), 420px)`,
      transform: 'translateX(-50%)',
    }
  }

  const measuredWidth = cardMetrics.value.width || 420
  const measuredHeight = cardMetrics.value.height || 320
  const cardWidth = Math.min(viewportWidth - margin * 2, measuredWidth, 420)
  const cardHeight = Math.min(viewportHeight - margin * 2, measuredHeight)
  const rect = props.targetRect
  const target = {
    top: rect.top,
    right: rect.right ?? rect.left + rect.width,
    bottom: rect.bottom ?? rect.top + rect.height,
    left: rect.left,
  }

  const candidates = [
    {
      placement: 'bottom',
      top: target.bottom + gap,
      left: clamp(rect.left, margin, viewportWidth - cardWidth - margin),
    },
    {
      placement: 'top',
      top: target.top - cardHeight - gap,
      left: clamp(rect.left, margin, viewportWidth - cardWidth - margin),
    },
    {
      placement: 'right',
      top: clamp(rect.top + rect.height / 2 - cardHeight / 2, margin, viewportHeight - cardHeight - margin),
      left: target.right + gap,
    },
    {
      placement: 'left',
      top: clamp(rect.top + rect.height / 2 - cardHeight / 2, margin, viewportHeight - cardHeight - margin),
      left: target.left - cardWidth - gap,
    },
  ].map((candidate) => {
    const normalized = {
      ...candidate,
      top: clamp(candidate.top, margin, viewportHeight - cardHeight - margin),
      left: clamp(candidate.left, margin, viewportWidth - cardWidth - margin),
      width: cardWidth,
      height: cardHeight,
    }

    return {
      ...normalized,
      overlapArea: getOverlapArea(normalized, target),
    }
  })

  const nonOverlappingCandidate = candidates.find((candidate) => candidate.overlapArea === 0)
  const bestCandidate = nonOverlappingCandidate
    || [...candidates].sort((a, b) => a.overlapArea - b.overlapArea)[0]

  return {
    top: `${bestCandidate.top}px`,
    left: `${bestCandidate.left}px`,
    width: `${cardWidth}px`,
    transform: 'none',
  }
})

function handleViewportChange() {
  if (!props.open) return
  updateCardMetrics()
}

watch(
  () => [props.open, props.currentIndex, props.currentStep?.title, props.currentStep?.text, props.currentStep?.tips?.length ?? 0],
  ([open]) => {
    if (!open) return
    updateCardMetrics()
  },
  { immediate: true },
)

watch(
  () => props.targetRect,
  () => {
    if (!props.open) return
    updateCardMetrics()
  },
)

onMounted(() => {
  window.addEventListener('resize', handleViewportChange)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleViewportChange)
})
</script>

<style>
.tutorial-overlay {
  z-index: 1080;
  height: 100vh;
}

.tutorial-overlay__backdrop {
  background: rgba(11, 18, 34, 0.68);
}

.tutorial-overlay__highlight {
  border-radius: 1rem;
  box-shadow: 0 0 0 9999px rgba(11, 18, 34, 0.48);
  outline: 3px solid rgba(13, 110, 253, 0.85);
  outline-offset: 0;
  transition: top 0.2s ease, left 0.2s ease, width 0.2s ease, height 0.2s ease;
}

.tutorial-overlay__card {
  width: min(calc(100vw - 2rem), 420px);
  max-height: calc(100vh - 2rem);
  overflow-y: auto;
  background-color: #ffffff;
  color: #212529;
  transition: top 0.2s ease, left 0.2s ease;
}

.tutorial-overlay__badge {
  letter-spacing: 0.08em;
}

.tutorial-overlay__title {
  color: inherit;
}

.tutorial-overlay__description {
  line-height: 1.65;
}

.tutorial-overlay__tips {
  background: rgba(13, 110, 253, 0.08);
  border: 1px solid rgba(13, 110, 253, 0.14);
}

.tutorial-overlay__tips-list {
  color: #495057;
}

.tutorial-overlay__footer {
  border-color: rgba(108, 117, 125, 0.2) !important;
}

.tutorial-overlay__actions {
  min-height: 38px;
}

.dark .tutorial-overlay__card,
body.dark .tutorial-overlay__card {
  background-color: #1b2e4b;
  color: #e0e6ed;
}

.dark .tutorial-overlay__description,
.dark .tutorial-overlay__tips-list,
.dark .tutorial-overlay .text-muted,
body.dark .tutorial-overlay__description,
body.dark .tutorial-overlay__tips-list,
body.dark .tutorial-overlay .text-muted {
  color: #bfc9d4 !important;
}

.dark .tutorial-overlay__tips,
body.dark .tutorial-overlay__tips {
  background: rgba(13, 110, 253, 0.14);
  border-color: rgba(13, 110, 253, 0.28);
}

.dark .tutorial-overlay__footer,
body.dark .tutorial-overlay__footer {
  border-color: rgba(191, 201, 212, 0.16) !important;
}

.dark .tutorial-overlay__close,
body.dark .tutorial-overlay__close {
  filter: invert(1) grayscale(100%) brightness(200%);
}

@media (max-width: 575.98px) {
  .tutorial-overlay__card {
    width: calc(100vw - 1.5rem) !important;
  }
}
</style>
