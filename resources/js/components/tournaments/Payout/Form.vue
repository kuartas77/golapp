<template>
    <div class="form-horizontal form-material">
        <div class="row form-body">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="tournament_id">Torneos</label>
                    <span class="bar"></span>
                    <select
                        v-model="form.tournament_id"
                        class="form-control form-control-sm"
                    >
                        <option value="">Seleccione un torneo...</option>
                        <option v-for="tournament in tournaments" :key="tournament.id" :value="tournament.id">{{ tournament.text }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="competition_group_id"
                        >Grupos De Competencia</label
                    >
                    <span class="bar"></span>
                    <select
                        v-model="form.competition_group_id"
                        class="form-control form-control-sm"
                    >
                        <option value="">Seleccione un torneo...</option>
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
                    />
                </div>
            </div>

            <div class="col-md-3">
                <button
                    class="btn waves-effect waves-light btn-rounded btn-info mt-4"
                    @click="search" :disabled="valid"
                >
                    Buscar
                </button>
                <button
                    type="button"
                    class="btn waves-effect waves-light btn-rounded btn-success mt-4"
                    @click="createTournamentPay" :disabled="valid"
                >
                    Crear Pagos
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted } from '@vue/runtime-core'
import usePayouts from '../../../composables/tournament_payouts'
export default {
    name: "form-payout",
    emits: ['search', 'create'],
    setup(){
        const {groups, tournaments, loadGroups, loadTournaments} = usePayouts()

        onMounted(loadTournaments)

        return {
            tournaments,
            groups,
            loadGroups,
            loadTournaments
        }
    },
    data() {
        return {
            form: {
                tournament_id: "",
                competition_group_id: "",
                unique_code: "",
            },
        };
    },
    watch:{
        'form.tournament_id'(newVal){
            if(newVal == ''){
                this.groups = []
                this.form.competition_group_id = ""
                return
            } 
            this.loadGroups(newVal)
        }
    },
    computed:{
        valid(){
            return !(this.form.tournament_id !== '' && this.form.competition_group_id !== '')
        }
    },
    mounted(){
        this.loadTournaments()
    }
};
</script>
