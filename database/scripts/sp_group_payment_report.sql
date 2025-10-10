CREATE PROCEDURE sp_group_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED,
    IN p_training_group_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        tg.name AS grupo,
        p.year,
        COUNT(p.id) AS total_inscriptions,

        /* Total recaudado (incluye matrícula) */
        ROUND(SUM(
            p.enrollment_amount + p.january_amount + p.february_amount + p.march_amount +
            p.april_amount + p.may_amount + p.june_amount + p.july_amount +
            p.august_amount + p.september_amount + p.october_amount +
            p.november_amount + p.december_amount
        ), 2) AS total_raised,

        ROUND(SUM(p.enrollment_amount), 2) AS total_enrollment,

        /* Meses pagados */
        SUM(
            (p.january IN (1,9,10,11,12)) +
            (p.february IN (1,9,10,11,12)) +
            (p.march IN (1,9,10,11,12)) +
            (p.april IN (1,9,10,11,12)) +
            (p.may IN (1,9,10,11,12)) +
            (p.june IN (1,9,10,11,12)) +
            (p.july IN (1,9,10,11,12)) +
            (p.august IN (1,9,10,11,12)) +
            (p.september IN (1,9,10,11,12)) +
            (p.october IN (1,9,10,11,12)) +
            (p.november IN (1,9,10,11,12)) +
            (p.december IN (1,9,10,11,12))
        ) AS monthly_payments_paid,

        /* Meses con deuda */
        SUM(
            (p.january = 2) + (p.february = 2) + (p.march = 2) + (p.april = 2) +
            (p.may = 2) + (p.june = 2) + (p.july = 2) + (p.august = 2) +
            (p.september = 2) + (p.october = 2) + (p.november = 2) + (p.december = 2)
        ) AS monthly_payments_debt,

        /* Meses becados */
        SUM(
            (p.january = 8) + (p.february = 8) + (p.march = 8) + (p.april = 8) +
            (p.may = 8) + (p.june = 8) + (p.july = 8) + (p.august = 8) +
            (p.september = 8) + (p.october = 8) + (p.november = 8) + (p.december = 8)
        ) AS monthly_payments_scholarship,

        /* Meses no válidos */
        SUM(
            (p.january IN (4,5,6,14)) + (p.february IN (4,5,6,14)) +
            (p.march IN (4,5,6,14)) + (p.april IN (4,5,6,14)) +
            (p.may IN (4,5,6,14)) + (p.june IN (4,5,6,14)) +
            (p.july IN (4,5,6,14)) + (p.august IN (4,5,6,14)) +
            (p.september IN (4,5,6,14)) + (p.october IN (4,5,6,14)) +
            (p.november IN (4,5,6,14)) + (p.december IN (4,5,6,14))
        ) AS monthly_payments_others,

        /* Porcentaje de cumplimiento (solo meses válidos) */
        ROUND(
            SUM(
                (p.january IN (1,9,10,11,12)) + (p.february IN (1,9,10,11,12)) +
                (p.march IN (1,9,10,11,12)) + (p.april IN (1,9,10,11,12)) +
                (p.may IN (1,9,10,11,12)) + (p.june IN (1,9,10,11,12)) +
                (p.july IN (1,9,10,11,12)) + (p.august IN (1,9,10,11,12)) +
                (p.september IN (1,9,10,11,12)) + (p.october IN (1,9,10,11,12)) +
                (p.november IN (1,9,10,11,12)) + (p.december IN (1,9,10,11,12))
            ) /
            (
                (COUNT(p.id) * 12)
                - SUM(
                    (p.january IN (4,5,6,14,8)) + (p.february IN (4,5,6,14,8)) +
                    (p.march IN (4,5,6,14,8)) + (p.april IN (4,5,6,14,8)) +
                    (p.may IN (4,5,6,14,8)) + (p.june IN (4,5,6,14,8)) +
                    (p.july IN (4,5,6,14,8)) + (p.august IN (4,5,6,14,8)) +
                    (p.september IN (4,5,6,14,8)) + (p.october IN (4,5,6,14,8)) +
                    (p.november IN (4,5,6,14,8)) + (p.december IN (4,5,6,14,8))
                )
            ) * 100, 2
        ) AS percentage_compliance

    FROM payments p
    INNER JOIN training_groups tg ON tg.id = p.training_group_id
    WHERE
        (p_year IS NULL OR p.year = p_year)
        AND (p_school_id IS NULL OR p.school_id = p_school_id)
        AND (p_training_group_id IS NULL OR p.training_group_id = p_training_group_id)
    GROUP BY tg.id, tg.name, p.year
    ORDER BY p.year DESC, total_raised DESC;
END
