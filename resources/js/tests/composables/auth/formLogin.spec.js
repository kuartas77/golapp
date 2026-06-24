import { describe, expect, it } from 'vitest'
import { resolvePostLoginRedirect } from '@/composables/auth/formLogin'

const router = {
    resolve(redirect) {
        const path = redirect.split('?')[0]

        if (path === '/login') {
            return {
                fullPath: '/ingreso',
                matched: [{ meta: {} }, { meta: { guest: true } }],
            }
        }

        if (path === '/ingreso') {
            return {
                fullPath: redirect,
                matched: [{ meta: { guest: true } }],
            }
        }

        if (path === '/inicio') {
            return {
                fullPath: redirect,
                matched: [{ meta: { requiresAuth: true } }],
            }
        }

        return { fullPath: redirect, matched: [] }
    },
}

describe('resolvePostLoginRedirect', () => {
    it('uses the dashboard when the old login URL is stored as redirect', () => {
        expect(resolvePostLoginRedirect(router, '/login')).toBe('/inicio')
    })

    it('does not return to another guest authentication screen', () => {
        expect(resolvePostLoginRedirect(router, '/ingreso?email=user@example.com')).toBe('/inicio')
    })

    it('preserves a valid authenticated destination', () => {
        expect(resolvePostLoginRedirect(router, '/inicio?tab=resume')).toBe('/inicio?tab=resume')
    })

    it('rejects unknown or external destinations', () => {
        expect(resolvePostLoginRedirect(router, '/ruta-inexistente')).toBe('/inicio')
        expect(resolvePostLoginRedirect(router, 'https://example.com')).toBe('/inicio')
        expect(resolvePostLoginRedirect(router, '//example.com')).toBe('/inicio')
    })
})
