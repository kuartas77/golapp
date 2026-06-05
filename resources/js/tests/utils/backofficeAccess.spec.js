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
})
