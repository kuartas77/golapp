<template>
    <section class="container portal-school-index">
        <div class="portal-school-index__header mb-4">
            <p class="text-primary text-uppercase fw-semibold small mb-2">Portal de Escuelas</p>
            <h1 class="display-6 portal-school-index__title mb-3">Encuentra la escuela y completa la inscripción en línea.</h1>
            <p class="text-muted portal-school-index__description mb-0">
                Selecciona la escuela para revisar el proceso de inscripción, documentos requeridos y acceso a la plataforma de acudientes.
            </p>
        </div>

        <div class="portal-school-index__content position-relative">
            <Loader :is-loading="loading" loading-text="Cargando escuelas..." />

            <div v-if="errorMessage" class="alert alert-danger mb-0" role="alert">
                {{ errorMessage }}
            </div>

            <div v-else-if="!loading && schools.length === 0" class="alert alert-info mb-0" role="alert">
                No hay escuelas disponibles en este momento.
            </div>

            <div v-else class="row g-4">
                <div
                    v-for="school in schools"
                    :key="school.id"
                    class="col-12 col-md-6 col-xl-4"
                >
                    <article class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <img
                                    :src="school.logo_file"
                                    :alt="school.name"
                                    class="portal-school-card__logo"
                                >
                            </div>

                            <h2 class="h4 mb-3">{{ school.name }}</h2>

                            <div class="portal-school-card__meta text-muted">
                                <p v-if="school.address" class="mb-2">
                                    <strong>Dirección:</strong> {{ school.address }}
                                </p>
                                <p v-if="school.phone" class="mb-2">
                                    <strong>Teléfono:</strong> {{ school.phone }}
                                </p>
                                <p v-if="school.email_info" class="mb-0">
                                    <strong>Correo:</strong> {{ school.email_info }}
                                </p>
                            </div>

                            <router-link
                                :to="{ name: 'portal-school-show', params: { slug: school.slug } }"
                                class="btn btn-primary mt-4 align-self-start"
                            >
                                Ver escuela
                            </router-link>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Loader from '@/components/general/Loader.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';

usePageTitle('Escuelas');

const loading = ref(true);
const errorMessage = ref('');
const schools = ref([]);

const fetchSchools = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await api.get('api/v2/portal/escuelas/data');
        schools.value = response.data?.data?.schools ?? [];
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No pudimos cargar el listado de escuelas.';
        schools.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchSchools);
</script>

<style scoped>
.portal-school-index {
    padding-top: 0.5rem;
}

.portal-school-index__title {
    max-width: 760px;
}

.portal-school-index__description {
    max-width: 720px;
}

.portal-school-index__content {
    min-height: 240px;
}

.portal-school-card__logo {
    width: 112px;
    height: 112px;
    object-fit: contain;
}

.portal-school-card__meta {
    line-height: 1.55;
}
</style>
