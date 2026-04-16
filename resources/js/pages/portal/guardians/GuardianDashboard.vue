<template>
    <section class="position-relative">
        <Loader :is-loading="loading" loading-text="Cargando jugadores..." />

        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm guardian-dashboard__hero">
                    <div class="card-body p-4 p-lg-5">
                        <p class="text-uppercase fw-semibold small mb-2">Portal de acudientes</p>
                        <h1 class="h2 mb-3">Mis jugadores vigentes</h1>
                        <p class="text-muted mb-0">
                            {{ guardianStore.user?.names ?? 'Acudiente' }}, aquí puedes consultar la información de los deportistas con inscripción activa en el año actual.
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="errorMessage" class="col-12">
                <div class="alert alert-danger mb-0" role="alert">
                    {{ errorMessage }}
                </div>
            </div>

            <div v-else-if="groupedPlayers.length === 0 && !loading" class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 mb-2">No hay jugadores disponibles</h2>
                        <p class="text-muted mb-0">
                            Cuando exista una inscripción vigente para alguno de tus jugadores, aparecerá aquí automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            <div v-for="group in groupedPlayers" :key="group.key" class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="h4 mb-1">{{ group.schoolName }}</h2>
                        <p class="text-muted mb-0">{{ group.players.length }} jugador(es) disponible(s)</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div v-for="player in group.players" :key="player.id" class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <img :src="player.photo_url" :alt="player.full_names" class="guardian-dashboard__photo">
                                    <div>
                                        <h3 class="h5 mb-1">{{ player.full_names }}</h3>
                                        <p class="text-muted mb-1">Código {{ player.unique_code }}</p>
                                        <p class="mb-0 small text-muted">
                                            {{ player.current_inscription?.training_group?.name || 'Sin grupo asignado' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <router-link
                                        :to="{ name: 'guardian-player-detail', params: { id: player.id } }"
                                        class="btn btn-primary w-100"
                                    >
                                        Ver detalle
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import Loader from '@/components/general/Loader.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';
import { useGuardianAuth } from '@/store/guardian-auth';

const guardianStore = useGuardianAuth();
const loading = ref(true);
const errorMessage = ref('');
const players = ref([]);

usePageTitle('Mis jugadores');

const groupedPlayers = computed(() => {
    const grouped = new Map();

    players.value.forEach((player) => {
        const schoolName = player.school?.name || 'Escuela';
        const schoolId = player.school?.id || schoolName;

        if (!grouped.has(schoolId)) {
            grouped.set(schoolId, {
                key: schoolId,
                schoolName,
                players: [],
            });
        }

        grouped.get(schoolId).players.push(player);
    });

    return Array.from(grouped.values());
});

const fetchPlayers = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        if (!guardianStore.user) {
            await guardianStore.getUser();
        }

        const response = await api.get('/api/v2/portal/acudientes/players');
        players.value = response.data?.data ?? [];
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar los jugadores.';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPlayers);
</script>

<style scoped>
.guardian-dashboard__hero {
    background:
        linear-gradient(135deg, rgba(15, 28, 70, 0.95), rgba(49, 82, 158, 0.88)),
        #0f1c46;
    color: #fff;
}

.guardian-dashboard__hero .text-muted,
.guardian-dashboard__hero .small {
    color: rgba(255, 255, 255, 0.8) !important;
}

.guardian-dashboard__photo {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    object-fit: cover;
    background: #f3f5fa;
    border: 1px solid rgba(15, 28, 70, 0.08);
}
</style>
