import { describe, expect, it } from 'vitest'
import { ref } from 'vue'

import { inventoryTutorial } from '@/tutorials/operations'

describe('inventoryTutorial', () => {
    it('uses the product journey on the inventory route', () => {
        const steps = inventoryTutorial.getSteps({ activeTab: ref('products') })

        expect(steps.map((step) => step.id)).toEqual([
            'inventory-actions',
            'inventory-tabs',
            'inventory-products-table',
        ])
    })

    it('uses the movement journey on the movements route', () => {
        const steps = inventoryTutorial.getSteps({ activeTab: ref('movements') })

        expect(steps.map((step) => step.id)).toEqual([
            'inventory-tabs',
            'inventory-movements-table',
        ])
    })
})
