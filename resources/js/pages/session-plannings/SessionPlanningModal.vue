<template>
    <div ref="modalRef" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable"><form class="modal-content" @submit.prevent="save">
            <div class="modal-header"><h5 class="modal-title">{{ sessionId ? `Actualizar planificación #${sessionId}` : 'Crear planificación de sesión' }}</h5><button type="button" class="btn-close" @click="close"></button></div>
            <div class="modal-body position-relative">
                <Loader :is-loading="loading" :loading-text="saving ? 'Guardando...' : 'Cargando...'" />
                <div v-if="error" class="alert alert-danger">{{ error }}</div>
                <div v-if="periodLocked" class="alert alert-warning">Este periodo ya está cerrado para instructores. Solicita a la escuela una corrección administrativa.</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button v-for="(label, index) in stepLabels" :key="label" type="button" class="btn btn-sm" :class="step === index ? 'btn-primary' : 'btn-outline-primary'" @click="goTo(index)">{{ label }}</button>
                </div>

                <section v-show="step === 0">
                    <div class="row g-3">
                        <div class="col-md-5"><label class="form-label">Grupo de entrenamiento *</label><CustomSelect2 v-model="form.training_group_id" :options="groupOptions" :disabled="identityLocked" @update:model-value="groupChanged" /></div>
                        <div class="col-md-3"><label class="form-label">Mes *</label><CustomSelect2 v-model="form.month" :options="monthOptions" :disabled="identityLocked" @update:model-value="monthChanged" /></div>
                        <div class="col-md-2"><label class="form-label">Periodo *</label><input v-model.trim="form.period" class="form-control" maxlength="100"></div>
                        <div class="col-md-2"><label class="form-label">Sesión *</label><input v-model.trim="form.session" class="form-control" maxlength="100"></div>
                        <div v-if="classDays.length" class="col-12"><label class="form-label d-block">Día de entrenamiento *</label><button v-for="day in classDays" :key="day.id" type="button" class="btn btn-sm m-1" :class="form.date === fullDate(day) ? 'btn-primary' : 'btn-outline-info'" :disabled="identityLocked" @click="selectDay(day)">#{{ day.index }} | {{ day.day }} {{ day.date }}</button></div>
                        <div class="col-md-6"><label class="form-label">Fecha *</label><input v-model="form.date" type="date" class="form-control" readonly></div>
                        <div class="col-md-6"><label class="form-label">Lugar</label><input v-model.trim="form.training_ground" class="form-control" maxlength="100"></div>
                        <div class="col-md-6"><label class="form-label">Materiales utilizados</label><textarea v-model.trim="form.material" class="form-control" rows="3"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Calentamiento</label><textarea v-model.trim="form.warm_up" class="form-control" rows="3"></textarea></div>
                        <div class="col-md-4"><label class="form-label">Cantidad de fases *</label><select :value="form.phases.length" class="form-select" @change="changePhaseCount(Number($event.target.value), $event)"><option v-for="n in 4" :key="n" :value="n">{{ n }}</option></select></div>
                    </div>
                </section>

                <section v-for="(phase, index) in form.phases" :key="index" v-show="step === index + 1">
                    <h6>Fase {{ index + 1 }}</h6><div class="row g-3">
                        <div class="col-md-8"><label class="form-label">Nombre *</label><input v-model.trim="phase.name" class="form-control" maxlength="100"></div>
                        <div class="col-md-4"><label class="form-label">Tiempo</label><input v-model.trim="phase.time" class="form-control" maxlength="50"></div>
                        <div class="col-12"><div class="session-phase-field"><SoccerFieldDiagramEditor v-model="phase.diagram" /></div></div>
                        <div class="col-md-6"><label class="form-label">Dosificación</label><textarea v-model.trim="phase.dosage" class="form-control" rows="4"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Descripción</label><textarea v-model.trim="phase.description" class="form-control" rows="4"></textarea></div>
                    </div>
                </section>

                <section v-show="step === form.phases.length + 1"><div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Vuelta a la calma</label><input v-model.trim="form.back_to_calm" class="form-control" maxlength="10"></div>
                    <div class="col-md-6"><label class="form-label">N° de jugadores presentes</label><input v-model="form.players" class="form-control" readonly></div>
                    <div class="col-12">
                        <label for="absence_inscription_ids" class="form-label d-block text-center">Deportistas</label>
                        <div class="row g-3 mb-1 text-center fw-semibold" aria-hidden="true"><div class="col-6 text-success">Izquierda: Asistieron</div><div class="col-6 text-danger">Derecha: Faltaron</div></div>
                        <CustomMultiSelect id="absence_inscription_ids" v-model="form.absence_inscription_ids" :buttons="true" :options="absenceOptions" @update:model-value="onAbsencesChanged" />
                        <small class="form-text text-muted d-block mt-1">Mueve a la lista derecha únicamente quienes faltaron. Los deportistas que permanezcan en la izquierda se marcarán automáticamente como asistencia.</small>
                    </div>
                    <div v-if="protectedPlayers.length" class="col-12"><div class="alert alert-info py-2">Se conservarán sin cambios: {{ protectedPlayers.map(player => `${player.label} (${player.status_label})`).join(', ') }}.</div></div>
                    <div class="col-md-6"><label class="form-label">Incidencias</label><textarea v-model.trim="form.incidents" class="form-control" rows="4"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Retroalimentación</label><textarea v-model.trim="form.feedback" class="form-control" rows="4"></textarea></div>
                </div></section>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" @click="close">Cerrar</button><button v-if="step > 0" type="button" class="btn btn-outline-primary" @click="step--">Anterior</button><button v-if="step < stepLabels.length - 1" type="button" class="btn btn-primary" @click="next">Siguiente</button><button v-else type="submit" class="btn btn-success" :disabled="saving || periodLocked">Guardar</button></div>
        </form></div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import Loader from '@/components/general/Loader.vue'
import SoccerFieldDiagramEditor from '@/pages/methodology/SoccerFieldDiagramEditor.vue'
import api from '@/utils/axios'
import { useSetting } from '@/store/settings-store'
const props = defineProps({ show: Boolean, sessionId: { type: [Number, String], default: null } })
const emit = defineEmits(['updated', 'cancel'])
const modalRef = ref(), modal = ref(), settings = useSetting(), step = ref(0), loading = ref(false), saving = ref(false), error = ref(null)
const classDays = ref([]), attendance = ref(null), identityLocked = ref(false), periodLocked = ref(false)
const blankPhase = () => ({ name: '', time: '', dosage: '', description: '', diagram: [] })
const blank = () => ({ training_group_id: null, month: new Date().getMonth() + 1, period: '', session: '', date: '', hour: '02:00 PM', training_ground: '', material: '', warm_up: '', back_to_calm: '', players: '', absence_inscription_ids: [], incidents: '', feedback: '', phases: [blankPhase()] })
const form = reactive(blank())
const monthOptions = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'].map((label, index) => ({ value: index + 1, label }))
const groupOptions = computed(() => settings.groups.filter(group => group.name !== 'Provisional').map(group => ({ value: String(group.id), label: group.full_schedule_group ?? group.full_group ?? group.name })))
const stepLabels = computed(() => ['Información general', ...form.phases.map((_, i) => `Fase ${i + 1}`), 'Cierre'])
const absenceOptions = computed(() => attendance.value?.players ?? [])
const protectedPlayers = computed(() => attendance.value?.protected_players ?? [])
function reset(values = blank()) { Object.assign(form, blank(), values); step.value = 0; error.value = null; classDays.value = []; attendance.value = null; identityLocked.value = false; periodLocked.value = false }
function fullDate(day) { return `${day.year}-${String(day.month).padStart(2, '0')}-${String(day.date).padStart(2, '0')}` }
async function loadDays() { classDays.value = []; if (!form.training_group_id || !form.month) return; const { data } = await api.get('/api/v2/training_group/classdays', { params: { training_group_id: form.training_group_id, month: form.month } }); classDays.value = data ?? [] }
async function loadAttendance(preferred = null) { if (!form.date) return; const { data } = await api.get('/api/v2/session-plannings/attendance-context', { params: { training_group_id: form.training_group_id, date: form.date } }); attendance.value = data.data; const ids = (preferred ?? attendance.value.current_absence_ids ?? []).map(Number); form.absence_inscription_ids = absenceOptions.value.filter(option => ids.includes(Number(option.value))); updatePlayerCount() }
async function groupChanged(value) { form.training_group_id = value; if (!identityLocked.value) { form.date = ''; await loadDays() } }
async function monthChanged(value) { form.month = Number(value); if (!identityLocked.value) { form.date = ''; await loadDays() } }
async function selectDay(day) { form.date = fullDate(day); await loadAttendance() }
function updatePlayerCount() { form.players = Math.max(absenceOptions.value.length - form.absence_inscription_ids.length, 0) }
function onAbsencesChanged(value) { form.absence_inscription_ids = Array.isArray(value) ? value : []; updatePlayerCount() }
async function changePhaseCount(count, event = null) { const current = form.phases.length; if (count < current) { const result = await window.Swal.fire({ title: '¿Reducir fases?', text: 'Se eliminarán las canchas y textos de las fases sobrantes al guardar.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, reducir' }); if (!result.isConfirmed) { if (event?.target) event.target.value = current; return } } while (form.phases.length < count) form.phases.push(blankPhase()); form.phases.splice(count); if (step.value >= stepLabels.value.length) step.value = stepLabels.value.length - 1 }
function validate(index) { error.value = null; if (index === 0 && (!form.training_group_id || !form.period || !form.session || !form.date)) error.value = 'Completa grupo, periodo, sesión y día de entrenamiento.'; else if (index > 0 && index <= form.phases.length && !form.phases[index - 1].name) error.value = `El nombre de la fase ${index} es obligatorio.`; return !error.value }
function next() { if (validate(step.value)) step.value++ }
function goTo(index) { if (index <= step.value) { step.value = index; return } for (let current = step.value; current < index; current++) { if (!validate(current)) { step.value = current; return } } step.value = index }
async function prepare() { reset(); loading.value = true; try { if (!settings.groups.length) await settings.getSettings(); await nextTick(); if (props.sessionId) { const { data: response } = await api.get(`/api/v2/session-plannings/${props.sessionId}`); const data = response.data; reset({ ...data, training_group_id: String(data.training_group_id), month: Number(data.date.slice(5, 7)), absence_inscription_ids: [], phases: data.phases.map(phase => ({ ...blankPhase(), ...phase, diagram: phase.diagram ?? [] })) }); identityLocked.value = data.attendance_synced; periodLocked.value = data.period_locked; await loadDays(); await loadAttendance(data.attendance_synced ? data.absence_inscription_ids : null) } modal.value.show() } catch (e) { error.value = e.response?.data?.message ?? 'No fue posible cargar la planificación.' } finally { loading.value = false } }
async function save() { for (let index = 0; index <= form.phases.length; index++) { if (!validate(index)) { step.value = index; return } } saving.value = true; error.value = null; try { const payload = { ...form, training_group_id: Number(form.training_group_id), sync_attendance: true, absence_inscription_ids: form.absence_inscription_ids.map(option => Number(option?.value ?? option)).filter(Number.isInteger), phases: form.phases.map((phase, index) => ({ ...phase, position: index + 1 })) }; if (props.sessionId) await api.put(`/api/v2/session-plannings/${props.sessionId}`, payload); else await api.post('/api/v2/session-plannings', payload); modal.value.hide(); emit('updated') } catch (e) { error.value = Object.values(e.response?.data?.errors ?? {}).flat()[0] ?? e.response?.data?.message ?? 'No fue posible guardar.' } finally { saving.value = false } }
function close() { modal.value.hide(); emit('cancel') }
watch(() => [props.show, props.sessionId], ([show]) => { if (modal.value) show ? prepare() : modal.value.hide() })
onMounted(() => { modal.value = new window.bootstrap.Modal(modalRef.value, { backdrop: 'static', keyboard: false, focus: false }) })
</script>

<style scoped>
.session-phase-field {
    width: min(100%, 820px);
    margin-inline: auto;
}
</style>
