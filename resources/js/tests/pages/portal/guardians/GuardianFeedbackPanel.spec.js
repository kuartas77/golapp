import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import GuardianFeedbackPanel from '@/pages/portal/guardians/components/GuardianFeedbackPanel.vue';

const entries = [
    {
        id: 'competition-8',
        source: 'competition',
        source_label: 'Competencia',
        event_date: '2026-03-10',
        created_at: '2026-03-10T14:00:00Z',
        group_name: 'Sub 14 (2012)',
        tournament_name: 'Torneo Apertura',
        coach_name: 'Laura Técnica',
        match_number: '4',
        position: 'Volante (Ofensivo Central)',
        rival_name: 'Academia Norte',
        score: { team: 3, rival: 3 },
        player_observation: 'Mostró buena lectura de juego.',
        group_observation: 'El grupo sostuvo el plan de juego.',
    },
    {
        id: 'attendance-3-2026-03-11',
        source: 'attendance',
        source_label: 'Asistencia',
        event_date: '2026-03-11',
        created_at: '2026-03-01T12:00:00Z',
        group_name: 'Sub 14',
        observation: 'Llegó con buena disposición.',
    },
];

describe('GuardianFeedbackPanel', () => {
    it('muestra el contexto completo de una competencia y permite seleccionar otra fecha', async () => {
        const wrapper = mount(GuardianFeedbackPanel, {
            props: { entries },
        });

        expect(wrapper.text()).toContain('Torneo Apertura');
        expect(wrapper.text()).toContain('Laura Técnica');
        expect(wrapper.text()).toContain('Volante (Ofensivo Central)');
        expect(wrapper.text()).toContain('3 - 3');
        expect(wrapper.text()).toContain('Mostró buena lectura de juego.');
        expect(wrapper.text()).toContain('El grupo sostuvo el plan de juego.');

        await wrapper.findAll('.guardian-feedback__timeline-item')[1].trigger('click');

        expect(wrapper.text()).toContain('Observación de asistencia');
        expect(wrapper.text()).toContain('Llegó con buena disposición.');
    });

    it('presenta un estado vacío cuando no hay textos de retroalimentación', () => {
        const wrapper = mount(GuardianFeedbackPanel, {
            props: { entries: [] },
        });

        expect(wrapper.text()).toContain('Sin observaciones por ahora');
    });
});
