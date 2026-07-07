import { describe, expect, it } from 'vitest'

import { backofficeAccessRequirements, hasBackofficeAccess } from '@/composables/useBackofficeAccess'

function makeAuth({ roles = [], schoolPermissions = {} } = {}) {
    return {
        hasRole(role) {
            return roles.includes(role)
        },
        hasSchoolPermission(permission) {
            return Boolean(schoolPermissions[permission])
        },
    }
}

describe('hasBackofficeAccess', () => {
    it('allows school users with inventory permission to see the inventory menu entry', () => {
        const auth = makeAuth({
            roles: ['school'],
            schoolPermissions: {
                'school.module.inventory': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.inventory)).toBe(true)
    })

    it('hides inventory access from instructors even when the school permission is enabled', () => {
        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.inventory': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.inventory)).toBe(false)
    })

    it('allows instructors with methodology permission to see the methodology menu entry', () => {
        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.methodology': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.methodology)).toBe(true)
    })

    it('allows instructors with matches permission to access both statistics modules without player CRUD', () => {
        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.matches': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.playerStats)).toBe(true)
        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.competitionStats)).toBe(true)
        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.players)).toBe(false)
    })

    it('does not grant player stats from the players permission alone', () => {
        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.players': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.playerStats)).toBe(false)
    })

    it('allows instructors to access document planning but not club documents', () => {
        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.document_planning': true,
                'school.module.club_documents': true,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.documentPlanning)).toBe(true)
        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.clubDocuments)).toBe(false)
    })

    it('keeps both document modules behind independent school permissions', () => {
        const auth = makeAuth({
            roles: ['school'],
            schoolPermissions: {
                'school.module.document_planning': true,
                'school.module.club_documents': false,
            },
        })

        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.documentPlanning)).toBe(true)
        expect(hasBackofficeAccess(auth, backofficeAccessRequirements.clubDocuments)).toBe(false)
    })
})
