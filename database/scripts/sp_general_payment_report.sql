CREATE PROCEDURE sp_general_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        p.year,
        COUNT(p.id) AS total_inscriptions,
        ROUND(SUM(p.enrollment_amount), 2) AS total_enrollment,
        ROUND(SUM(
            p.enrollment_amount + p.january_amount + p.february_amount + p.march_amount +
            p.april_amount + p.may_amount + p.june_amount + p.july_amount +
            p.august_amount + p.september_amount + p.october_amount +
            p.november_amount + p.december_amount
        ), 2) AS total_raised,

        SUM((p.january = 8) + (p.february = 8) + (p.march = 8) + (p.april = 8) +
            (p.may = 8) + (p.june = 8) + (p.july = 8) + (p.august = 8) +
            (p.september = 8) + (p.october = 8) + (p.november = 8) + (p.december = 8)
        ) AS monthly_payments_debt,

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
        ) AS total_compliance_percentage
    FROM payments p
    WHERE
        (p_year IS NULL OR p.year = p_year)
        AND (p_school_id IS NULL OR p.school_id = p_school_id)
    GROUP BY p.year;
END
