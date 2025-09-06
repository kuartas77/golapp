<template>
    <div class="form-horizontal form-material">
        <div class="row form-body">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="groups">Grupo De Entrenamiento</label>
                    <span class="bar"></span>
                    <select
                        v-model="form.training_group_id"
                        class="form-control form-control-sm"
                        id="groups"
                    >
                        <option value="">Selecciona...</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.text }}</option>
                    </select>

                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="unique_code">Código Único</label>
                    <span class="bar"></span>
                    <input
                        type="text"
                        v-model="form.unique_code"
                        class="form-control form-control-sm"
                        placeholder="Ej: 20190000"
                        id="unique_code"
                    />
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="categories"
                        >Categoría</label
                    >
                    <span class="bar"></span>
                    <select
                        v-model="form.category"
                        class="form-control form-control-sm"
                        id="categories"
                    >
                        <option value="">Selecciona...</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.text }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <button
                    class="btn waves-effect waves-light btn-rounded btn-info mt-4"
                    @click="searchPays" :disabled="valid"
                >
                    Buscar
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted } from '@vue/runtime-core'
import useMonthlyPayments from '@/composables/monthly_payments'
export default {
    name: "form-monthly-payout",
    emits: ['search'],
    setup(){
        const {
            groups,
            categories,
            getGroupsCategories
        } = useMonthlyPayments()

        onMounted(getGroupsCategories)

        return {
            groups,
            categories
        }
    },
    data() {
        return {
            form: {
                training_group_id: "",
                category: "",
                unique_code: "",
            },
        };
    },
    methods:{
        searchPays(){
            this.$emit('search', this.form)
        },
    },
    computed:{
        valid(){
            return !(this.form.training_group_id || this.form.category != '' || this.form.unique_code != '')
        }
    }
};
</script>