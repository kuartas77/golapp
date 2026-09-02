SELECT
    COUNT(*) AS conceptos,
    ROUND(SUM(ii.total), 2) AS total_no_registrado
FROM invoice_items ii
INNER JOIN invoices i
    ON i.id = ii.invoice_id
LEFT JOIN payments p
    ON p.id = ii.payment_id
WHERE i.school_id = :school_id
  AND i.year = :year
  AND i.deleted_at IS NULL
  AND i.status <> 'cancelled'
  AND ii.is_paid = 1
  AND ii.type IN ('monthly', 'enrollment')
  AND (
        p.id IS NULL
        OR CASE ii.month
            WHEN 'enrollment' THEN p.enrollment
            WHEN 'january' THEN p.january
            WHEN 'february' THEN p.february
            WHEN 'march' THEN p.march
            WHEN 'april' THEN p.april
            WHEN 'may' THEN p.may
            WHEN 'june' THEN p.june
            WHEN 'july' THEN p.july
            WHEN 'august' THEN p.august
            WHEN 'september' THEN p.september
            WHEN 'october' THEN p.october
            WHEN 'november' THEN p.november
            WHEN 'december' THEN p.december
        END NOT IN (1, 3, 9, 10, 11, 12, 15)
        OR ii.month IS NULL
    );


-- para ver el detalle de cada concepto:

SELECT
    ii.id AS invoice_item_id,
    i.id AS invoice_id,
    i.invoice_number,
    ii.type,
    ii.month,
    ii.total AS valor_facturado,
    ii.payment_id,
    CASE ii.month
        WHEN 'enrollment' THEN p.enrollment
        WHEN 'january' THEN p.january
        WHEN 'february' THEN p.february
        WHEN 'march' THEN p.march
        WHEN 'april' THEN p.april
        WHEN 'may' THEN p.may
        WHEN 'june' THEN p.june
        WHEN 'july' THEN p.july
        WHEN 'august' THEN p.august
        WHEN 'september' THEN p.september
        WHEN 'october' THEN p.october
        WHEN 'november' THEN p.november
        WHEN 'december' THEN p.december
    END AS estado_pago,
    CASE
        WHEN p.id IS NULL THEN 'Sin registro de pago'
        WHEN ii.month IS NULL THEN 'Sin mes asociado'
        ELSE 'Estado no monetario'
    END AS motivo
FROM invoice_items ii
INNER JOIN invoices i
    ON i.id = ii.invoice_id
LEFT JOIN payments p
    ON p.id = ii.payment_id
WHERE i.school_id = :school_id
  AND i.year = :year
  AND i.deleted_at IS NULL
  AND i.status <> 'cancelled'
  AND ii.is_paid = 1
  AND ii.type IN ('monthly', 'enrollment')
  AND (
        p.id IS NULL
        OR CASE ii.month
            WHEN 'enrollment' THEN p.enrollment
            WHEN 'january' THEN p.january
            WHEN 'february' THEN p.february
            WHEN 'march' THEN p.march
            WHEN 'april' THEN p.april
            WHEN 'may' THEN p.may
            WHEN 'june' THEN p.june
            WHEN 'july' THEN p.july
            WHEN 'august' THEN p.august
            WHEN 'september' THEN p.september
            WHEN 'october' THEN p.october
            WHEN 'november' THEN p.november
            WHEN 'december' THEN p.december
        END NOT IN (1, 3, 9, 10, 11, 12, 15)
        OR ii.month IS NULL
    )
ORDER BY ii.total DESC;


-- Cargos personalizados pagados sin factura
SELECT
    COUNT(*) AS cantidad_cargos,
    ROUND(SUM(ic.value), 2) AS total_pagado_sin_factura
FROM inscription_custom_charges ic
WHERE ic.school_id = :school_id
  AND ic.status = 'paid'
  AND ic.invoice_item_id IS NULL
  AND ic.due_date >= CONCAT(:year, '-01-01')
  AND ic.due_date < CONCAT(:year + 1, '-01-01')
  AND ic.deleted_at IS NULL;

SELECT
    ic.id,
    ic.name AS concepto,
    ic.value AS valor,
    ic.due_date AS fecha,
    ic.inscription_id,
    ic.player_id
FROM inscription_custom_charges ic
WHERE ic.school_id = 9
  AND ic.status = 'paid'
  AND ic.invoice_item_id IS NULL
  AND ic.due_date >= '2026-01-01'
  AND ic.due_date < '2027-01-01'
  AND ic.deleted_at IS NULL
ORDER BY ic.due_date, ic.id;
