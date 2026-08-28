<template>
    <panel>
        <template #body>
            <Loader :is-loading="loading" loading-text="Cargando deportista..." />
            <ContentState v-if="error" type="error" title="No fue posible cargar el deportista" :message="error" />

            <div v-if="player" class="row g-3">
                <div class="col-12 d-flex align-items-center gap-3">
                    <img :src="player.photo_url || '/img/user.webp'" class="player-photo" alt="Foto del deportista">
                    <div>
                        <h4 class="mb-1">{{ player.full_names }}</h4>
                        <span class="badge bg-primary">{{ player.unique_code }}</span>
                        <span class="badge bg-secondary ms-1">Sólo lectura</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100"><div class="card-body">
                        <h6>Información del deportista</h6>
                        <dl class="detail-list">
                            <template v-for="field in playerFields" :key="field.label">
                                <dt>{{ field.label }}</dt><dd>{{ field.value || '—' }}</dd>
                            </template>
                        </dl>
                    </div></div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100"><div class="card-body">
                        <h6>Acudientes y contactos</h6>
                        <div v-for="person in player.people || []" :key="person.id" class="border rounded p-2 mb-2">
                            <strong>{{ person.full_names || `${person.names || ''} ${person.last_names || ''}`.trim() }}</strong>
                            <div class="small text-muted">{{ person.relationship_name || person.relationship || 'Contacto' }}</div>
                            <div class="small">{{ person.mobile || person.phones || 'Sin teléfono' }} · {{ person.email || 'Sin correo' }}</div>
                        </div>
                        <p v-if="!player.people?.length" class="text-muted mb-0">No hay contactos registrados.</p>
                    </div></div>
                </div>

                <div class="col-12">
                    <div class="card"><div class="card-body">
                        <h6>Inscripciones por año</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Año</th><th>Estado</th><th>Grupo</th><th>Categoría</th><th></th></tr></thead>
                                <tbody>
                                    <tr v-for="inscription in player.inscriptions || []" :key="inscription.id">
                                        <td>{{ inscription.year }}</td>
                                        <td>{{ inscription.deleted_at ? 'Retirada' : (inscription.pre_inscription ? 'Preinscripción' : 'Activa') }}</td>
                                        <td>{{ inscription.training_group?.name || '—' }}</td>
                                        <td>{{ inscription.category || '—' }}</td>
                                        <td class="text-end"><router-link class="btn btn-outline-primary btn-sm" :to="{ name: 'inscriptions.summary', params: { id: inscription.id } }">Ver resumen</router-link></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div></div>
                </div>
            </div>
        </template>
    </panel>
    <breadcrumb parent="Deportistas" current="Detalle" />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/utils/axios'
import Loader from '@/components/general/Loader.vue'
import ContentState from '@/components/general/ContentState.vue'
import { usePageTitle } from '@/composables/use-meta'

usePageTitle('Detalle del deportista')
const route = useRoute()
const player = ref(null)
const loading = ref(false)
const error = ref('')
const playerFields = computed(() => [
    { label: 'Documento', value: player.value?.identification_document },
    { label: 'Fecha de nacimiento', value: player.value?.date_birth },
    { label: 'Género', value: player.value?.gender },
    { label: 'EPS', value: player.value?.eps },
    { label: 'RH', value: player.value?.rh },
    { label: 'Teléfono', value: player.value?.mobile || player.value?.phones },
    { label: 'Correo', value: player.value?.email },
    { label: 'Dirección', value: player.value?.address },
])

onMounted(async () => {
    loading.value = true
    try {
        const { data } = await api.get(`/api/v2/players/${route.params.unique_code}`)
        player.value = data
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Intenta nuevamente.'
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.player-photo { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; }
.detail-list { display: grid; grid-template-columns: minmax(130px, auto) 1fr; gap: .5rem 1rem; margin: 0; }
.detail-list dt, .detail-list dd { margin: 0; }
:global(body.dark .card), :global(body.dark .border) { border-color: #3b3f4a !important; }
</style>
