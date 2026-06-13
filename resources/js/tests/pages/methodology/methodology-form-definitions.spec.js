import { describe, expect, it } from 'vitest'

import {
    METHODOLOGY_TYPES,
    createBlankDiagrams,
    createBlankFields,
    getTabByType,
    methodologyTabs,
} from '@/pages/methodology/methodology-form-definitions'

describe('methodology form definitions', () => {
    it('defines the four methodology tabs', () => {
        expect(methodologyTabs.map((tab) => tab.type)).toEqual([
            METHODOLOGY_TYPES.planning,
            METHODOLOGY_TYPES.characterizationSheet,
            METHODOLOGY_TYPES.monthlyReport,
            METHODOLOGY_TYPES.categoryMonthlyReport,
        ])
    })

    it('creates planning fields and diagram buckets for payloads', () => {
        expect(createBlankFields(METHODOLOGY_TYPES.planning)).toEqual(expect.objectContaining({
            objective: '',
            initial_phase_time: '',
            central_phase_three_time: '',
            final_phase_time: '',
            material: '',
            observations: '',
        }))

        expect(createBlankDiagrams()).toEqual({
            initial_phase: [],
            central_phase_one: [],
            central_phase_two: [],
            central_phase_three: [],
        })

        expect(getTabByType('unknown').type).toBe(METHODOLOGY_TYPES.planning)
    })

    it('creates characterization fields that match the printed format', () => {
        expect(createBlankFields(METHODOLOGY_TYPES.characterizationSheet)).toEqual(expect.objectContaining({
            category: '',
            year_semester: '',
            sport_objectives: '',
            constitutive_values: '',
            tactical_schemes: '',
            internal_rules: '',
            medical_prescription_player_1_name: '',
            projection_player_1_qualities: '',
        }))
    })

    it('creates category monthly report fields that match the printed format', () => {
        expect(createBlankFields(METHODOLOGY_TYPES.categoryMonthlyReport)).toEqual(expect.objectContaining({
            coach: '',
            category: '',
            report_month: '',
            monthly_objectives_description: '',
            monthly_achievements_description: '',
            monthly_difficulties_description: '',
            sport_values_description: '',
            specific_player_news_description: '',
            player_follow_up_description: '',
        }))
        expect(createBlankFields(METHODOLOGY_TYPES.categoryMonthlyReport)).not.toHaveProperty('signature')
        expect(createBlankFields(METHODOLOGY_TYPES.categoryMonthlyReport)).not.toHaveProperty('signer_name')
        expect(createBlankFields(METHODOLOGY_TYPES.categoryMonthlyReport)).not.toHaveProperty('signer_document')
    })

    it('creates monthly report fields that match the printed format', () => {
        expect(createBlankFields(METHODOLOGY_TYPES.monthlyReport)).toEqual(expect.objectContaining({
            coach: '',
            category: '',
            report_month: '',
            coach_obligation_1_activity: '',
            coach_obligation_1_support: '',
            coach_obligation_7_activity: '',
            coach_obligation_7_support: '',
        }))
        expect(createBlankFields(METHODOLOGY_TYPES.monthlyReport)).not.toHaveProperty('signature')
        expect(createBlankFields(METHODOLOGY_TYPES.monthlyReport)).not.toHaveProperty('signer_name')
        expect(createBlankFields(METHODOLOGY_TYPES.monthlyReport)).not.toHaveProperty('signer_document')
    })
})
