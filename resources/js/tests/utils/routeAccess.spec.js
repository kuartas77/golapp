import { describe, expect, it } from 'vitest'

import { canAccessRoute } from '@/utils/routeAccess'

function makeAuth({ roles = [], schoolPermissions = {} } = {}) {
    return {
        roles,
        hasSchoolPermission(permission) {
            return Boolean(schoolPermissions[permission])
        },
    }
}

describe('canAccessRoute', () => {
    it('blocks instructors from routes that require school or super-admin even when the school permission is enabled', () => {
        const route = {
            matched: [
                {
                    meta: {
                        requiresRole: ['super-admin', 'school'],
                        requiresSchoolPermission: ['school.module.inscriptions'],
                    },
                },
            ],
        }

        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.inscriptions': true,
            },
        })

        expect(canAccessRoute(route, auth)).toBe(false)
    })

    it('allows instructors into routes that only require an enabled school permission', () => {
        const route = {
            matched: [
                {
                    meta: {
                        requiresSchoolPermission: ['school.module.matches'],
                    },
                },
            ],
        }

        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.matches': true,
            },
        })

        expect(canAccessRoute(route, auth)).toBe(true)
    })

    it('blocks instructors from the inventory route even when inventory permission is enabled', () => {
        const route = {
            matched: [
                {
                    meta: {
                        requiresRole: ['super-admin', 'school'],
                        requiresSchoolPermission: ['school.module.inventory'],
                    },
                },
            ],
        }

        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.inventory': true,
            },
        })

        expect(canAccessRoute(route, auth)).toBe(false)
    })

    it('allows instructors into methodology when the school permission is enabled', () => {
        const route = {
            matched: [
                {
                    meta: {
                        requiresSchoolPermission: ['school.module.methodology'],
                    },
                },
            ],
        }

        const auth = makeAuth({
            roles: ['instructor'],
            schoolPermissions: {
                'school.module.methodology': true,
            },
        })

        expect(canAccessRoute(route, auth)).toBe(true)
    })
})
