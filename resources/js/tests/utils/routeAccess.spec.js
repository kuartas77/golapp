import { describe, expect, it } from 'vitest'

import { canAccessRoute } from '@/utils/routeAccess'

function makeAuth({ roles = [], schoolPermissions = {}, viewerModules = [], electronicInvoicingEnabled = false } = {}) {
    return {
        roles,
        user: {
            electronic_invoicing_enabled: electronicInvoicingEnabled,
        },
        hasSchoolPermission(permission) {
            return Boolean(schoolPermissions[permission])
        },
        canViewSchoolModule(permission) {
            return Boolean(schoolPermissions[permission])
                && (!roles.includes('viewer') || viewerModules.includes(permission))
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

    it('requires a school role and the school outings permission for outings routes', () => {
        const route = {
            matched: [
                {
                    meta: {
                        requiresRole: ['super-admin', 'school'],
                        requiresSchoolPermission: ['school.module.school_outings'],
                    },
                },
            ],
        }

        expect(canAccessRoute(route, makeAuth({
            roles: ['school'],
            schoolPermissions: { 'school.module.school_outings': true },
        }))).toBe(true)

        expect(canAccessRoute(route, makeAuth({
            roles: ['school'],
            schoolPermissions: { 'school.module.school_outings': false },
        }))).toBe(false)

        expect(canAccessRoute(route, makeAuth({
            roles: ['instructor'],
            schoolPermissions: { 'school.module.school_outings': true },
        }))).toBe(false)
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

    it('protects invoice numbering by the school electronic invoicing mode', () => {
        const route = {
            matched: [{
                meta: {
                    requiresRole: ['super-admin', 'school'],
                    requiresSchoolPermission: ['school.module.billing'],
                    requiresElectronicInvoicing: true,
                },
            }],
        }
        const permission = { 'school.module.billing': true }

        expect(canAccessRoute(route, makeAuth({
            roles: ['school'],
            schoolPermissions: permission,
            electronicInvoicingEnabled: true,
        }))).toBe(true)
        expect(canAccessRoute(route, makeAuth({
            roles: ['school'],
            schoolPermissions: permission,
        }))).toBe(false)
        expect(canAccessRoute(route, makeAuth({
            roles: ['super-admin'],
            schoolPermissions: permission,
        }))).toBe(true)
    })

    it('allows the assistant into enabled financial routes but not administrative or sports children', () => {
        const auth = makeAuth({
            roles: ['assistant'],
            schoolPermissions: {
                'school.module.reports': true,
                'school.module.billing': true,
            },
        })
        const financialRoute = {
            matched: [{ meta: {
                requiresRole: ['super-admin', 'school', 'assistant'],
                requiresSchoolPermission: ['school.module.reports'],
            } }],
        }
        const billingCreateRoute = {
            matched: [{ meta: {
                requiresRole: ['super-admin', 'school', 'assistant'],
                requiresSchoolPermission: ['school.module.billing'],
            } }],
        }
        const sportsRoute = {
            matched: [
                financialRoute.matched[0],
                { meta: { requiresRole: ['super-admin', 'school'] } },
            ],
        }

        expect(canAccessRoute(financialRoute, auth)).toBe(true)
        expect(canAccessRoute(billingCreateRoute, auth)).toBe(true)
        expect(canAccessRoute(sportsRoute, auth)).toBe(false)
        expect(canAccessRoute(financialRoute, makeAuth({ roles: ['assistant'] }))).toBe(false)
    })

    it('requires every assigned and school-enabled module for viewer composite routes', () => {
        const route = {
            matched: [{ meta: {
                requiresRole: ['super-admin', 'school'],
                requiresSchoolPermissionAll: ['school.module.reports', 'school.module.payments'],
            } }],
        }
        const schoolPermissions = {
            'school.module.reports': true,
            'school.module.payments': true,
        }

        expect(canAccessRoute(route, makeAuth({
            roles: ['viewer'],
            schoolPermissions,
            viewerModules: ['school.module.reports', 'school.module.payments'],
        }))).toBe(true)

        expect(canAccessRoute(route, makeAuth({
            roles: ['viewer'],
            schoolPermissions,
            viewerModules: ['school.module.reports'],
        }))).toBe(false)
    })

    it('does not let the viewer inherit a mutation-only child route from a readable parent', () => {
        const route = {
            matched: [
                { meta: {
                    requiresRole: ['super-admin', 'school', 'viewer'],
                    requiresSchoolPermission: ['school.module.billing'],
                } },
                { meta: { requiresRole: ['super-admin', 'school'] } },
            ],
        }

        expect(canAccessRoute(route, makeAuth({
            roles: ['viewer'],
            schoolPermissions: { 'school.module.billing': true },
            viewerModules: ['school.module.billing'],
        }))).toBe(false)
    })
})
