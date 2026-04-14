CREATE OR REPLACE VIEW vw_payments_report_detail AS
SELECT
    x.payment_id,
    x.school_id,
    x.training_group_id,
    x.inscription_id,
    x.unique_code,
    x.payment_year,

    CASE
        WHEN x.period_order = 0 THEN NULL
        ELSE x.period_order
    END AS month_number,

    x.period_order,
    x.period_key,
    x.period_label,

    CASE WHEN x.period_order = 0 THEN 1 ELSE 0 END AS is_enrollment,
    CASE WHEN x.period_order BETWEEN 1 AND 12 THEN 1 ELSE 0 END AS is_monthly,

    x.status_code,

    CASE x.status_code
        WHEN 0 THEN 'Pendiente'
        WHEN 1 THEN 'Pagó'
        WHEN 9 THEN 'Pagó - Efectivo'
        WHEN 10 THEN 'Pagó - Consignación'
        WHEN 11 THEN 'Pago Anualidad Consignación'
        WHEN 12 THEN 'Pago Anualidad Efectivo'
        WHEN 13 THEN 'Acuerdo de Pago'
        WHEN 14 THEN 'No Aplica'
        WHEN 2 THEN 'Debe'
        WHEN 3 THEN 'Abonó'
        WHEN 4 THEN 'Incapacidad'
        WHEN 5 THEN 'Retiro Temporal'
        WHEN 6 THEN 'Retiro Definitivo'
        WHEN 7 THEN 'Otro'
        WHEN 8 THEN 'Becado'
        ELSE 'Desconocido'
    END AS status_label,

    x.amount,

    CASE
        WHEN x.status_code IN (1, 9, 10, 11, 12, 5, 6) THEN 1
        ELSE 0
    END AS sums_in_reports,

    CASE
        WHEN x.status_code IN (1, 9, 10, 11, 12, 5, 6) THEN x.amount
        ELSE CAST(0 AS DECIMAL(10,2))
    END AS report_amount,

    CASE
        WHEN x.deleted_at IS NOT NULL THEN 1
        ELSE 0
    END AS is_deleted_record,

    CASE
        WHEN x.deleted_at IS NOT NULL
         AND x.status_code IN (1, 9, 10, 11, 12, 5, 6)
        THEN x.amount
        ELSE CAST(0 AS DECIMAL(10,2))
    END AS deleted_record_report_amount,

    x.created_at,
    x.updated_at,
    x.deleted_at

FROM (
    SELECT
        p.id AS payment_id,
        p.school_id,
        p.training_group_id,
        p.inscription_id,
        p.unique_code,
        CAST(p.`year` AS UNSIGNED) AS payment_year,

        m.period_order,
        m.period_key,
        m.period_label,

        CASE m.period_order
            WHEN 0 THEN p.enrollment
            WHEN 1 THEN p.january
            WHEN 2 THEN p.february
            WHEN 3 THEN p.march
            WHEN 4 THEN p.april
            WHEN 5 THEN p.may
            WHEN 6 THEN p.june
            WHEN 7 THEN p.july
            WHEN 8 THEN p.august
            WHEN 9 THEN p.september
            WHEN 10 THEN p.october
            WHEN 11 THEN p.november
            WHEN 12 THEN p.december
        END AS status_code,

        CAST(
            CASE m.period_order
                WHEN 0 THEN COALESCE(p.enrollment_amount, 0)
                WHEN 1 THEN COALESCE(p.january_amount, 0)
                WHEN 2 THEN COALESCE(p.february_amount, 0)
                WHEN 3 THEN COALESCE(p.march_amount, 0)
                WHEN 4 THEN COALESCE(p.april_amount, 0)
                WHEN 5 THEN COALESCE(p.may_amount, 0)
                WHEN 6 THEN COALESCE(p.june_amount, 0)
                WHEN 7 THEN COALESCE(p.july_amount, 0)
                WHEN 8 THEN COALESCE(p.august_amount, 0)
                WHEN 9 THEN COALESCE(p.september_amount, 0)
                WHEN 10 THEN COALESCE(p.october_amount, 0)
                WHEN 11 THEN COALESCE(p.november_amount, 0)
                WHEN 12 THEN COALESCE(p.december_amount, 0)
            END
        AS DECIMAL(10,2)) AS amount,

        p.created_at,
        p.updated_at,
        p.deleted_at

    FROM payments p
    INNER JOIN (
        SELECT 0 AS period_order, 'enrollment' AS period_key, 'Matrícula' AS period_label
        UNION ALL SELECT 1, 'january', 'Enero'
        UNION ALL SELECT 2, 'february', 'Febrero'
        UNION ALL SELECT 3, 'march', 'Marzo'
        UNION ALL SELECT 4, 'april', 'Abril'
        UNION ALL SELECT 5, 'may', 'Mayo'
        UNION ALL SELECT 6, 'june', 'Junio'
        UNION ALL SELECT 7, 'july', 'Julio'
        UNION ALL SELECT 8, 'august', 'Agosto'
        UNION ALL SELECT 9, 'september', 'Septiembre'
        UNION ALL SELECT 10, 'october', 'Octubre'
        UNION ALL SELECT 11, 'november', 'Noviembre'
        UNION ALL SELECT 12, 'december', 'Diciembre'
    ) m ON 1 = 1
) x;