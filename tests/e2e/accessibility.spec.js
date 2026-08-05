import { expect, test } from '@playwright/test';

const CHANNEL_PATTERN = /[\d.]+/g;

function luminance(cssColor) {
    const channels = cssColor.match(CHANNEL_PATTERN).slice(0, 3).map(Number);

    return channels
        .map(channel => channel / 255)
        .map(channel => channel <= 0.04045
            ? channel / 12.92
            : ((channel + 0.055) / 1.055) ** 2.4)
        .reduce((total, channel, index) => total + channel * [0.2126, 0.7152, 0.0722][index], 0);
}

function contrastRatio(foreground, background) {
    const lighter = Math.max(luminance(foreground), luminance(background));
    const darker = Math.min(luminance(foreground), luminance(background));

    return (lighter + 0.05) / (darker + 0.05);
}

for (const mode of ['light', 'dark']) {
    test(`${mode} theme preserves readable text and visible keyboard focus`, async ({ page }) => {
        await page.addInitScript(selectedMode => {
            localStorage.setItem('dark_mode', selectedMode);
        }, mode);

        await page.goto('/ingreso');
        await expect(page.getByRole('button', { name: 'Ingresar' })).toBeVisible();
        await expect(page.locator('body')).toHaveClass(mode === 'dark' ? /dark/ : /^(?!.*dark)/);

        const colors = await page.evaluate(() => {
            const fixture = document.createElement('div');
            fixture.style.backgroundColor = document.body.classList.contains('dark') ? '#060818' : '#f1f2f3';
            fixture.innerHTML = '<span class="text-muted">Texto secundario</span>';
            document.body.append(fixture);

            const bodyStyles = getComputedStyle(document.body);
            const fixtureStyles = getComputedStyle(fixture);
            const mutedStyles = getComputedStyle(fixture.firstElementChild);

            return {
                bodyText: bodyStyles.color,
                background: fixtureStyles.backgroundColor,
                mutedText: mutedStyles.color,
            };
        });

        expect(contrastRatio(colors.bodyText, colors.background)).toBeGreaterThanOrEqual(4.5);
        expect(contrastRatio(colors.mutedText, colors.background)).toBeGreaterThanOrEqual(4.5);

        await page.keyboard.press('Tab');

        const focusStyles = await page.locator(':focus').evaluate(element => {
            const styles = getComputedStyle(element);

            return {
                outlineColor: styles.outlineColor,
                outlineStyle: styles.outlineStyle,
                outlineWidth: styles.outlineWidth,
            };
        });

        expect(focusStyles.outlineStyle).toBe('solid');
        expect(focusStyles.outlineWidth).toBe('3px');
        expect(contrastRatio(focusStyles.outlineColor, colors.background)).toBeGreaterThanOrEqual(3);
    });
}

test('public school error state is announced and can recover in place', async ({ page }) => {
    let requestCount = 0;

    await page.route('**/api/v2/portal/escuelas/**/data', async route => {
        requestCount += 1;

        if (requestCount === 1) {
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'El servicio de inscripciones no está disponible.' }),
            });
            return;
        }

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                data: {
                    school: {
                        id: 1,
                        name: 'Escuela E2E',
                        slug: 'escuela-e2e',
                        inscriptions_enabled: false,
                        tutor_platform: false,
                        create_contract: false,
                        send_documents: false,
                    },
                    year: 2026,
                    inscriptionLimit: { is_full: false },
                    contracts: { available: [] },
                    links: {},
                    endpoints: {},
                    assets: {},
                    options: {},
                    recaptcha: { enabled: false },
                },
            }),
        });
    });

    await page.goto('/portal/escuelas/escuela-e2e');

    const errorState = page.getByRole('alert').filter({ hasText: 'El servicio de inscripciones no está disponible.' });
    await expect(errorState).toBeVisible();
    await errorState.getByRole('button', { name: 'Reintentar' }).click();

    await expect(page.getByRole('heading', { name: 'Escuela E2E', level: 1 })).toBeVisible();
    await expect(errorState).toBeHidden();
    expect(requestCount).toBe(2);
});

test('attendance page explains the initial state before the first search', async ({ page }) => {
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
                school_permissions: {
                    'school.module.attendances': true,
                },
            },
        }),
    }));

    await page.route('**/api/v2/admin/info_campus', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            is_school: true,
            schools: [],
            school_selected: 1,
        }),
    }));

    await page.route('**/api/v2/settings/general', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            current_school_id: 1,
            all_t_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
            t_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
            attendance_training_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
        }),
    }));

    await page.goto('/asistencias');

    await expect(page).toHaveURL(/\/asistencias$/);
    await expect(page.getByRole('heading', { name: 'Selecciona un grupo' })).toBeVisible();
    await expect(page.getByText('Elige un grupo y presiona Buscar para mostrar sus días de entrenamiento')).toBeVisible();
    await expect(page.locator('#attendance_table')).toHaveCount(0);
});

test('monthly payments starts with guidance instead of an empty table', async ({ page }) => {
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
                school_permissions: {
                    'school.module.payments': true,
                },
            },
        }),
    }));

    await page.route('**/api/v2/admin/info_campus', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ is_school: true, schools: [], school_selected: 1 }),
    }));

    await page.route('**/api/v2/settings/general', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            current_school_id: 1,
            all_t_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
            t_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
            normal_training_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
            categories: [{ category: 'Sub 12' }],
            inscription_years: [{ id: 2026, year: 2026 }],
            type_payments: [{ value: 2, label: 'Debe' }],
        }),
    }));

    await page.route('**/api/v2/payments/status-catalog', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            statuses: [{ value: '2', label: 'Debe' }],
            groups: { paid: [], debt: [2], player_credit: [] },
            months: [],
        }),
    }));

    await page.goto('/mensualidades');

    await expect(page).toHaveURL(/\/mensualidades$/);
    await expect(page.getByRole('heading', { name: 'Consulta las mensualidades' })).toBeVisible();
    await expect(page.getByText('Selecciona un grupo o una categoría y presiona Buscar')).toBeVisible();
    await expect(page.locator('[data-tour="monthly-payments-table"] table')).toHaveCount(0);
});

test('invoices announces a failed load and recovers with accessible row actions', async ({ page }) => {
    let invoicesRequestCount = 0;

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
                school_permissions: {
                    'school.module.billing': true,
                },
            },
        }),
    }));

    await page.route('**/api/v2/admin/info_campus', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ is_school: true, schools: [], school_selected: 1 }),
    }));

    await page.route('**/api/v2/settings/general', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
            current_school_id: 1,
            normal_training_groups: [{ id: 10, name: 'Sub 12', full_group: 'Sub 12' }],
        }),
    }));

    await page.route('**/api/v2/invoices**', async route => {
        invoicesRequestCount += 1;

        if (invoicesRequestCount === 1) {
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'El servicio de facturación no está disponible.' }),
            });
            return;
        }

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                data: [{
                    id: 42,
                    invoice_number: 'FAC-0042',
                    student_name: 'Deportista E2E',
                    training_group: { name: 'Sub 12' },
                    total_amount: 120000,
                    paid_amount: 0,
                    status: 'pending',
                    created_at: '2026-08-05T12:00:00Z',
                    url_print: '/facturas/42/imprimir',
                }],
                recordsTotal: 1,
                recordsFiltered: 1,
            }),
        });
    });

    await page.goto('/facturas');

    const errorState = page.getByRole('alert').filter({ hasText: 'El servicio de facturación no está disponible.' });
    await expect(errorState).toBeVisible();
    await errorState.getByRole('button', { name: 'Reintentar' }).click();

    await expect(errorState).toBeHidden();
    await expect(page.getByRole('button', { name: 'Ver factura FAC-0042' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Imprimir factura FAC-0042' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Revisar anulación de factura FAC-0042' })).toBeVisible();
    await expect(page.getByText('Totales de esta página:')).toBeVisible();
    expect(invoicesRequestCount).toBe(2);
});

test('invoice items and custom charges recover from failed table requests', async ({ page }) => {
    let itemRequestCount = 0;
    let chargeRequestCount = 0;

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
                school_permissions: {
                    'school.module.billing': true,
                },
            },
        }),
    }));

    await page.route('**/api/v2/admin/info_campus', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ is_school: true, schools: [], school_selected: 1 }),
    }));

    await page.route('**/api/v2/settings/general', route => route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ current_school_id: 1 }),
    }));

    await page.route('**/api/v2/invoices/items/invoices**', async route => {
        itemRequestCount += 1;

        if (itemRequestCount === 1) {
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'No se pudieron consultar los conceptos.' }),
            });
            return;
        }

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                data: [{
                    id: 71,
                    invoice: { invoice_number: 'FAC-ITEM-1', student_name: 'Deportista E2E' },
                    created_at: '2026-08-05T12:00:00Z',
                    type: 'monthly',
                    description: 'Mensualidad agosto',
                    payment_method: null,
                    quantity: 1,
                    unit_price: 100000,
                    total: 100000,
                    is_paid: false,
                }],
                recordsTotal: 1,
                recordsFiltered: 1,
            }),
        });
    });

    await page.goto('/facturas/items');
    await expect(page).toHaveURL(/\/facturas\/items$/);

    const itemError = page.getByRole('alert').filter({ hasText: 'No se pudieron consultar los conceptos.' });
    await expect(itemError).toBeVisible();
    await itemError.getByRole('button', { name: 'Reintentar' }).click();
    await expect(itemError).toBeHidden();
    await expect(page.getByText('FAC-ITEM-1')).toBeVisible();
    await expect(page.getByText('Totales de esta página:')).toBeVisible();

    await page.route('**/api/v2/admin/inscription-custom-charges**', async route => {
        chargeRequestCount += 1;

        if (chargeRequestCount === 1) {
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'No se pudieron consultar los cargos.' }),
            });
            return;
        }

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                data: [{
                    id: 81,
                    player_name: 'Deportista Cargo E2E',
                    player_unique_code: 'DEP-81',
                    inscription_year: 2026,
                    name: 'Transporte',
                    value: 25000,
                    status: 'pending',
                    due_date: '2026-08-20',
                    invoice_number: null,
                    invoice_item_id: null,
                }],
                recordsTotal: 1,
                recordsFiltered: 1,
            }),
        });
    });

    await page.goto('/facturas/cargos-personalizados');

    const chargeError = page.getByRole('alert').filter({ hasText: 'No se pudieron consultar los cargos.' });
    await expect(chargeError).toBeVisible();
    await chargeError.getByRole('button', { name: 'Reintentar' }).click();
    await expect(chargeError).toBeHidden();
    await expect(page.getByText('Deportista Cargo E2E')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Editar' })).toBeVisible();

    expect(itemRequestCount).toBe(2);
    expect(chargeRequestCount).toBe(2);
});
