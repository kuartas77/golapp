import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { apiMock } = vi.hoisted(() => ({
    apiMock: {
        get: vi.fn(),
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

import { useSettingGroups } from '@/store/settings-store';

describe('useSettingGroups', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        apiMock.get.mockReset();
    });

    it('normalizes year_active and option ids from the groups settings payload', async () => {
        apiMock.get.mockResolvedValue({
            data: {
                users: [{ id: 15, name: 'Profesor Demo' }],
                year_active: { 2026: '2026' },
                schedules: [{ id: '08:00 AM', name: '08:00 AM' }],
                categories: [{ id: 'SUB-12', name: 'SUB-12' }],
                tournaments: [{ id: 7, name: 'Liga Escolar' }],
            },
        });

        const store = useSettingGroups();

        await store.getGroupSettings();

        expect(store.users).toEqual([{ value: '15', label: 'Profesor Demo' }]);
        expect(store.year_active).toEqual(['2026']);
        expect(store.schedules).toEqual([{ value: '08:00 AM', label: '08:00 AM' }]);
        expect(store.categories).toEqual([{ value: 'SUB-12', label: 'SUB-12' }]);
        expect(store.tournaments).toEqual([{ value: '7', label: 'Liga Escolar' }]);
    });
});
