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
        expect(wrapper.find('.arrow-line').attributes('x1')).toBe('-4')
        expect(wrapper.find('.arrow-line').element.parentElement.getAttribute('transform'))
            .toContain('translate(50 32) rotate(0)')

        await wrapper.findAll('button').find((button) => button.text().includes('+15°')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[1]).toEqual(expect.objectContaining({
            rotation: 15,
        }))

        await wrapper.setProps({ modelValue: value })
        expect(wrapper.find('.arrow-line').element.parentElement.getAttribute('transform'))
            .toContain('translate(50 32) rotate(15)')

        await wrapper.findAll('button').find((button) => button.text().includes('-15°')).trigger('click')
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

        expect(wrapper.findAll('.field-color-option')).toHaveLength(4)
        await wrapper.find('input[aria-label="Color Rojo"]').trigger('change')
        await wrapper.findAll('button').find((button) => button.text().includes('Ficha')).trigger('click')
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
        await wrapper.find('.field-text-input input').setValue('9')
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

        await wrapper.findAll('button').find((button) => button.text().includes('+15°')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[3]).toEqual(expect.objectContaining({
            type: 'cross',
            rotation: 15,
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Eliminar')).trigger('click')

        expect(latestModelValue(wrapper).map((item) => item.type)).toEqual(['pass', 'dribble', 'off_ball_run'])
    })

    it('adds positioned training equipment and rotates it with shift drag', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((button) => button.text().includes('Valla')).trigger('click')
        let value = latestModelValue(wrapper)
        await wrapper.setProps({ modelValue: value })

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'agility_hurdle',
            rotation: 0,
        }))
        expect(wrapper.find('.hurdle-frame').element.parentElement.getAttribute('transform'))
            .toContain('translate(50 32)')

        await wrapper.find('input[aria-label="Color Verde"]').trigger('change')
        value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'agility_hurdle',
            color: 'green',
            rotation: 0,
        }))

        await wrapper.setProps({ modelValue: value })
        wrapper.find('.field-item').element.dispatchEvent(makePointerEvent('pointerdown', {
            pointerId: 2,
            clientX: 10,
            shiftKey: true,
            buttons: 1,
        }))
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointermove', {
            pointerId: 2,
            clientX: 60,
            buttons: 1,
        }))
        await nextTick()
        value = latestModelValue(wrapper)

        expect(value[0].rotation).toBe(40)

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((button) => button.text().includes('Bastón')).trigger('click')
        value = latestModelValue(wrapper)

        expect(value[1]).toEqual(expect.objectContaining({
            type: 'stick',
            rotation: 0,
        }))
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

        await wrapper.find('input[aria-label="Color Amarillo"]').trigger('change')
        await wrapper.findAll('button').find((button) => button.text().includes('Lápiz')).trigger('click')
        wrapper.find('svg').element.dispatchEvent(makePointerEvent('pointerdown', { pointerId: 1, clientX: 12, clientY: 14 }))
        await nextTick()
        let value = latestModelValue(wrapper)

        expect(value[0]).toEqual(expect.objectContaining({
            type: 'freehand',
            color: 'yellow',
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

    it('adds team players with auto-incrementing numbers and goalkeeper', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((btn) => btn.text().includes('Equipo A')).trigger('click')
        let value = latestModelValue(wrapper)
        expect(value).toHaveLength(1)
        expect(value[0]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'blue',
            label: '1',
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((btn) => btn.text().includes('Equipo A')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(2)
        expect(value[1]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'blue',
            label: '2',
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((btn) => btn.text().includes('Equipo B')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(3)
        expect(value[2]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'red',
            label: '1',
        }))

        await wrapper.setProps({ modelValue: value })
        await wrapper.findAll('button').find((btn) => btn.text().includes('Portero')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(4)
        expect(value[3]).toEqual(expect.objectContaining({
            type: 'player_token',
            color: 'yellow',
            label: '1',
        }))
    })

    it('duplicates a selected item and supports undo/redo', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        await wrapper.findAll('button').find((btn) => btn.text().includes('Balón')).trigger('click')
        let value = latestModelValue(wrapper)
        expect(value).toHaveLength(1)
        await wrapper.setProps({ modelValue: value })

        await wrapper.findAll('button').find((btn) => btn.text().includes('Duplicar')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(2)
        expect(value[1].type).toBe('ball')
        await wrapper.setProps({ modelValue: value })

        // Deshacer
        await wrapper.findAll('button').find((btn) => btn.text().includes('Deshacer')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(1)
        await wrapper.setProps({ modelValue: value })

        // Rehacer
        await wrapper.findAll('button').find((btn) => btn.text().includes('Rehacer')).trigger('click')
        value = latestModelValue(wrapper)
        expect(value).toHaveLength(2)
    })

    it('renders directional tip handle and regulation field pitch markings', async () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [],
            },
        })

        // Regulation markings
        expect(wrapper.find('#fieldGrassStripes').exists()).toBe(true)
        expect(wrapper.findAll('.goal-post')).toHaveLength(2)
        expect(wrapper.findAll('.goal-net')).toHaveLength(2)

        // Directional item has only 1 handle (tip handle) and no separate top rotation pin
        await wrapper.findAll('button').find((btn) => btn.text().includes('Pase')).trigger('click')
        let value = latestModelValue(wrapper)
        await wrapper.setProps({ modelValue: value })

        expect(wrapper.find('.tip-handle-control').exists()).toBe(true)
        expect(wrapper.find('.tip-handle-circle').exists()).toBe(true)
        expect(wrapper.find('.rotation-control').exists()).toBe(false)

        // Training equipment has only 1 handle (top rotation pin) and no tip handle
        await wrapper.findAll('button').find((btn) => btn.text().includes('Cono')).trigger('click')
        value = latestModelValue(wrapper)
        await wrapper.setProps({ modelValue: value })

        expect(wrapper.find('.rotation-control').exists()).toBe(true)
        expect(wrapper.find('.tip-handle-control').exists()).toBe(false)
    })

    it('renders xmark with normal dimensions even when loaded with string coordinates', () => {
        const wrapper = mount(SoccerFieldDiagramEditor, {
            props: {
                modelValue: [
                    { type: 'xmark', x: '50', y: '32', color: 'blue' },
                ],
            },
        })

        const xmarkGroup = wrapper.find('.field-item g')
        expect(xmarkGroup.exists()).toBe(true)
        expect(xmarkGroup.attributes('transform')).toBe('translate(50 32)')

        const lines = wrapper.findAll('.xmark-line')
        expect(lines).toHaveLength(2)
        expect(lines[0].attributes('x1')).toBe('-1.3')
        expect(lines[0].attributes('x2')).toBe('1.3')
    })
})

function makePointerEvent(name, values = {}) {
    const event = new Event(name, { bubbles: true, cancelable: true })

    Object.entries(values).forEach(([key, value]) => {
        Object.defineProperty(event, key, { value })
    })

    return event
}
