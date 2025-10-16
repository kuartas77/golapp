<template>
    <div class="modal fade" id="composeModalTrainigG" tabindex="-1" role="dialog" aria-labelledby="modalTrainigG"
        aria-hidden="false" aria-modal="true">
        <div class="modal-dialog modal-sm" role="document">
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
                                        <Field name="user_id" v-slot="{ field, handleChange, handleBlur }">
                                            <CustomSelect2 v-binf="field" :options="items" @change="handleChange" @blur="handleBlur"/>
                                        </Field>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year_active" class="form-label">Año de actividad</label><span
                                            class="text-danger">*</span>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <br>
                                        <small class="text-justify">El Año de actividad determina que año se mostrará el grupo, en los meses 10,
                                            11, 12 se pueden crear los grupos del año siguiente.</small>
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
import { getCurrentInstance, useTemplateRef, ref, onMounted, watch } from "vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import api from "@/utils/axios";
import { useSettingGroups } from "@/store/settings-store";

const url = "/api/v2/admin/training_groups";
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

const daysOptions = ref([
    { id: "Lunes", name: "Lunes" },
    { id: "Martes", name: "Martes" },
    { id: "Miércoles", name: "Miércoles" },
    { id: "Jueves", name: "Jueves" },
    { id: "Viernes", name: "Viernes" },
    { id: "Sábado", name: "Sábado" },
    { id: "Domingo", name: "Domingo" },
]);

const form = useTemplateRef("form");
const composeModalTrainigG = ref(null);
const initialData = ref({
    id: null,
    name: null,
    stage: "",
    year_active: null,
    days: [],
    schedules: [],
    user_id: [],
    years: [],
});

const schema = yup.object().shape({
    name: yup.string().required(),
    stage: yup.string(),
    year_active: yup.mixed().required(),
    days: yup
        .array()
        .min(1, "Selecciona al menos un día")
        .max(3, "No puedes seleccionar más de 3 días")
        .required(),
    schedules: yup.array().min(1, "Selecciona al menos un horario").required(),
    user_id: yup.array().min(1, "Selecciona al menos un instructor").required(),
    years: yup
        .array()
        .min(1, "Selecciona al menos una categoría")
        .max(12, "No puedes seleccionar más de 12 categorías")
        .required(),
});

const submit = async (values, actions) => {
    try {
        let formData = {
            days: values.days.map((day) => day.id),
            schedules: values.schedules.map((day) => day.id),
            users_id: values.user_id.map((user) => user.id),
            categories: values.years.map((year) => year.id),
            name: values.name,
            stage: values.stage,
            year_active: values.year_active,
        };

        let urlAction = url
        if (props.id) {
            formData._method = "PUT";
            urlAction = `${url}/${props.id}`;
        }

        const response = await api.post(urlAction, formData);

        if (response.data.success === true) {
            const message = props.id ? 'Modifiado correctamente' : 'Guardado correctamente';
            showMessage(message);
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
        console.log(error)
    } finally {
        emit("update");
        modalHidden();
        composeModalTrainigG.value.hide();
        form.value.resetForm();
    }
};

const onCancel = async () => {
    form.value.resetForm();
    modalHidden();
    composeModalTrainigG.value.hide();
    emit("cancel");
};

const onLoadData = async () => {
    const response = await api.get(`${url}/${props.id}`);

    if (response.data?.data) {
        const {
            id,
            name,
            stage,
            year_active,
            years,
            explode_days,
            explode_schedules,
            instructors,
            category,
        } = response.data.data;

        if (name === "Provisional") {
            showMessage("El grupo Provisional no se puede modificar.", "warning");
            return;
        }

        const data = {
            id: id,
            name: name,
            stage: stage,
            year_active: year_active,
            days: explode_days.map((i) => ({ id: i, name: i })),
            schedules: explode_schedules.map((i) => ({ id: i, name: i })),
            user_id: instructors.map((i) => ({ id: i.id, name: i.name })),
            years: category.map((i) => ({ id: i, name: i })),
        };

        form.value.resetForm();
        form.value.setValues(data);

        composeModalTrainigG.value.show();
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

onMounted(() => {
    settingsGroup.getGroupSettings();

    composeModalTrainigG.value = new window.bootstrap.Modal(
        document.getElementById("composeModalTrainigG"),
        {
            backdrop: "static", // Prevents closing the modal by clicking outside
            keyboard: false, // Disables closing the modal with the escape key
            focus: false, // Focuses the modal when initialized (default is true)
        }
    );
});
</script>