import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import { describe, expect, it } from 'vitest'

import SoccerFieldDiagramEditor from '@/pages/methodology/SoccerFieldDiagramEditor.vue'

function latestModelValue(wrapper) {
    return wrapper.emitted('update:modelValue').at(-1)[0]
}

describe('SoccerFieldDiagramEditor', () => {
    it('supports a compact size variant', () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
                size: 'compact',
            },
        })

        expect(wrapper.find('.field-editor').classes()).toContain('field-editor--compact')
    })

    it('switches to image mode and shows an image preview', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
                visualMode: 'diagram',
                imageUrl: '/img/dynamic/school/methodology/phase.jpg',
            },
        })

        await wrapper.findAll('button').find((button) => button.text().includes('Imagen')).trigger('click')

        expect(wrapper.emitted('update:visualMode').at(-1)[0]).toBe('image')

        await wrapper.setProps({ visualMode: 'image' })

        expect(wrapper.find('svg').exists()).toBe(false)
        expect(wrapper.find('img.profile-preview').attributes('src')).toBe('/img/dynamic/school/methodology/phase.jpg')
    })

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

    it('adds editable player tokens with colors', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((button) => button.text().includes('Ficha roja')).trigger('click')
        let value = latestModelValue(wrapper)

        expect(value).toHaveLength(1)
        expect(value[0]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'red',
            label: '1',
            x: 50,
            y: 32,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.find('input').setValue('9')
        value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'red',
            label: '9',
        }))
    })

    it('adds tactical symbols and rotates directional items', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        for (const label of ['Pase', 'Conducción', 'Recorrido', 'Centro']) {
            await wrapper.findAll('button').find((button) => button.text().includes(label)).trigger('click')
            await wrapper.setProps({ modelValue: latestModelValue(wrapper) })
        }

        let value = latestModelValue(wrapper)

        expect(value.map((item) => item.type)).toEqual(['pass', 'dribble', 'off_ball_run', 'cross'])
        expect(value.every((item) => item.rotation === 0)).toBe(true)

        await wrapper.findAll('button').find((button) => button.text().includes('Derecha')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[3]).toEqual(expect.objectContaining({
            type: 'cross',
            rotation: 45,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Eliminar')).trigger('click')

        expect(latestModelValue(wrapper).map((item) => item.type)).toEqual(['pass', 'dribble', 'off_ball_run'])
    })

    it('moves only the selected item when loaded diagram items do not have ids', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [
                    { type: 'arrow', x: 26.66, y: 10.17 },
                    { type: 'pass', x: 30.57, y: 31.99 },
                ],
            },
        })

        const svg = wrapper.find('svg').element
        svg.createSVGPoint = () => ({
            x: 0,
            y: 0,
            matrixTransform() {
                return { x: 70, y: 21 }
            },
        })
        svg.getScreenCTM = () => ({
            inverse() {
                return {}
            },
        })

        wrapper.findAll('.field-item')[1].element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 1 }))
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointermove', { clientX: 70, clientY: 21 }))
        await nextTick()

        expect(latestModelValue(wrapper)).toEqual([
            { type: 'arrow', x: 26.66, y: 10.17 },
            { type: 'pass', x: 70, y: 21 },
        ])
    })

    it('draws freehand strokes and erases only pencil strokes', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })
        const svg = wrapper.find('svg').element
        let projectedPoint = { x: 12, y: 14 }
        svg.createSVGPoint = () => ({
            x: 0,
            y: 0,
            matrixTransform() {
                return projectedPoint
            },
        })
        svg.getScreenCTM = () => ({
            inverse() {
                return {}
            },
        })
        svg.setPointerCapture = () => {}

        await wrapper.findAll('button').find((button) => button.text().includes('Lápiz')).trigger('click')
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 1, clientX: 12, clientY: 14 }))
        await nextTick()
        let value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'freehand',
            points: [{ x: 12, y: 14 }],
        }))

        await wrapper.setProps({ modelValue: value })
        projectedPoint = { x: 18, y: 20 }
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointermove', { pointerId: 1, clientX: 18, clientY: 20 }))
        await nextTick()
        value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'freehand',
            points: [{ x: 12, y: 14 }, { x: 18, y: 20 }],
        }))

        await wrapper.setProps({
            modelValue: [
                value[0],
                { id: 'player-one', type: 'player', x: 18, y: 20 },
            ],
        })
        await wrapper.findAll('button').find((button) => button.text().includes('Borrador')).trigger('click')
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 2, clientX: 18, clientY: 20 }))
        await nextTick()

        expect(latestModelValue(wrapper)).toEqual([
            { id: 'player-one', type: 'player', x: 18, y: 20 },
        ])
    })

    it('allows a new pencil stroke after a long stroke loses the pointer release', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })
        const svg = wrapper.find('svg').element
        let projectedPoint = { x: 10, y: 12 }
        svg.createSVGPoint = () => ({
            x: 0,
            y: 0,
            matrixTransform() {
                return projectedPoint
            },
        })
        svg.getScreenCTM = () => ({
            inverse() {
                return {}
            },
        })
        svg.setPointerCapture = () => {}

        await wrapper.findAll('button').find((button) => button.text().includes('Lápiz')).trigger('click')
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 1, buttons: 1 }))
        await nextTick()
        let value = latestModelValue(wrapper)

        await wrapper.setProps({ modelValue: value })
        projectedPoint = { x: 90, y: 55 }
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointermove', { pointerId: 1, buttons: 0 }))
        await nextTick()

        expect(latestModelValue(wrapper)).toEqual(value)

        projectedPoint = { x: 22, y: 24 }
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 2, buttons: 1 }))
        await nextTick()
        value = latestModelValue(wrapper)

        expect(value).toHaveLength(2)
        expect(value.map((item) => item.type)).toEqual(['freehand', 'freehand'])
        expect(value[1].points).toEqual([{ x: 22, y: 24 }])
        expect(wrapper.find('.field-item--freehand').exists()).toBe(true)
    })
})

function makePointerEvent(name, values = {}) {
    const event = new Event(name, { bubbles: true, cancelable: true })

    Object.entries(values).forEach(([key, value]) => {
        Object.defineProperty(event, key, { value })
    })

    return event
}
