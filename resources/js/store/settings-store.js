import { defineStore } from "pinia";
import api from "@/utils/axios";

export const useSetting = defineStore('settings-store', {
    persist: true,
    state: () => ({
        groups: [],
        categories: [],
        genders: [],
        positions: [],
        blood_types: [],
        averages: [],
        dominant_profile: [],
        relationships: [],
        competition_groups: [],
        inscription_years: [],
        document_types: [],
        jornada: [],
        schools: [],
    }),
    getters: {},
    actions: {
        clearState() {
            this.$reset()
        },
        async getSettings()  {
            const response = await api.get('api/v2/settings/general')
            this.groups = response.data.t_groups
            this.categories = response.data.categories
            this.genders = response.data.genders
            this.positions = response.data.positions
            this.blood_types = response.data.blood_types
            this.averages = response.data.averages
            this.dominant_profile = response.data.dominant_profile
            this.relationships = response.data.relationships
            this.competition_groups = response.data.competition_groups
            this.inscription_years = response.data.inscription_years
            this.document_types = response.data.document_types
            this.jornada = response.data.jornada
            this.schools = response.data.schools
        }
    }

})