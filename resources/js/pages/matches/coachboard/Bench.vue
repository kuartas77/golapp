<template>
    <div>
        <label class="form-label">Banca / Suplentes</label>

        <div class="d-flex gap-2 flex-wrap">
            <div v-for="p in bench" :key="p.id" class="text-center" style="width:80px;">
                <img :src="p.img" alt="suple" width="60" height="60" class="rounded-circle" />
                <div class="small">{{ p.name }}</div>
            </div>
            <div v-if="bench.length === 0" class="text-muted small">Sin suplentes</div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({ bench: { type: Array, required: true } })
</script>

<style scoped>
img {
    object-fit: cover;
}
</style>