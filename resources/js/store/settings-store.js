import { defineStore } from "pinia";
import api from "@/utils/axios";

const toArray = (value) => Array.isArray(value) ? value : []
const mapOptions = (value, mapper) => toArray(value).map(mapper)

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
            const data = response?.data ?? {}

            this.all_groups = toArray(data.all_t_groups)
            this.groups = toArray(data.t_groups)
            this.categories = toArray(data.categories)
            this.genders = toArray(data.genders)
            this.positions = toArray(data.positions)
            this.blood_types = toArray(data.blood_types)
            this.averages = toArray(data.averages)
            this.dominant_profile = toArray(data.dominant_profile)
            this.relationships = toArray(data.relationships)
            this.competition_groups = toArray(data.competition_groups)
            this.inscription_years = mapOptions(data.inscription_years, (i) => ({ value: i.id, label: i.year }))
            this.document_types = toArray(data.document_types)
            this.jornada = toArray(data.jornada)
            this.schools = toArray(data.schools)
            this.type_assistance = toArray(data.type_assistance)
            this.type_payments = toArray(data.type_payments)
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
            const data = response?.data ?? {}

            this.users = mapOptions(data.users, (i) => ({ value: i.id, label: i.name }))
            this.year_active = toArray(data.year_active)
            this.schedules = mapOptions(data.schedules, (i) => ({ value: i.id, label: i.name }))
            this.categories = mapOptions(data.categories, (i) => ({ value: i.id, label: i.name }))
            this.tournaments = mapOptions(data.tournaments, (i) => ({ value: i.id, label: i.name }))
        }
    }

})
