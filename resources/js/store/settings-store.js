import { defineStore } from "pinia";
import api from "@/utils/axios";

export const useSetting = defineStore('settings-store', {
    persist: true,
    state: () => ({
        all_groups: [],
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
        type_assistance: [],
        type_payments: [],
    }),
    getters: {},
    actions: {
        clearState() {
            this.$reset()
        },
        async getSettings()  {
            const response = await api.get('api/v2/settings/general')
            this.all_groups = response.data.all_t_groups
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
            this.type_assistance = response.data.type_assistance
            this.type_payments = response.data.type_payments
        }
    }

})

export const useSettingGroups = defineStore('settings-groups-store', {
    state: () => ({
        users: [],
        year_active: [],
        schedules: [],
        categories: [],
        tournaments: [],
    }),
    getters: {},
    actions: {
        clearState() {
            this.$reset()
        },
        async getGroupSettings()  {
            const response = await api.get('api/v2/settings/groups')
            this.users = response.data.users.map((i) => ({value: i.id, label: i.name}))
            this.year_active = response.data.year_active
            this.schedules = response.data.schedules.map((i) => ({value: i.id, label: i.name}))
            this.categories = response.data.categories.map((i) => ({value: i.id, label: i.name}))
            this.tournaments = response.data.tournaments.map((i) => ({value: i.id, label: i.name}))
        }
    }

})