<template>
    <section class="position-relative">
        <Skeleton
            name="guardian-dashboard"
            :loading="loading"
            animate="shimmer"
            :transition="true"
            class="guardian-dashboard__skeleton"
        >
            <GuardianDashboardContent
                :guardian-name="guardianName"
                :grouped-players="groupedPlayers"
                :loading="loading"
                :error-message="errorMessage"
            />

            <template #fixture>
                <GuardianDashboardContent
                    guardian-name="Acudiente"
                    :grouped-players="fixtureGroupedPlayers"
                    :loading="false"
                />
            </template>

            <template #fallback>
                <GuardianDashboardContent
                    guardian-name="Acudiente"
                    :grouped-players="fixtureGroupedPlayers"
                    :loading="false"
                />
                <Loader :is-loading="true" loading-text="Cargando jugadores..." />
            </template>
        </Skeleton>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import Skeleton from 'boneyard-js/vue';
import Loader from '@/components/general/Loader.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';
import GuardianDashboardContent from '@/pages/portal/guardians/components/GuardianDashboardContent.vue';
import { useGuardianAuth } from '@/store/guardian-auth';

const guardianStore = useGuardianAuth();
const loading = ref(true);
const errorMessage = ref('');
const players = ref([]);
const LOADING_PREVIEW_DELAY_MS = import.meta.env.MODE === 'development' ? 1600 : 0;

usePageTitle('Mis jugadores');

const guardianName = computed(() => guardianStore.user?.names ?? 'Acudiente');

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

const fixtureGroupedPlayers = [
    {
        key: 'fixture-school',
        schoolName: 'Escuela deportiva',
        players: [
            {
                id: 'fixture-player-1',
                full_names: 'Juan David Pérez',
                unique_code: 'GL-1024',
                photo_url: '/img/user.png',
                current_inscription: {
                    training_group: { name: 'Sub 13 - Mañana' },
                },
            },
            {
                id: 'fixture-player-2',
                full_names: 'María Camila Torres',
                unique_code: 'GL-2048',
                photo_url: '/img/user.png',
                current_inscription: {
                    training_group: { name: 'Sub 15 - Tarde' },
                },
            },
            {
                id: 'fixture-player-3',
                full_names: 'Santiago Gómez',
                unique_code: 'GL-3072',
                photo_url: '/img/user.png',
                current_inscription: {
                    training_group: { name: 'Iniciación' },
                },
            },
        ],
    },
];

const waitForLoadingPreview = () => (
    LOADING_PREVIEW_DELAY_MS > 0
        ? new Promise((resolve) => setTimeout(resolve, LOADING_PREVIEW_DELAY_MS))
        : Promise.resolve()
);

const fetchPlayers = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const previewDelay = waitForLoadingPreview();

        if (!guardianStore.user) {
            await guardianStore.getUser();
        }

        const response = await api.get('/api/v2/portal/acudientes/players');
        players.value = response.data?.data ?? [];

        await previewDelay;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar los jugadores.';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPlayers);
</script>

<style scoped>
.guardian-dashboard__skeleton {
    display: block;
}
</style>
