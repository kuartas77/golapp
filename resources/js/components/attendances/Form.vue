<template>
    <div class="form-horizontal form-material">
        <div class="row form-body">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="group_id"
                        >Grupo De Entrenamiento</label
                    >
                    <span class="bar"></span>
                    <select
                        v-model="form.group_id"
                        class="form-control form-control-sm"
                    >
                        <option value="">Seleccione...</option>
                        <option v-for="(group, index) in groups" :key="index" :value="group">{{ group }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="month">Mes</label>
                    <span class="bar"></span>
                    <select
                        v-model="form.month"
                        class="form-control form-control-sm"
                    >
                        <option value="">Seleccione...</option>
                        <option v-for="(month, index) in months" :key="index" :value="index">{{ month }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <button
                    class="btn waves-effect waves-light btn-rounded btn-info mt-4"
                    @click="searchAttendances" :disabled="!isValid"
                >
                    Buscar
                </button>
                <button
                    type="button"
                    class="btn waves-effect waves-light btn-rounded btn-success mt-4"
                    @click="createAttendances" :disabled="!isValid"
                >
                    Crear Asistencias
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import { onMounted, computed } from 'vue'
export default {
    name: "form-attendances",
    props: ['groups', 'months'],
    data() {
        return {
            form: {
                group_id: "",
                month: "",
            },
        };
    },
    methods: {
        searchAttendances(){},
        createAttendances(){}
    },
    computed: {
        isValid(){
            return this.form.group_id != '' && this.form.month != ''
        }
    }
}
</script>