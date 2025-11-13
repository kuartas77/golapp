<template>
    <Form ref="form_matches" :validation-schema="schema" :initial-values="{ date: null, hour: null }"
        @submit="handleSubmit">

        <div class="layout-px-spacing">
            <div class="account-settings-container layout-top-spacing">

                <div class="account-content">
                    <div class="panel">
                        <div class="panel-body">
                            <Loader :is-loading="isLoading" />
                            <div class="row col-md-12 no-print">

                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <label for="competition_group" class="form-label">Grupo Competencia</label>
                                        <Field name="competition_group" as="input" id="competition_group" readonly
                                            class="form-control-plaintext">
                                        </Field>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <label for="professor" class="form-label">Director Técnico</label>
                                        <Field name="professor" as="input" id="professor" readonly
                                            class="form-control-plaintext">
                                        </Field>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <label for="tournament_id" class="form-label">Torneo<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <Field name="tournament_id" as="select" id="tournament_id"
                                            class="form-select form-select-sm">
                                            <option :value="item.value" v-for="item in settingsGroup.tournaments"
                                                :key="item.value">
                                                {{ item.label }}
                                            </option>
                                        </Field>
                                        <ErrorMessage name="tournament_id" class="custom-error" />
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <inputField label="Lugar" name="place" :is-required="true" />
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <inputField label="Encuentro #" name="num_match" :is-required="true" />
                                    </div>
                                </div>


                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <label for="date" class="form-label">Fecha<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                            <Field name="date" v-slot="{ field }" id="date">
                                                <flat-pickr v-bind="field" v-model="field.value"
                                                    :config="flatpickrConfigDate"
                                                    class="form-control form-control-sm flatpickr" id="date" />
                                            </Field>
                                            <ErrorMessage name="date" class="custom-error" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <label for="hour" class="form-label">Hora<span
                                                class="text-danger">&nbsp;(*)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                            <Field name="hour" v-slot="{ field }" id="hour">
                                                <flat-pickr v-bind="field" v-model="field.value"
                                                    :config="flatpickrConfigHour"
                                                    class="form-control form-control-sm flatpickr" id="hour" />
                                            </Field>
                                            <ErrorMessage name="hour" class="custom-error" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-3 col-xl-3">
                                    <div class="form-group">
                                        <inputField label="Equipo Rival" name="rival_name" :is-required="true" />
                                    </div>
                                </div>

                                <h6 class="text-center">Resultado Final</h6>
                                <div class="col-md-12 col-sm-12 col-lg-4 col-xl-4">
                                    <div class="form-group" v-if="isEdition">
                                        <label for="file_upload" class="form-label small">Formato</label>
                                        <input type="file" id="file_upload" name="details"
                                            class="form-control form-control-sm" @change="uploadFileFormat"
                                            accept=".csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                                        <small class="text-muted">Se debe subir el formato descargado y lo mostrará
                                            en el listado de abajo.</small>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label for="final_score_school" class="form-label small">Escuela</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="far fa-futbol"></i></span>
                                            <inputField name="final_score_school" :is-required="true"
                                                id="final_score_school" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-2 col-xl-2">
                                    <div class="form-group">
                                        <label for="final_score_rival" class="form-label small">Rival</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="far fa-futbol"></i></span>
                                            <inputField name="final_score_rival" :is-required="true"
                                                id="final_score_rival" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-lg-4 col-xl-4"></div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="general_concept" class="form-label">Concepto General</label>
                                        <Field name="general_concept" as="textarea" id="general_concept"
                                            class="form-control form-control-sm" rows="2"
                                            placeholder="Concepto General" />
                                        <ErrorMessage name="general_concept" class="custom-error" />
                                    </div>
                                </div>


                            </div>

                            <div class="table-responsive no-print">
                                <table class="table table-bordered table-sm dataTable align-middle ">
                                    <thead>
                                        <tr>
                                            <th class="dt-head-center" style="width: 20%;">deportista</th>
                                            <th class="dt-head-center" style="width: 8%;">asistió?</th>
                                            <th class="dt-head-center" style="width: 8%;">titular?</th>
                                            <th class="dt-head-center" style="width: 10%;">tiempo jugado</th>
                                            <th class="dt-head-center" style="width: 12%;">posición</th>
                                            <th class="dt-head-center" style="width: 6%">goles</th>
                                            <th class="dt-head-center" style="width: 6%">t.amarillas</th>
                                            <th class="dt-head-center" style="width: 6%">t.roja</th>
                                            <th class="dt-head-center" style="width: 9%;">calificación</th>
                                            <th class="dt-head-center" style="width: 15%;">observación</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <template v-if="skills_controls.length">
                                            <tr v-for="(skill_control, index) in skills_controls">
                                                <td>
                                                    <div class="media d-md-flex d-block text-sm-start text-center">
                                                        <div class="media-aside align-self-start avatar avatar-sm me-1">
                                                            <img :src="skill_control.player.photo_url" alt="avatar"
                                                                class="rounded-circle" />
                                                        </div>
                                                        <div class="media-body">
                                                            <div class="d-xl-flex d-block justify-content-between">
                                                                <div>
                                                                    <small>{{ skill_control.player.full_names
                                                                        }}</small>
                                                                    <p>{{ skill_control.player.unique_code }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].assistance`" as="select"
                                                        :id="`skill_controls[${index}].assistance`"
                                                        class="form-select form-select-sm">
                                                        <option value="">Selecciona...</option>
                                                        <option value="1">Sí</option>
                                                        <option value="0">No</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].assistance`"
                                                        class="custom-error" />

                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].titular`" as="select"
                                                        :id="`skill_controls[${index}].titular`"
                                                        class="form-select form-select-sm">
                                                        <option value="">Selecciona...</option>
                                                        <option value="1">Sí</option>
                                                        <option value="0">No</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].titular`"
                                                        class="custom-error" />
                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].played_approx`" as="select"
                                                        :id="`skill_controls[${index}].played_approx`"
                                                        class="form-select form-select-sm">
                                                        <option value="">Selecciona...</option>
                                                        <option value="0">0 MIN</option>
                                                        <option value="1">1 MIN</option>
                                                        <option value="2">2 MIN</option>
                                                        <option value="3">3 MIN</option>
                                                        <option value="4">4 MIN</option>
                                                        <option value="5">5 MIN</option>
                                                        <option value="6">6 MIN</option>
                                                        <option value="7">7 MIN</option>
                                                        <option value="8">8 MIN</option>
                                                        <option value="9">9 MIN</option>
                                                        <option value="10">10 MIN</option>
                                                        <option value="11">11 MIN</option>
                                                        <option value="12">12 MIN</option>
                                                        <option value="13">13 MIN</option>
                                                        <option value="14">14 MIN</option>
                                                        <option value="15">15 MIN</option>
                                                        <option value="16">16 MIN</option>
                                                        <option value="17">17 MIN</option>
                                                        <option value="18">18 MIN</option>
                                                        <option value="19">19 MIN</option>
                                                        <option value="20">20 MIN</option>
                                                        <option value="21">21 MIN</option>
                                                        <option value="22">22 MIN</option>
                                                        <option value="23">23 MIN</option>
                                                        <option value="24">24 MIN</option>
                                                        <option value="25">25 MIN</option>
                                                        <option value="26">26 MIN</option>
                                                        <option value="27">27 MIN</option>
                                                        <option value="28">28 MIN</option>
                                                        <option value="29">29 MIN</option>
                                                        <option value="30">30 MIN</option>
                                                        <option value="31">31 MIN</option>
                                                        <option value="32">32 MIN</option>
                                                        <option value="33">33 MIN</option>
                                                        <option value="34">34 MIN</option>
                                                        <option value="35">35 MIN</option>
                                                        <option value="36">36 MIN</option>
                                                        <option value="37">37 MIN</option>
                                                        <option value="38">38 MIN</option>
                                                        <option value="39">39 MIN</option>
                                                        <option value="40">40 MIN</option>
                                                        <option value="41">41 MIN</option>
                                                        <option value="42">42 MIN</option>
                                                        <option value="43">43 MIN</option>
                                                        <option value="44">44 MIN</option>
                                                        <option value="45">45 MIN</option>
                                                        <option value="46">46 MIN</option>
                                                        <option value="47">47 MIN</option>
                                                        <option value="48">48 MIN</option>
                                                        <option value="49">49 MIN</option>
                                                        <option value="50">50 MIN</option>
                                                        <option value="51">51 MIN</option>
                                                        <option value="52">52 MIN</option>
                                                        <option value="53">53 MIN</option>
                                                        <option value="54">54 MIN</option>
                                                        <option value="55">55 MIN</option>
                                                        <option value="56">56 MIN</option>
                                                        <option value="57">57 MIN</option>
                                                        <option value="58">58 MIN</option>
                                                        <option value="59">59 MIN</option>
                                                        <option value="60">60 MIN</option>
                                                        <option value="61">61 MIN</option>
                                                        <option value="62">62 MIN</option>
                                                        <option value="63">63 MIN</option>
                                                        <option value="64">64 MIN</option>
                                                        <option value="65">65 MIN</option>
                                                        <option value="66">66 MIN</option>
                                                        <option value="67">67 MIN</option>
                                                        <option value="68">68 MIN</option>
                                                        <option value="69">69 MIN</option>
                                                        <option value="70">70 MIN</option>
                                                        <option value="71">71 MIN</option>
                                                        <option value="72">72 MIN</option>
                                                        <option value="73">73 MIN</option>
                                                        <option value="74">74 MIN</option>
                                                        <option value="75">75 MIN</option>
                                                        <option value="76">76 MIN</option>
                                                        <option value="77">77 MIN</option>
                                                        <option value="78">78 MIN</option>
                                                        <option value="79">79 MIN</option>
                                                        <option value="80">80 MIN</option>
                                                        <option value="81">81 MIN</option>
                                                        <option value="82">82 MIN</option>
                                                        <option value="83">83 MIN</option>
                                                        <option value="84">84 MIN</option>
                                                        <option value="85">85 MIN</option>
                                                        <option value="86">86 MIN</option>
                                                        <option value="87">87 MIN</option>
                                                        <option value="88">88 MIN</option>
                                                        <option value="89">89 MIN</option>
                                                        <option value="90">90 MIN</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].played_approx`"
                                                        class="custom-error" />
                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].position`" as="select"
                                                        :id="`skill_controls[${index}].position`"
                                                        class="form-select form-select-sm">
                                                        <option value="">Selecciona...</option>
                                                        <option value="Portero">Portero</option>
                                                        <option value="Defensa(Central)">Defensa(Central)</option>
                                                        <option value="Defensa(Derecho)(Izquierdo)">
                                                            Defensa(Derecho)(Izquierdo)</option>
                                                        <option value="Volante(Central)">Volante(Central)</option>
                                                        <option value="Volante(Primera linea)">Volante(Primera
                                                            linea)
                                                        </option>
                                                        <option value="Volante(Segunda linea)">Volante(Segunda
                                                            linea)
                                                        </option>
                                                        <option value="Volante(Extremo)">Volante(Extremo)</option>
                                                        <option value="Delantero">Delantero</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].position`"
                                                        class="custom-error" />
                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].goals`" as="select"
                                                        :id="`skill_controls[${index}].goals`"
                                                        class="form-select form-select-sm">
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].goals`"
                                                        class="custom-error" />
                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].yellow_cards`" as="select"
                                                        :id="`skill_controls[${index}].yellow_cards`"
                                                        class="form-select form-select-sm">
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].yellow_cards`"
                                                        class="custom-error" />

                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].red_cards`" as="select"
                                                        :id="`skill_controls[${index}].red_cards`"
                                                        class="form-select form-select-sm">
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].red_cards`"
                                                        class="custom-error" />

                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].qualification`" as="select"
                                                        :id="`skill_controls[${index}].qualification`"
                                                        class="form-select form-select-sm">
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                    </Field>

                                                    <ErrorMessage :name="`skill_controls[${index}].qualification`"
                                                        class="custom-error" />

                                                </td>
                                                <td>
                                                    <Field :name="`skill_controls[${index}].general_concept`"
                                                        :id="`skill_controls[${index}].general_concept`" as="textarea"
                                                        class="form-control form-control-sm" rows="2" />
                                                    <ErrorMessage :name="`skill_controls[${index}].general_concept`"
                                                        class="custom-error" />
                                                </td>
                                            </tr>
                                        </template>
                                        <template v-else>
                                            <tr>
                                                <td colspan="10" class="dt-body-center">
                                                    El grupo no cuenta con integrantes
                                                </td>
                                            </tr>
                                        </template>

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="account-settings-footer mt-2 no-print">
                    <div class="as-footer-container">

                        <template v-if="urlExportFormat">
                            <a :href="urlExportFormat" class="btn btn-info">Descargar formato</a>
                        </template>

                        <button type="submit" class="btn btn-info" :disabled="!skills_controls.length">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </Form>
</template>
<script setup>
import "@/assets/sass/users/account-setting.scss"
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import dayjs from '@/utils/dayjs'
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component'
import Loader from '@/components/general/Loader'

import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta"
import { ErrorMessage, Field, Form } from "vee-validate"
import * as yup from 'yup'
import { getCurrentInstance, useTemplateRef, onMounted, ref } from "vue"
import { useRoute } from "vue-router"
import { useSettingGroups } from '@/store/settings-store'

const props = defineProps({ isEdition: { type: Boolean, default: false } })

const { proxy } = getCurrentInstance()
const globalError = ref(null)
const route = useRoute()
const settingsGroup = useSettingGroups()
const currentTitlePage = ref("")
const formMatches = useTemplateRef('form_matches')
const isLoading = ref(true)
const urlExportFormat = ref(null)
const skills_controls = ref([])
const players = ref([])
// settings flatpick
const flatpickrConfigDate = {
    locale: Spanish,
    minDate: dayjs().subtract(1, 'month').format('YYYY-M-D'),
    maxDate: dayjs().format('YYYY-M-D'),
}
const flatpickrConfigHour = {
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K"
}

const schema = yup.object().shape({
    id: yup.number().nullable(),
    tournament_id: yup.string().required(),
    competition_group_id: yup.string(),
    place: yup.string().required(),
    num_match: yup.number().integer().required().typeError('Debe ser un número.'),
    date: yup.date().required(),
    hour: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 9:30 AM o 12:00 PM)'
    ).required(),
    rival_name: yup.string().required(),
    final_score_school: yup.number().integer().required().typeError('Debe ser un número.'),
    final_score_rival: yup.number().integer().required().typeError('Debe ser un número.'),
    general_concept: yup.string().nullable(),
    skill_controls: yup.array().of(
        yup.object({
            id: yup.number().nullable(),
            assistance: yup.string().required('Es requerido'),
            titular: yup.string().required('Es requerido'),
            played_approx: yup.string().required('Es requerido'),
            position: yup.string().nullable().required('Es requerido'),
            goals: yup.number().integer().required('Es requerido'),
            yellow_cards: yup.number().integer().required('Es requerido'),
            red_cards: yup.number().integer().required('Es requerido'),
            qualification: yup.number().integer().required('Es requerido'),
            general_concept: yup.string().nullable(),
            game_id: yup.number().nullable(),
        })
    )
})


const onLoadData = async () => {
    try {
        let url = ''
        let dataParams = {}
        if (props.isEdition) {
            url = `/api/v2/matches/${route.params.id}`
        } else {
            url = '/api/v2/matches/0'
            dataParams.competition_group = route.params.grupo_competencia
        }

        isLoading.value = true


        const response = await api.get(url, { params: dataParams })
        if (response.status === 200 && response.data) {
            const match = response.data
            skills_controls.value = match.skills_controls
            players.value = match.skills_controls.map(item => item.player)

            urlExportFormat.value = match.competition_group.url_format_match
            // urlExportFormat.value = match.id ? null : match.competition_group.url_format_match
            formMatches.value.setValues({
                id: match.id,
                competition_group_id: match.competition_group.id,
                competition_group: match.competition_group.name,
                tournament_id: match.competition_group.tournament.id,
                professor: match.competition_group.professor.name,
                place: match.place,
                date: match.date,
                hour: match.hour,
                num_match: match.num_match,
                rival_name: match.rival_name,
                final_score_school: match.final_score.soccer,
                final_score_rival: match.final_score.rival,
                general_concept: match.general_concept,
                skill_controls: match.skills_controls
            })
        }
    } catch (error) {
        console.log(error)
        showMessage('Algo salió mal.', 'error')
    } finally {
        isLoading.value = false
    }
}

const handleSubmit = async (values, actions) => {

    try {

        let data = {}
        let url = ''
        if (props.isEdition) {
            url = `/api/v2/matches/${route.params.id}`
            data = { _method: 'PUT', ...values }
        } else {
            url = `/api/v2/matches`
            data = { ...values }
        }

        isLoading.value = true

        const response = await api.post(url, data)

        if (response.data.success) {
            showMessage('Guardado correctamente.')
        }

    } catch (error) {
        showMessage('Algo salió mal.', 'error')
        proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
    } finally {
        isLoading.value = false
    }
}

const uploadFileFormat = async (e) => {

    if (!props.isEdition) {
        return
    }
    const file = e.target.files[0];
    const formData = new FormData();
    formData.append('file', file, file.name)
    console.log('upload', file)

    api.post(`/import/matches/${route.params.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(resp => {
            if (resp.data.success) {
                showMessage('Guardado correctamente.')
            } else {
                showMessage('Algo salió mal.', 'error')
            }
        })
}

onMounted(() => {
    onLoadData()
    settingsGroup.getGroupSettings()
    currentTitlePage.value = props.isEdition ? `Competencia ${route.params.id}` : 'Crear Competencia';
    usePageTitle(currentTitlePage);
})

</script>
<style lang="scss" scoped>
.scroll-container {
    min-height: 70vh;
    max-height: 70vh;
    overflow: auto;
}
</style>