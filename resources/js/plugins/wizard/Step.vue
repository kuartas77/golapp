<template>
  <div class="wizard-step">
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, required: true },
  contentMode: { type: Number, default: 0 }, // 0=html, 1=iframe, 2=async
  contentUrl: { type: String, default: '' }
})
</script>

<style scoped>
.wizard-step { padding: 0.5rem; }
</style>
