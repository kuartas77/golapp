<template>
    <div class="modal fade" id="composeModalCompetitionG" tabindex="-1" role="dialog" aria-labelledby="modalTrainigG"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <Form ref="form" :validation-schema="schema" @submit="submit" :initial-values="initialData">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTrainigG">Grupo de competencia</h5>
                        <button type="button" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                            class="btn-close" @click="onCancel"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <inputField label="Nombre del grupo" name="name" :is-required="true" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_id" class="form-label">Formador</label><span
                                            class="text-danger">*</span>
                                        <Field name="user_id" as="CustomSelect2" id="user_id"
                                            :options="userOptions" />
                                        <ErrorMessage name="user_id" class="custom-error" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Torneo</label><span
                                            class="text-danger">*</span>
                                        <Field name="tournament_id" as="CustomSelect2" id="tournament_id"
                                            :options="tournamentOptions" />
                                        <ErrorMessage name="tournament_id" class="custom-error" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year" class="form-label">Categoria</label><span
                                            class="text-danger">*</span>
                                        <Field name="year" as="CustomSelect2" id="year" :options="categoryOptions" />
                                        <ErrorMessage name="year" class="custom-error" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn" @click="onCancel">
                            <i class="flaticon-cancel-12"></i> Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </Form>
        </div>
    </div>
</template>
<script>
export default {
    name: 'modal_competition_group'
}
</script>
<script setup>
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, useTemplateRef, watch } from "vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import { useSettingGroups } from "@/store/settings-store";

const url = "/api/v2/admin/competition_groups";
const props = defineProps({
    id: {
        type: String,
        default: null,
    },
});
const emit = defineEmits(["update", "cancel"]);

const { proxy } = getCurrentInstance();
const globalError = ref(null);
const settingsGroup = useSettingGroups();
const form = useTemplateRef("form");
const composeModalCompetitionG = ref(null);
const modalElement = ref(null);
const selectedUserOptions = ref([]);
const selectedTournamentOptions = ref([]);
const selectedCategoryOptions = ref([]);

const buildDefaultValues = () => ({
    name: null,
    user_id: null,
    tournament_id: null,
    year: null,
});
const initialData = ref(buildDefaultValues());

const normalizeOption = (value, label = value) => ({
    value: String(value),
    label: String(label ?? value),
});

const mergeOptionLists = (...optionLists) => {
    const mergedOptions = new Map();

    optionLists
        .flat()
        .filter((option) => option?.value !== undefined && option?.value !== null && option?.value !== "")
        .forEach((option) => {
            const normalized = normalizeOption(
                option.value ?? option.id,
                option.label ?? option.name ?? option.text ?? option.value ?? option.id
            );

            mergedOptions.set(normalized.value, normalized);
        });

    return Array.from(mergedOptions.values());
};

const findOptionByValue = (options, value) => {
    if (value === null || value === undefined || value === "") {
        return null;
    }

    return options.find((option) => option.value === String(value)) ?? null;
};

const userOptions = computed(() => mergeOptionLists(settingsGroup.users, selectedUserOptions.value));
const tournamentOptions = computed(() => mergeOptionLists(settingsGroup.tournaments, selectedTournamentOptions.value));
const categoryOptions = computed(() => mergeOptionLists(settingsGroup.categories, selectedCategoryOptions.value));

const clearSelectedOptions = () => {
    selectedUserOptions.value = [];
    selectedTournamentOptions.value = [];
    selectedCategoryOptions.value = [];
};

const resetFormState = () => {
    globalError.value = null;
    clearSelectedOptions();
    form.value?.resetForm({ values: buildDefaultValues() });
};

const schema = yup.object().shape({
    name: yup.string().required(),
    user_id: yup.string().required(),
    tournament_id: yup.string().required(),
    year: yup.string().required()
});

const submit = async (values, actions) => {
    try {
        let urlAction = url
        if (props.id) {
            values._method = "PUT";
            urlAction = `${url}/${props.id}`;
        }

        const response = await api.post(urlAction, values);

        if (response.data.success === true) {
            const message = props.id ? 'Modifiado correctamente' : 'Guardado correctamente';
            showMessage(message);
            emit("update");
            modalHidden();
            composeModalCompetitionG.value?.hide();
            resetFormState();
        }
        if (response.data.success === false) {
            showMessage('Algo salió mal', 'error');
        }
    } catch (error) {
        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (msg) => (globalError.value = msg)
        );
    }
};

const onCancel = async () => {
    resetFormState();
    modalHidden();
    composeModalCompetitionG.value?.hide();
    emit("cancel");
};

const onLoadData = async () => {
    await settingsGroup.getGroupSettings();

    const response = await api.get(`${url}/${props.id}`);
    if (response.data?.data) {
        const {
            id,
            name,
            tournament_id,
            user_id,
            year,
            professor,
            tournament,
        } = response.data.data;

        selectedUserOptions.value = user_id
            ? [normalizeOption(user_id, professor?.name ?? user_id)]
            : [];
        selectedTournamentOptions.value = tournament_id
            ? [normalizeOption(tournament_id, tournament?.name ?? tournament_id)]
            : [];
        selectedCategoryOptions.value = year
            ? [normalizeOption(year, year)]
            : [];

        const data = {
            id: id,
            name: name,
            tournament_id: findOptionByValue(tournamentOptions.value, tournament_id)?.value ?? String(tournament_id),
            user_id: findOptionByValue(userOptions.value, user_id)?.value ?? String(user_id),
            year: findOptionByValue(categoryOptions.value, year)?.value ?? String(year),
        };

        form.value.resetForm({ values: data });

        composeModalCompetitionG.value.show();
    }
};

watch(
    () => props.id,
    (newValue) => {
        if (newValue !== null) {
            onLoadData();
        }
    }
);

const handleModalShow = () => {
    if (props.id === null) {
        resetFormState();
    }
};

onMounted(() => {
    settingsGroup.getGroupSettings();
    modalElement.value = document.getElementById("composeModalCompetitionG");

    modalElement.value?.addEventListener("show.bs.modal", handleModalShow);

    composeModalCompetitionG.value = new window.bootstrap.Modal(
        modalElement.value,
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    );
});

onBeforeUnmount(() => {
    modalElement.value?.removeEventListener("show.bs.modal", handleModalShow);
});
</script>
