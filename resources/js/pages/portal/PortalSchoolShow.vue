<template>
    <section class="container portal-school-show position-relative">
        <Loader :is-loading="loading" loading-text="Cargando escuela..." />

        <div v-if="errorMessage" class="card shadow-sm">
            <div class="card-body">
                <h1 class="h3 mb-3">No fue posible cargar la escuela</h1>
                <p class="text-muted mb-4">{{ errorMessage }}</p>
                <!-- <router-link :to="{ name: 'portal-school-index' }" class="btn btn-outline-primary">
                    Volver al listado
                </router-link> -->
            </div>
        </div>

        <div v-else-if="school" class="row g-4">
            <div class="col-12 col-lg-4">
                <aside class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <img :src="school.logo_file" :alt="school.name" class="portal-school-show__logo">
                        <h1 class="h3 mb-4">{{ school.name }}</h1>

                        <div class="portal-school-show__school-meta text-start text-muted">
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
                    </div>
                </aside>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-transparent pb-0">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <button
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: activeTab === 'inscriptions' }"
                                    @click="activeTab = 'inscriptions'"
                                >
                                    Inscripciones
                                </button>
                            </li>
                            <li v-if="school.tutor_platform" class="nav-item">
                                <button
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: activeTab === 'platform' }"
                                    @click="activeTab = 'platform'"
                                >
                                    Plataforma Acudientes
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div v-show="activeTab === 'inscriptions'">
                            <div class="portal-school-show__intro">
                                <p class="text-primary text-uppercase fw-semibold small mb-2">Formulario de inscripción {{ pageData.year }}</p>
                                <h2 class="h3 mb-3">{{ school.name }}</h2>
                            </div>

                            <div v-if="school.inscriptions_enabled">
                                <p class="text-muted mb-4" v-if="school.send_documents">
                                    Antes de comenzar, deja listos los documentos y firmas que se solicitan durante el proceso.
                                </p>
                                <p class="text-muted mb-4" v-else>
                                    Completa el formulario y la escuela gestionará contigo los pasos adicionales del proceso.
                                </p>

                                <ul class="list-group list-group-flush mb-4">
                                    <li
                                        v-for="item in registrationChecklist"
                                        :key="item"
                                        class="list-group-item px-0 bg-transparent"
                                    >
                                        {{ item }}
                                    </li>
                                </ul>

                                <p class="text-muted mb-4">
                                    Una vez envíes la información, la escuela revisará la inscripción y enviará la confirmación al correo registrado.
                                </p>

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal_inscription"
                                >
                                    Realizar Inscripción
                                </button>
                            </div>

                            <div v-else class="alert alert-warning mb-0" role="alert">
                                Las inscripciones se encuentran deshabilitadas. Comunícate con {{ school.name }} para más información.
                            </div>
                        </div>

                        <div v-if="school.tutor_platform" v-show="activeTab === 'platform'">
                            <p class="text-primary text-uppercase fw-semibold small mb-2">Plataforma de acudientes</p>
                            <h2 class="h3 mb-3">Seguimiento del deportista</h2>

                            <p class="text-muted mb-4">
                                Desde la plataforma puedes consultar el proceso del deportista y mantener sus datos al día.
                            </p>

                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item px-0 bg-transparent">Ver y actualizar la información del deportista.</li>
                                <li class="list-group-item px-0 bg-transparent">Consultar pagos y estado de mensualidades.</li>
                                <li class="list-group-item px-0 bg-transparent">Revisar asistencias, estadísticas e historial por año.</li>
                                <li class="list-group-item px-0 bg-transparent">Activar el acceso con el correo del acudiente y una contraseña personal.</li>
                            </ul>

                            <a :href="pageData.links.guardianLogin" class="btn btn-outline-primary">
                                Ir a plataforma de acudientes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <PortalSchoolInscriptionModal
            v-if="school && school.inscriptions_enabled"
            :school="pageData.school"
            :year="pageData.year"
            :file-size-mb="pageData.fileSizeMb"
            :storage-key="pageData.storageKey"
            :endpoints="pageData.endpoints"
            :assets="pageData.assets"
            :contracts="pageData.contracts"
            :options="pageData.options"
            :recaptcha="pageData.recaptcha"
        />
    </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import Loader from '@/components/general/Loader.vue';
import PortalSchoolInscriptionModal from '@/pages/portal/PortalSchoolInscriptionModal.vue';
import api from '@/utils/axios';
import { usePageTitle } from '@/composables/use-meta';

const route = useRoute();

const loading = ref(true);
const errorMessage = ref('');
const activeTab = ref('inscriptions');
const pageData = ref(null);

const school = computed(() => pageData.value?.school ?? null);

const registrationChecklist = computed(() => {
    if (!school.value) {
        return [];
    }

    const items = [];

    if (school.value.create_contract) {
        items.push(
            school.value.sign_player
                ? 'Se solicitará la firma del acudiente y del deportista.'
                : 'Se solicitará la firma del acudiente.'
        );
        items.push('Debes aceptar los términos y condiciones de inscripción.');
    }

    items.push('Foto tipo documento del deportista (opcional).');

    if (school.value.send_documents) {
        items.push('Documento de identidad del deportista.');
        items.push('Certificado EPS.');
        items.push('Documento de identidad del acudiente.');
        items.push('Recibo de pago de la inscripción si ya fue realizado.');
    }

    return items;
});

usePageTitle(computed(() => school.value?.name ?? 'Escuelas'));

const fetchSchool = async (slug) => {
    if (!slug) {
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    activeTab.value = 'inscriptions';
    pageData.value = null;

    try {
        const response = await api.get(`api/v2/portal/escuelas/${encodeURIComponent(slug)}/data`);
        pageData.value = response.data?.data ?? null;

        if (!pageData.value?.school) {
            throw new Error('La escuela no está disponible.');
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'La escuela no existe o no está disponible en este momento.';
    } finally {
        loading.value = false;
    }
};

watch(
    () => route.params.slug,
    (slug) => {
        fetchSchool(String(slug ?? ''));
    },
    { immediate: true }
);
</script>

<style scoped>
.portal-school-show {
    min-height: 420px;
}

.portal-school-show__logo {
    width: 128px;
    height: 128px;
    object-fit: contain;
    margin-bottom: 1rem;
}

.portal-school-show__school-meta {
    line-height: 1.55;
}

.portal-school-show__intro {
    margin-bottom: 1.5rem;
}

.portal-school-show :deep(.card-header-tabs .nav-link) {
    font-weight: 600;
}
</style>
