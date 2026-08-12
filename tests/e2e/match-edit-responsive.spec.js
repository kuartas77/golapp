import { expect, test } from '@playwright/test'
import AxeBuilder from '@axe-core/playwright'

const match = {
    id: 33,
    competition_group: {
        id: 9,
        name: 'Sub 12 A',
        tournament: { id: 4, name: 'Liga E2E' },
        professor: { id: 7, name: 'Directora Técnica' },
        url_format_match: '/export/matches/33/format',
    },
    place: 'Cancha principal',
    date: '2026-08-10',
    hour: '09:30 AM',
    num_match: 8,
    rival_name: 'Rival E2E',
    status: 'scheduled',
    final_score: { soccer: 2, rival: 1 },
    general_concept: 'Partido de prueba',
    skills_controls: [{
        id: 71,
        game_id: 33,
        inscription_id: 21,
        assistance: 1,
        titular: 1,
        played_approx: 60,
        position: 'Defensa (Central)',
        goals: 1,
        goal_assists: 0,
        goal_saves: 0,
        yellow_cards: 0,
        red_cards: 0,
        qualification: 4,
        observation: 'Buen desempeño',
        is_retired_player: false,
        player: {
            id: 15,
            full_names: 'Deportista de Prueba con Nombre Largo',
            unique_code: 'E2E-015',
            photo_url: '/img/user.webp',
        },
    }],
}

async function mockAuthenticatedMatchEdit(page) {
    await page.addInitScript(() => {
        localStorage.setItem('auth-user', JSON.stringify({
            user: {
                id: 1,
                name: 'Escuela E2E',
                email: 'e2e@golapp.local',
                school_id: 1,
                school_name: 'Escuela E2E',
            },
            initialized: true,
            roles: ['school'],
            permissions: [],
            schoolPermissions: { 'school.module.matches': true },
        }))
    })

    await page.route('**/api/v2/user', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            data: {
                id: 1,
                name: 'Escuela E2E',
                email: 'e2e@golapp.local',
                school_id: 1,
                school_name: 'Escuela E2E',
                roles: ['school'],
                permissions: [],
                school_permissions: { 'school.module.matches': true },
            },
        }),
    }))

    await page.route('**/api/v2/admin/info_campus', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ is_school: true, schools: [], school_selected: 1 }),
    }))

    await page.route('**/api/v2/settings/general', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            current_school_id: 1,
            positions: ['Arquero', 'Defensa (Central)', 'Delantero'],
        }),
    }))

    await page.route('**/api/v2/settings/groups', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ tournaments: [{ value: 4, label: 'Liga E2E' }] }),
    }))
}

test('match edition groups fields, becomes mobile cards and preserves its payload', async ({ page }) => {
    await mockAuthenticatedMatchEdit(page)

    let submittedPayload = null
    await page.route('**/api/v2/matches/33', async route => {
        if (route.request().method() === 'GET') {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify(match),
            })
            return
        }

        submittedPayload = route.request().postDataJSON()
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ success: true }),
        })
    })

    await page.setViewportSize({ width: 1440, height: 900 })
    await page.goto('/control-competencias/33')

    await expect(page.locator('.match-table thead th')).toHaveText([
        'Deportista',
        'Participación',
        'Posición',
        'Rendimiento',
        'Disciplina',
        'Evaluación',
    ])
    await expect(page.locator('.match-player-row')).toHaveCSS('display', 'table-row')
    await expect(page.locator('.match-player-cell')).toHaveCSS('position', 'sticky')
    await expect(page.getByLabel('Goles')).toHaveCount(1)
    await expect(page.getByLabel('Observación')).toHaveCount(1)

    const playerFilter = page.getByLabel('Filtrar jugadores')
    await playerFilter.click()
    await page.getByLabel('Buscar jugador...').fill('E2E-015')
    const playerOptions = page.locator('#match-player-filter-listbox').getByRole('option')
    await expect(playerOptions).toHaveCount(1)
    await playerOptions.click()
    await expect(page.locator('.match-player-row')).toBeVisible()
    await expect(page.getByLabel('Goles')).toHaveCount(1)

    const generalConceptHeight = await page.getByLabel('Concepto General')
        .evaluate(element => element.getBoundingClientRect().height)
    expect(generalConceptHeight).toBeGreaterThanOrEqual(160)

    const columnLayout = await page.locator('.match-player-row').evaluate(row => ({
        playerWidth: row.querySelector('.match-player-cell').getBoundingClientRect().width,
        positionWidth: row.querySelector('.match-position-cell').getBoundingClientRect().width,
        playerNameTop: row.querySelector('.match-player-name').getBoundingClientRect().top,
        playerCodeTop: row.querySelector('.match-player-code').getBoundingClientRect().top,
    }))
    expect(columnLayout.positionWidth).toBeGreaterThan(columnLayout.playerWidth)
    expect(columnLayout.playerCodeTop).toBeGreaterThan(columnLayout.playerNameTop)

    const fieldPositions = await page.evaluate(() => {
        const top = label => document.querySelector(`[aria-label="${label}"]`)?.getBoundingClientRect().top
            ?? [...document.querySelectorAll('label')]
                .find(element => element.textContent.trim() === label)
                ?.nextElementSibling?.getBoundingClientRect().top

        return {
            goals: top('Goles'),
            assists: top('Asist. gol'),
            saves: top('Atajadas'),
            yellow: top('🟨 Amarillas'),
            red: top('🟥 Rojas'),
            rating: top('Calificación'),
            observation: top('Observación'),
        }
    })
    expect(fieldPositions.goals).toBe(fieldPositions.assists)
    expect(fieldPositions.saves).toBeGreaterThan(fieldPositions.goals)
    expect(fieldPositions.red).toBeGreaterThan(fieldPositions.yellow)
    expect(fieldPositions.observation).toBeGreaterThan(fieldPositions.rating)

    await page.evaluate(() => localStorage.setItem('dark_mode', 'dark'))
    await page.reload()
    await expect(page.locator('body')).toHaveClass(/dark/)

    await page.setViewportSize({ width: 720, height: 900 })
    await expect(page.locator('.match-player-row')).toHaveCSS('display', 'grid')
    await expect(page.locator('.match-player-cell')).toContainText('Deportista de Prueba con Nombre Largo')
    await expect(page.locator('.match-participation-cell')).toContainText('Asistió')
    await expect(page.locator('.match-performance-cell')).toContainText('Atajadas')

    const viewportFits = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)
    expect(viewportFits).toBe(true)

    const accessibility = await new AxeBuilder({ page })
        .include('.match-players-card')
        .analyze()
    expect(accessibility.violations).toEqual([])

    await page.setViewportSize({ width: 390, height: 844 })
    const phoneColumns = await page.locator('.match-player-row').evaluate(element => (
        getComputedStyle(element).gridTemplateColumns.split(' ').length
    ))
    expect(phoneColumns).toBe(1)
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true)

    const observation = page.getByLabel('Observación')
    const compactHeight = await observation.evaluate(element => element.getBoundingClientRect().height)
    await observation.focus()
    await expect.poll(() => observation.evaluate(element => element.getBoundingClientRect().height))
        .toBeGreaterThan(compactHeight)

    await page.getByLabel('Goles').selectOption('3')
    await observation.fill('Observación actualizada desde la tarjeta')
    await page.getByRole('button', { name: 'Guardar cambios' }).click()

    await expect.poll(() => submittedPayload).not.toBeNull()
    expect(submittedPayload._method).toBe('PUT')
    expect(submittedPayload.skill_controls).toHaveLength(1)
    expect(submittedPayload.skill_controls[0]).toMatchObject({
        id: 71,
        inscription_id: 21,
        goals: '3',
        observation: 'Observación actualizada desde la tarjeta',
    })
    expect(submittedPayload.status).toBe('played')
})

test('matches list explains which statuses feed competition statistics', async ({ page }) => {
    await mockAuthenticatedMatchEdit(page)
    await page.addInitScript(() => localStorage.setItem('dark_mode', 'dark'))
    await page.route('**/api/v2/datatables/matches**', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            data: [{
                id: 33,
                tournament_name: 'Liga E2E',
                competition_group_name: 'Sub 12 A',
                date: '10/08/2026',
                hour: '09:30 AM',
                rival_name: 'Rival E2E',
                status: 'scheduled',
                status_label: 'Programado',
                final_score: null,
                url_show: '/competencias/33/pdf',
            }],
            recordsTotal: 1,
            recordsFiltered: 1,
        }),
    }))

    await page.goto('/control-competencias')

    const notice = page.getByRole('note')
    await expect(notice).toContainText('Solo cuentan los partidos jugados')
    await expect(notice).toContainText('Programado')
    await expect(notice).toContainText('Jugado')
    await expect(page.locator('body')).toHaveClass(/dark/)
    await expect(notice.locator('p')).toHaveCSS('color', 'rgb(199, 208, 227)')

    const accessibility = await new AxeBuilder({ page })
        .include('.matches-stats-notice')
        .analyze()
    expect(accessibility.violations).toEqual([])
})
