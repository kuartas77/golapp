<template>
    <div class="modal fade" id="composeModalInscription" tabindex="-1" role="dialog" aria-labelledby="modalInscription"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="initialData" v-slot="{ isSubmitting }">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInscription">{{ isEditing ? 'Modificar inscripción' : 'Inscripción' }}</h5>
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                            class="btn-close" @click="onCancel"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>
                        <div v-if="isReactivationMode" class="alert alert-warning py-2" role="alert">
                            Se reactivará una inscripción retirada del año {{ selectedInscriptionYear }}. La fecha de inicio original se conservará y podrás ajustar los demás datos antes de guardar.
                        </div>
                        <div class="row col-12 ">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="unique_code">Código único</label><span class="text-danger">(*)</span>:
                                    <Field v-if="isEditing" name="unique_code" as="input" id="unique_code" readonly
                                        class="form-control form-control-sm" />
                                    <Field v-else name="unique_code" v-slot="{ field }">
                                        <TypeAhead inputClass="form-control form-control-sm"
                                            dropdownClass="dropdown custom-dropdown-icon"
                                            dropdownMenuClass="dropdown-menu w-100" id="unique_code" :items="search"
                                            :itemProjection="item => item" :modelValue="field.value"
                                            @update:modelValue="field.onChange($event);onChangeCode($event)"/>
                                    </Field>

                                    <small v-if="isEditing" class="form-text text-muted">El jugador no se puede modificar desde este formulario.</small>
                                    <small v-else class="form-text text-muted">Buscará deportistas sin inscripción activa en el año {{ selectedInscriptionYear }}.</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="player_name">Jugador:</label>
                                    <Field name="player_name" as="input" id="player_name" readonly
                                        class="form-control-plaintext">
                                    </Field>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="start_date">Fecha de inicio</label><span class="text-danger">(*)</span>:
                                    <Field v-if="isEditing || isReactivationMode" name="start_date" as="input" id="start_date" readonly
                                        class="form-control form-control-sm" />
                                    <Field v-else name="start_date" v-slot="{ field }" id="start_date">
                                        <flat-pickr v-bind="field" v-model="field.value" :config="flatpickrConfigDate"
                                            class="form-control form-control-sm flatpickr" id="start_date" />
                                    </Field>
                                    <ErrorMessage name="start_date" class="custom-error" />
                                    <small v-if="isEditing || isReactivationMode" class="form-text text-muted">La fecha de inicio se conserva para evitar desajustes con pagos y asistencias.</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <small class="form-text text-muted mt-4">Al ser becado, todas las mensualidades del año
                                    se estableceran cómo: "<span class="text-warning">Becado</span>"</small>
                                <checkbox label="¿ Becado ?" name="scholarship" />
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="monthly_payment_type">Tarifa mensual:</label>
                                    <Field name="monthly_payment_type" v-slot="{ field, handleChange }">
                                        <CustomSelect2
                                            id="monthly_payment_type"
                                            :options="monthlyPaymentOptions"
                                            :modelValue="field.value"
                                            :clearable="false"
                                            @update:modelValue="handleChange"
                                        />
                                    </Field>
                                    <ErrorMessage name="monthly_payment_type" class="custom-error" />
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="training_group_id">Grupo de entrenamiento:</label>
                                    <Field name="training_group_id" v-slot="{ field, handleChange }">
                                        <CustomSelect2
                                            id="training_group_id"
                                            :options="trainingGroups"
                                            :modelValue="field.value"
                                            @update:modelValue="(value) => { handleChange(value); onTrainingGroupChange(value) }"
                                        />
                                    </Field>
                                    <ErrorMessage name="training_group_id" class="custom-error" as="div" />
                                    <small class="form-text text-muted">Si no se selecciona, se agregará al grupo
                                        "Provisional"</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label for="competition_groups">Grupo de competencia:</label>
                                    <Field name="competition_groups" as="CustomSelect2" :options="competitionGroups" id="competition_groups"
                                        :multiple="true" />
                                    <ErrorMessage name="competition_groups" class="custom-error" />
                                </div>
                            </div>
                        </div>
                        <div class="row col-12">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <h6 class="text-center text-uppercase text-muted"><strong>Documentos</strong></h6>
                                <div class="check col">
                                    <checkbox label="Fotos" name="photos" />
                                    <checkbox label="Fotocopia Doc. Identidad" name="copy_identification_document" />
                                    <checkbox label="Certificado de Salud" name="eps_certificate" />
                                    <checkbox label="Certificado Médico" name="medic_certificate" />
                                    <checkbox label="Fotocopia Doc. Acudiente" name="study_certificate" />
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <h6 class="text-center text-uppercase text-muted">
                                    <strong>Pre-Inscripción</strong>
                                </h6>
                                <div
                                    class="rounded border p-2 mb-2"
                                    :class="preInscriptionAutoReason ? 'bg-light border-warning' : 'bg-light border-secondary'"
                                >
                                    <small class="d-block text-uppercase fw-semibold mb-1">
                                        {{ preInscriptionStatusTitle }}
                                    </small>
                                    <small class="d-block">
                                        {{ preInscriptionStatusMessage }}
                                    </small>
                                </div>
                                <Field name="pre_inscription" v-slot="{ value, handleChange }">
                                    <div class="form-group mt-2">
                                        <div class="form-check ps-0">
                                            <div class="custom-control custom-checkbox checkbox-primary">
                                                <input
                                                    id="pre_inscription"
                                                    type="checkbox"
                                                    class="custom-control-input"
                                                    :checked="Boolean(value)"
                                                    @change="(event) => onPreInscriptionInput(event, handleChange)"
                                                />
                                                <label class="custom-control-label" for="pre_inscription">
                                                    Marcar como preinscripción
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </Field>
                                <small class="form-text text-muted">
                                    Marca esta opción solo si la inscripción aún está pendiente de documentación.
                                </small>
                                <ErrorMessage name="pre_inscription" class="custom-error" as="div" />
                            </div>

                        </div>
                        <div class="row col-12 mt-3">
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <h6 class="text-uppercase text-muted mb-0">
                                            <strong>Cargos personalizados</strong>
                                        </h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted">Vencimiento nuevos</small>
                                            <flat-pickr
                                                v-model="customChargesDueDate"
                                                :config="flatpickrConfigDateIssue"
                                                class="form-control form-control-sm custom-charge-date"
                                            />
                                        </div>
                                    </div>

                                    <div v-if="customChargesLoading" class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </div>

                                    <div v-else-if="customChargeRows.length" class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 36%">Cargo</th>
                                                    <th style="width: 18%">Estado</th>
                                                    <th style="width: 24%">Valor</th>
                                                    <th style="width: 12%" class="text-center">Asignar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="charge in customChargeRows" :key="charge.rowKey">
                                                    <td>
                                                        <div class="fw-semibold">{{ charge.name }}</div>
                                                        <small class="text-muted">
                                                            Base {{ moneyFormat(Number(charge.unit_price || 0)) }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge" :class="customChargeStatusClass(charge.status)">
                                                            {{ customChargeStatusLabel(charge.status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <CurrencyInput
                                                            v-model="charge.value"
                                                            class="form-control form-control-sm"
                                                            :disabled="charge.disabled || !charge.selected"
                                                            autocomplete="off"
                                                        />
                                                    </td>
                                                    <td class="text-center">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            v-model="charge.selected"
                                                            :disabled="charge.disabled"
                                                            @change="onCustomChargeToggle(charge, $event)"
                                                        >
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <small v-else class="text-muted d-block py-2">
                                        No hay cargos personalizados configurados para esta escuela.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" :disabled="isSubmitting" @click="onCancel">Cerrar</button>
                        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
<script>
export default {
    name: 'modal_inscription'
}
</script>
<script setup>
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import dayjs from '@/utils/dayjs'
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component'
import TypeAhead from 'vue3-bootstrap-typeahead'
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, useTemplateRef, watch } from "vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import { useSetting } from "@/store/settings-store";
import CurrencyInput from '@/components/general/CurrencyInput';

const props = defineProps({
    inscription_id: {
        type: [Number, String],
        default: null,
    },
    create_open: {
        type: Boolean,
        default: false,
    },
    selected_year: {
        type: [Number, String],
        default: null,
    },
    unique_code: {
        type: String,
        default: null,
    },
})
const emit = defineEmits(["success", "cancel"]);

const { proxy } = getCurrentInstance();
const globalError = ref(null);
const settings = useSetting();
const form = useTemplateRef("form");
const composeModalInscription = ref(null);
const currentTrainingGroupId = ref(null);
const currentPreInscription = ref(false);
const reactivationCandidate = ref(null);
const customChargeCatalog = ref([]);
const existingCustomCharges = ref([]);
const customChargeRows = ref([]);
const customChargesLoading = ref(false);
const customChargesDueDate = ref(dayjs().add(15, 'day').format('YYYY-MM-DD'));
const customChargeRemovalIds = ref([]);
const editIdentifier = computed(() => props.inscription_id ?? props.unique_code);
const isEditing = computed(() => editIdentifier.value !== null);
const selectedInscriptionYear = computed(() => {
    const year = Number(props.selected_year ?? dayjs().year())

    return Number.isInteger(year) && year > 0 ? year : dayjs().year()
});
const isReactivationMode = computed(() => !isEditing.value && Boolean(reactivationCandidate.value?.id));
const trainingGroups = computed(() => settings.groups.map((group) => ({ value: group.id, label: group.name })));
const monthlyPaymentOptions = [
    { value: 'MONTHLY_PAYMENT', label: 'Mensualidad por defecto' },
    { value: 'BROTHER_MONTHLY_PAYMENT', label: 'Mensualidad hermano' },
    { value: 'MONTHLY_PAYMENT_OPTION_1', label: 'Mensualidad opción 1' },
    { value: 'MONTHLY_PAYMENT_OPTION_2', label: 'Mensualidad opción 2' },
    { value: 'MONTHLY_PAYMENT_OPTION_3', label: 'Mensualidad opción 3' },
];
const competitionGroups = computed(() => settings.competition_groups.map((group) => ({
    value: String(group.id),
    label: group.full_name_group ?? group.full_name ?? group.name ?? String(group.id),
})));
const provisionalTrainingGroup = computed(() => settings.all_groups?.[0] ?? null);
const hasTrainingGroupSelected = computed(() => ![null, '', undefined].includes(currentTrainingGroupId.value));
const preInscriptionAutoReason = computed(() => {
    if (!hasTrainingGroupSelected.value) {
        return 'empty'
    }

    if (provisionalTrainingGroup.value && String(currentTrainingGroupId.value) === String(provisionalTrainingGroup.value.id)) {
        return 'provisional'
    }

    return null
});
const preInscriptionStatusTitle = computed(() => {
    if (preInscriptionAutoReason.value) {
        return 'Se guardará como preinscripción'
    }

    if (currentPreInscription.value) {
        return 'Preinscripción manual'
    }

    return 'Inscripción lista para activarse'
});
const preInscriptionStatusMessage = computed(() => {
    if (preInscriptionAutoReason.value === 'empty') {
        return `Si guardas sin grupo de entrenamiento, el sistema asignará "${provisionalTrainingGroup.value?.name ?? 'Provisional'}". Mientras esté en preinscripción o en ese grupo, este deportista no aparecerá en asistencias ni pagos.`
    }

    if (preInscriptionAutoReason.value === 'provisional') {
        return `El grupo seleccionado es "${provisionalTrainingGroup.value?.name ?? 'Provisional'}". Mientras permanezca en ese grupo o en preinscripción, este deportista no aparecerá en asistencias ni pagos.`
    }

    if (currentPreInscription.value) {
        return 'Usa este estado cuando la inscripción aún esté pendiente de validación documental. En este estado, el deportista no aparecerá en asistencias ni pagos.'
    }

    return 'Con grupo definitivo y documentación validada, el deportista podrá aparecer en asistencias y pagos.'
});

const resolveDefaultStartDate = (year = selectedInscriptionYear.value) => {
    const normalizedYear = Number(year)

    if (normalizedYear === dayjs().year()) {
        return dayjs().format('YYYY-M-D')
    }

    return dayjs(`${normalizedYear}-01-01`).format('YYYY-M-D')
}

const flatpickrConfigDate = computed(() => {
    const year = selectedInscriptionYear.value

    return {
        locale: Spanish,
        minDate: `${year}-01-01`,
        maxDate: year === dayjs().year()
            ? dayjs().format('YYYY-M-D')
            : `${year}-12-31`,
    }
})

const flatpickrConfigDateIssue = computed(() => {
    const year = selectedInscriptionYear.value

    return {
        locale: Spanish,
        minDate: `${year}-01-01`,
    }
})

const resolveMonthlyPaymentType = (type, brotherPayment = false) => {
    const validType = monthlyPaymentOptions.some((option) => option.value === type)

    if (validType) {
        return type
    }

    return brotherPayment ? 'BROTHER_MONTHLY_PAYMENT' : 'MONTHLY_PAYMENT'
}

const defaultValues = () => ({
    id: null,
    player_id: null,
    unique_code: null,
    player_name: null,
    start_date: resolveDefaultStartDate(),
    scholarship: false,
    brother_payment: false,
    monthly_payment_type: 'MONTHLY_PAYMENT',
    training_group_id: null,
    competition_groups: [],
    photos: false,
    copy_identification_document: false,
    eps_certificate: false,
    medic_certificate: false,
    study_certificate: false,
    pre_inscription: false,
});

const initialData = defaultValues();

const schema = yup.object().shape({
    player_id: yup.string().nullable(),
    unique_code: yup.string().required('Ingresa un código único'),
    player_name: yup.string().required('Ingresa un código único'),
    start_date: yup.date().required('La Fecha de inicio es requerida'),
    scholarship: yup.boolean().default(false),
    brother_payment: yup.boolean().default(false),
    monthly_payment_type: yup.string().oneOf(monthlyPaymentOptions.map((option) => option.value)).default('MONTHLY_PAYMENT'),
    training_group_id: yup.mixed().nullable(),
    competition_groups: yup.array().default([]),
    photos: yup.boolean().default(false),
    copy_identification_document: yup.boolean().default(false),
    eps_certificate: yup.boolean().default(false),
    medic_certificate: yup.boolean().default(false),
    study_certificate: yup.boolean().default(false),
    pre_inscription: yup.boolean().default(false),
})

const resetFormState = () => {
    form.value?.resetForm({ values: defaultValues() })
    currentTrainingGroupId.value = null
    currentPreInscription.value = false
    reactivationCandidate.value = null
    existingCustomCharges.value = []
    customChargeRemovalIds.value = []
    customChargesDueDate.value = dayjs().add(15, 'day').format('YYYY-MM-DD')
    rebuildCustomChargeRows()
    globalError.value = null
}

const closeModal = () => {
    modalHidden()
    composeModalInscription.value?.hide()
}

const onCancel = () => {
    resetFormState()
    closeModal()
    emit("cancel")
}

const search = async (query) => {
    if (!query) return [];

    const response = await api.get('/api/v2/autocomplete/list_code_unique?trashed=true', {
        params: {
            query,
            year: selectedInscriptionYear.value,
        },
    })
    return response.data.data ?? []
}

const openCreateModal = () => {
    resetFormState()
    loadCustomChargeData()
    composeModalInscription.value?.show()
}

const loadInscriptionForEdit = async (inscriptionId) => {
    try {
        resetFormState()
        const [resp] = await Promise.all([
            api.get(`/api/v2/inscriptions/${inscriptionId}/edit`),
            loadCustomChargeData(inscriptionId),
        ])
        const data = resp.data

        currentTrainingGroupId.value = data.training_group_id
        currentPreInscription.value = Boolean(data.pre_inscription)
        form.value.setValues({
            ...defaultValues(),
            id: data.id,
            player_id: data.player_id,
            unique_code: data.unique_code,
            player_name: data.player.full_names,
            start_date: data.start_date,
            scholarship: Boolean(data.scholarship),
            brother_payment: Boolean(data.brother_payment),
            monthly_payment_type: resolveMonthlyPaymentType(data.monthly_payment_type, data.brother_payment),
            training_group_id: data.training_group_id,
            competition_groups: data.competition_groups ?? [],
            photos: Boolean(data.photos),
            copy_identification_document: Boolean(data.copy_identification_document),
            eps_certificate: Boolean(data.eps_certificate),
            medic_certificate: Boolean(data.medic_certificate),
            study_certificate: Boolean(data.study_certificate),
            pre_inscription: Boolean(data.pre_inscription),
        })

        composeModalInscription.value?.show()
    } catch (error) {
        showMessage("No se pudo cargar la inscripción.", 'error')
        emit("cancel")
    }
}

const loadCustomChargeCatalog = async () => {
    if (customChargeCatalog.value.length) {
        return
    }

    const { data } = await api.get('/api/v2/admin/invoice-items-custom')
    customChargeCatalog.value = Array.isArray(data) ? data : data.data ?? []
}

const loadCustomChargeData = async (inscriptionId = null) => {
    customChargesLoading.value = true

    try {
        await loadCustomChargeCatalog()

        if (inscriptionId) {
            const { data } = await api.get(`/api/v2/inscriptions/${inscriptionId}/custom-charges`)
            existingCustomCharges.value = Array.isArray(data) ? data : data.data ?? []
        } else {
            existingCustomCharges.value = []
        }

        rebuildCustomChargeRows()
    } catch (error) {
        showMessage('No se pudieron cargar los cargos personalizados.', 'error')
    } finally {
        customChargesLoading.value = false
    }
}

const rebuildCustomChargeRows = () => {
    const activeByCatalogId = new Map()

    existingCustomCharges.value.forEach((charge) => {
        if (customChargeRemovalIds.value.includes(Number(charge.id))) {
            return
        }

        if (['pending', 'due'].includes(charge.status) && charge.invoice_custom_item_id) {
            activeByCatalogId.set(Number(charge.invoice_custom_item_id), charge)
        }
    })

    const catalogRows = customChargeCatalog.value.map((item) => {
        const existingCharge = activeByCatalogId.get(Number(item.id))

        return existingCharge
            ? mapExistingCharge(existingCharge, item)
            : {
                rowKey: `catalog-${item.id}`,
                id: null,
                invoice_custom_item_id: item.id,
                name: item.name,
                unit_price: Number(item.unit_price || 0),
                value: Number(item.unit_price || 0),
                status: 'available',
                selected: false,
                disabled: false,
            }
    })

    const historicalRows = existingCustomCharges.value
        .filter((charge) => !customChargeRemovalIds.value.includes(Number(charge.id)))
        .filter((charge) => !['pending', 'due'].includes(charge.status) || !charge.invoice_custom_item_id)
        .map((charge) => mapExistingCharge(charge))

    customChargeRows.value = [...catalogRows, ...historicalRows]
}

const mapExistingCharge = (charge, catalogItem = null) => ({
    rowKey: `charge-${charge.id}`,
    id: charge.id,
    invoice_custom_item_id: charge.invoice_custom_item_id,
    name: charge.name,
    unit_price: Number(catalogItem?.unit_price ?? charge.invoice_custom_item?.unit_price ?? charge.value ?? 0),
    value: Number(charge.value || 0),
    status: charge.status,
    selected: true,
    disabled: ['due', 'paid'].includes(charge.status) || Boolean(charge.invoice_item_id),
})

const onCustomChargeToggle = (charge, event) => {
    const checked = Boolean(event?.target?.checked)

    if (charge.status === 'due') {
        charge.selected = true
        return
    }

    if (!checked && charge.id && charge.status === 'pending') {
        const chargeId = Number(charge.id)

        if (!customChargeRemovalIds.value.includes(chargeId)) {
            customChargeRemovalIds.value.push(chargeId)
        }

        const catalogItem = customChargeCatalog.value.find((item) => Number(item.id) === Number(charge.invoice_custom_item_id))
        charge.rowKey = `catalog-${charge.invoice_custom_item_id}`
        charge.id = null
        charge.status = 'available'
        charge.selected = false
        charge.disabled = false
        charge.value = Number(catalogItem?.unit_price ?? charge.unit_price ?? 0)
        charge.unit_price = Number(catalogItem?.unit_price ?? charge.unit_price ?? 0)
    }
}

const customChargeStatusLabel = (status) => ({
    available: 'Disponible',
    pending: 'Pendiente',
    due: 'Debe',
    paid: 'Pagado',
}[status] ?? status)

const customChargeStatusClass = (status) => ({
    available: 'badge-light',
    pending: 'badge-warning',
    due: 'badge-danger',
    paid: 'badge-success',
}[status] ?? 'badge-light')

const selectedCustomChargesPayload = () => [
    ...customChargeRemovalIds.value.map((id) => ({
        id,
        _delete: true,
    })),
    ...customChargeRows.value
        .filter((charge) => charge.selected && !charge.disabled)
        .map((charge) => ({
        id: charge.id,
        invoice_custom_item_id: charge.invoice_custom_item_id,
        value: Number(charge.value || 0),
        due_date: customChargesDueDate.value,
    })),
]

const loadPlayerByUniqueCode = async (uniqueCode) => {
    if (!uniqueCode) {
        reactivationCandidate.value = null
        currentTrainingGroupId.value = null
        currentPreInscription.value = false
        form.value?.setValues({
            ...defaultValues(),
            unique_code: uniqueCode,
        })
        return
    }

    try {
        const response = await api.get('/api/v2/autocomplete/search_unique_code?unique=true', {
            params: {
                unique_code: uniqueCode,
                year: selectedInscriptionYear.value,
            },
        })
        const data = response.data.data

        if (!data) {
            reactivationCandidate.value = null
            currentTrainingGroupId.value = null
            currentPreInscription.value = false
            form.value?.setValues({
                ...defaultValues(),
                unique_code: uniqueCode,
            })
            showMessage(`El deportista ya tiene una inscripción activa para ${selectedInscriptionYear.value}.`, 'warning')
            return
        }

        const reactivationInscription = data.reactivation_inscription ?? null
        reactivationCandidate.value = reactivationInscription

        form.value.setValues({
            ...defaultValues(),
            unique_code: uniqueCode,
            player_id: data.id,
            player_name: data.full_names,
            start_date: reactivationInscription?.start_date || resolveDefaultStartDate(),
            scholarship: Boolean(reactivationInscription?.scholarship),
            brother_payment: Boolean(reactivationInscription?.brother_payment),
            monthly_payment_type: resolveMonthlyPaymentType(reactivationInscription?.monthly_payment_type, reactivationInscription?.brother_payment),
            training_group_id: reactivationInscription?.training_group_id ?? null,
            competition_groups: reactivationInscription?.competition_groups ?? [],
            photos: Boolean(reactivationInscription?.photos),
            copy_identification_document: Boolean(reactivationInscription?.copy_identification_document),
            eps_certificate: Boolean(reactivationInscription?.eps_certificate),
            medic_certificate: Boolean(reactivationInscription?.medic_certificate),
            study_certificate: Boolean(reactivationInscription?.study_certificate),
            pre_inscription: Boolean(reactivationInscription?.pre_inscription),
        })
        currentTrainingGroupId.value = reactivationInscription?.training_group_id ?? null
        currentPreInscription.value = Boolean(reactivationInscription?.pre_inscription)
    } catch (error) {
        reactivationCandidate.value = null
        currentTrainingGroupId.value = null
        currentPreInscription.value = false
        form.value?.setValues({
            ...defaultValues(),
            unique_code: uniqueCode,
        })
        showMessage("El deportista no se encontró o no está disponible para el año seleccionado.", 'error')
    }
}

const submit = async (values, actions) => {
    try {
        globalError.value = null
        let response = null
        const data = { ...values }
        data.custom_charges = selectedCustomChargesPayload()
        data.custom_charges_due_date = customChargesDueDate.value

        if (isEditing.value) {
            data._method = 'PUT'
            response = await api.post(`/api/v2/inscriptions/${data.id}`, data)
        } else {
            response = await api.post(`/api/v2/inscriptions`, data)
        }

        if (response.data.success !== true) {
            showMessage('Algo salió mal', 'error')
            return
        }

        const message = isEditing.value
            ? 'Modificado correctamente'
            : response.data.reactivated
                ? 'Inscripción reactivada correctamente'
                : 'Guardado correctamente'
        showMessage(message)
        emit("success")
        closeModal()
        resetFormState()
    } catch (error) {
        proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
    }
}

watch(
    editIdentifier,
    async (newValue) => {
        if (newValue !== null) {
            await loadInscriptionForEdit(newValue)
        }
    }
)

watch(
    () => props.create_open,
    (isOpen) => {
        if (isOpen && !isEditing.value) {
            openCreateModal()
        }
    }
)

const onChangeCode = (uniqueCode) => {
    loadPlayerByUniqueCode(uniqueCode)
}

const onTrainingGroupChange = (value) => {
    currentTrainingGroupId.value = value
}

const onPreInscriptionChange = (value) => {
    currentPreInscription.value = Boolean(value)
}

const onPreInscriptionInput = (event, handleChange) => {
    const checked = Boolean(event?.target?.checked)

    handleChange(checked)
    onPreInscriptionChange(checked)
}

const listenerClickOutSide = (event) => {
    const modalElement = document.getElementById("composeModalInscription")
    if (event.target === modalElement) {
        showMessage("Guarda los cambios o Cancela.", 'warning')
    }
}

onMounted(async () => {
    const modalElement = document.getElementById("composeModalInscription")
    composeModalInscription.value = new window.bootstrap.Modal(modalElement,
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    )

    modalElement.addEventListener('click', listenerClickOutSide)

    await settings.getSettings()
    await loadCustomChargeCatalog()
    rebuildCustomChargeRows()
})

onBeforeUnmount(() => {
    const modalElement = document.getElementById("composeModalInscription")
    modalElement?.removeEventListener('click', listenerClickOutSide)
})
</script>

<style scoped>
.custom-charge-date {
    width: 130px;
}
</style>
