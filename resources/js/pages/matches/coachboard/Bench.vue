<template>
    <section class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Vista rápida</div>
                    <h3 class="h5 mb-0">Banca y relevos</h3>
                </div>
                <span class="badge rounded-pill text-bg-warning px-3 py-2">{{ bench.length }}</span>
            </div>

            <div class="row g-2">
                <div v-for="p in bench" :key="p.id" class="col-lg-4 col-md-4 col-sm-6 col-6">
                    <div class="bench-player border rounded-3 p-2 text-center h-100">
                        <img :src="p.img" :alt="p.name" width="52" height="52" class="bench-avatar mb-2" />
                        <div class="small fw-semibold text-break">{{ p.name }}</div>
                    </div>
                </div>
                <div v-if="bench.length === 0" class="col-12">
                    <div class="bench-empty border rounded-3 p-3 text-center text-muted small">Sin suplentes disponibles</div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({ bench: { type: Array, required: true } })
</script>

<style scoped>
.bench-player {
    transition: box-shadow 160ms ease, transform 160ms ease;
}

.bench-player:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.bench-avatar {
    object-fit: cover;
    border-radius: 50%;
}

:global(.dark) .bench-player {
    background: rgba(255, 255, 255, 0.04);
}

:global(.dark) .bench-empty {
    background: rgba(255, 255, 255, 0.04);
}
</style>
