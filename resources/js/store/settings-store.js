import { defineStore } from "pinia";
import api from "@/utils/axios";

const toArray = (value) => Array.isArray(value) ? value : []
const mapOptions = (value, mapper) => toArray(value).map(mapper)
const normalizeOptionList = (value) => {
    if (Array.isArray(value)) {
        return value.map((item, index) => {
            if (item && typeof item === 'object' && !Array.isArray(item)) {
                const optionValue = item.value ?? item.id ?? index
                const optionLabel = item.label ?? item.name ?? optionValue

                return {
                    value: String(optionValue),
                    label: String(optionLabel),
                }
            }

            return {
                value: String(item),
                label: String(item),
            }
        })
    }

    return Object.entries(value ?? {}).map(([optionValue, optionLabel]) => ({
        value: String(optionValue),
        label: String(optionLabel),
    }))
}
const optionListToMap = (value) => normalizeOptionList(value).reduce((accumulator, option) => {
    accumulator[String(option.value)] = option.label
    return accumulator
}, {})

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
    getters: {
        genderOptions: (state) => normalizeOptionList(state.genders),
        bloodTypeOptions: (state) => normalizeOptionList(state.blood_types),
        averageOptions: (state) => normalizeOptionList(state.averages),
        dominantProfileOptions: (state) => normalizeOptionList(state.dominant_profile),
        relationshipOptions: (state) => normalizeOptionList(state.relationships),
        documentTypeOptions: (state) => normalizeOptionList(state.document_types),
        jornadaOptions: (state) => normalizeOptionList(state.jornada),
        assistanceTypeOptions: (state) => normalizeOptionList(state.type_assistance),
        paymentTypeOptions: (state) => normalizeOptionList(state.type_payments),
        paymentTypeLabels: (state) => optionListToMap(state.type_payments),
    },
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
            this.genders = normalizeOptionList(data.genders)
            this.positions = toArray(data.positions)
            this.blood_types = normalizeOptionList(data.blood_types)
            this.averages = normalizeOptionList(data.averages)
            this.dominant_profile = normalizeOptionList(data.dominant_profile)
            this.relationships = normalizeOptionList(data.relationships)
            this.competition_groups = toArray(data.competition_groups)
            this.inscription_years = mapOptions(data.inscription_years, (i) => ({ value: i.id, label: i.year }))
            this.document_types = normalizeOptionList(data.document_types)
            this.jornada = normalizeOptionList(data.jornada)
            this.schools = toArray(data.schools)
            this.type_assistance = normalizeOptionList(data.type_assistance)
            this.type_payments = normalizeOptionList(data.type_payments)
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

            this.users = normalizeOptionList(data.users)
            this.year_active = normalizeOptionList(data.year_active).map((option) => option.value)
            this.schedules = normalizeOptionList(data.schedules)
            this.categories = normalizeOptionList(data.categories)
            this.tournaments = normalizeOptionList(data.tournaments)
        }
    }

})
