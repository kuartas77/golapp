import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import { describe, expect, it } from 'vitest'

import SoccerFieldDiagramEditor from '@/pages/methodology/SoccerFieldDiagramEditor.vue'

function latestModelValue(wrapper) {
    return wrapper.emitted('update:modelValue').at(-1)[0]
}

describe('SoccerFieldDiagramEditor', () => {
    it('adds, moves, edits and deletes field items', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((button) => button.text().includes('Jugador')).trigger('click')
        let value = latestModelValue(wrapper)

        expect(value).toHaveLength(1)
        expect(value[0]).toEqual(expect.objectContaining({
            type: 'player',
            x: 50,
            y: 32,
        }))

        await wrapper.setProps({ modelValue: value })

        const svg = wrapper.find('svg').element
        svg.createSVGPoint = () => ({
            x: 0,
            y: 0,
            matrixTransform() {
                return { x: 76, y: 22 }
            },
        })
        svg.getScreenCTM = () => ({
            inverse() {
                return {}
            },
        })

        wrapper.find('.field-item').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 1 }))
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointermove', { clientX: 76, clientY: 22 }))
        await nextTick()
        value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            x: 76,
            y: 22,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Eliminar')).trigger('click')

        expect(latestModelValue(wrapper)).toEqual([])
    })

    it('adds x marks and rotates arrows', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((button) => button.text().trim() === 'X').trigger('click')
        let value = latestModelValue(wrapper)

        expect(value).toHaveLength(1)
        expect(value[0]).toEqual(expect.objectContaining({
            type: 'xmark',
            x: 50,
            y: 32,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Flecha')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[1]).toEqual(expect.objectContaining({
            type: 'arrow',
            rotation: 0,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Derecha')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[1]).toEqual(expect.objectContaining({
            rotation: 45,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Izquierda')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[1]).toEqual(expect.objectContaining({
            rotation: 0,
        }))
    })
})

function makePointerEvent(name, values = {}) {
    const event = new Event(name, { bubbles: true, cancelable: true })

    Object.entries(values).forEach(([key, value]) => {
        Object.defineProperty(event, key, { value })
    })

    return event
}
