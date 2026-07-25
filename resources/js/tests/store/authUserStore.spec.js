import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
        post: vi.fn(),
        interceptors: {
            request: {
                use: vi.fn(),
            },
            response: {
                use: vi.fn(),
            },
        },
    },
}));

vi.mock('axios', () => ({
    default: {
        create: vi.fn(() => apiMock),
    },
}));

import { useAuthUser } from '@/store/auth-user';

describe('useAuthUser', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        apiMock.get.mockReset();
        apiMock.post.mockReset();
    });

    it('stores effective school permissions and enabled sports from the user context', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                data: {
                    id: 10,
                    name: 'Club Demo',
                    email: 'demo@example.com',
                    school_id: 5,
                    school_name: 'Club Demo',
                    school_slug: 'club-demo',
                    school_logo: '/img/logo.webp',
                    school_organization_type: 'club',
                    enabled_sports: ['basketball'],
                    roles: ['school'],
                    permissions: ['school.module.training_sessions'],
                    school_permissions: {
                        'school.module.matches': false,
                        'school.module.training_sessions': true,
                    },
                    school_permissions_configured: {
                        'school.module.matches': true,
                        'school.module.training_sessions': true,
                    },
                    system_notify: false,
                },
            },
        });

        const store = useAuthUser();

        await store.getUser();

        expect(store.user.school_organization_type).toBe('club');
        expect(store.user.enabled_sports).toEqual(['basketball']);
        expect(store.enabledSports).toEqual(['basketball']);
        expect(store.hasSchoolPermission('school.module.matches')).toBe(false);
        expect(store.hasSchoolPermission('school.module.training_sessions')).toBe(true);
        expect(store.schoolPermissionsConfigured['school.module.matches']).toBe(true);
    });
});
