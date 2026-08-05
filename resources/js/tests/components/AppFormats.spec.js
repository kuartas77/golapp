import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import AppDate from '@/components/general/AppDate.vue'
import AppMoney from '@/components/general/AppMoney.vue'
import AppStatus from '@/components/general/AppStatus.vue'
import {
    formatAppDate,
    formatAppMoney,
    renderAppStatus,
    resolveAppStatus,
} from '@/utils/appFormatters'

describe('shared application formats', () => {
    it('renders valid dates semantically and uses a readable fallback for invalid values', () => {
        const validDate = mount(AppDate, { props: { value: '2026-08-05' } })

        expect(validDate.get('time').attributes('datetime')).toBe('2026-08-05')
        expect(validDate.text()).toBe('05/08/2026')
        expect(formatAppDate('not-a-date')).toBe('—')

        const invalidDate = mount(AppDate, { props: { value: 'not-a-date', fallback: 'N/D' } })
        expect(invalidDate.find('time').exists()).toBe(false)
        expect(invalidDate.text()).toBe('N/D')
    })

    it('uses the same Colombian money contract in the utility and component', () => {
        const wrapper = mount(AppMoney, { props: { value: 125000 } })

        expect(wrapper.text()).toBe(formatAppMoney(125000))
        expect(wrapper.text()).toContain('125.000')
        expect(formatAppMoney(null)).toBe('—')
        expect(formatAppMoney('invalid')).toBe('—')
        expect(formatAppMoney(0)).not.toBe('—')
    })

    it('centralizes status vocabulary for Vue and DataTable surfaces', () => {
        const wrapper = mount(AppStatus, {
            props: { value: 'paid', context: 'invoice' },
        })

        expect(wrapper.text()).toBe('Pagada')
        expect(wrapper.classes()).toContain('badge-success')
        expect(resolveAppStatus(false, { context: 'invoice-item' })).toEqual({
            label: 'Pendiente',
            variant: 'warning',
        })
        expect(renderAppStatus('due', { context: 'custom-charge' }))
            .toBe('<span class="badge badge-danger">Debe</span>')
        expect(renderAppStatus('paid', { context: 'invoice', type: 'filter' })).toBe('Pagada')
    })

    it('escapes explicit status labels before returning DataTable HTML', () => {
        const rendered = renderAppStatus('custom', {
            label: '<img src=x onerror=alert(1)>',
            variant: 'danger',
        })

        expect(rendered).not.toContain('<img')
        expect(rendered).toContain('&lt;img')
    })
})
