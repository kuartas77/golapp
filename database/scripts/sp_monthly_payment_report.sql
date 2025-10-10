CREATE PROCEDURE sp_monthly_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED,
    IN p_training_group_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        p.year,
        ROUND(SUM(p.enrollment_amount), 2) AS total_enrollment,

        ROUND(SUM(p.january_amount), 2) AS january,
        SUM(p.january IN (1,9,10,11,12)) AS payments_january,

        ROUND(SUM(p.february_amount), 2) AS february,
        SUM(p.february IN (1,9,10,11,12)) AS payments_february,

        ROUND(SUM(p.march_amount), 2) AS march,
        SUM(p.march IN (1,9,10,11,12)) AS payments_march,

        ROUND(SUM(p.april_amount), 2) AS april,
        SUM(p.april IN (1,9,10,11,12)) AS payments_april,

        ROUND(SUM(p.may_amount), 2) AS may,
        SUM(p.may IN (1,9,10,11,12)) AS payments_may,

        ROUND(SUM(p.june_amount), 2) AS june,
        SUM(p.june IN (1,9,10,11,12)) AS payments_june,

        ROUND(SUM(p.july_amount), 2) AS july,
        SUM(p.july IN (1,9,10,11,12)) AS payments_july,

        ROUND(SUM(p.august_amount), 2) AS august,
        SUM(p.august IN (1,9,10,11,12)) AS payments_august,

        ROUND(SUM(p.september_amount), 2) AS september,
        SUM(p.september IN (1,9,10,11,12)) AS payments_september,

        ROUND(SUM(p.october_amount), 2) AS october,
        SUM(p.october IN (1,9,10,11,12)) AS payments_october,

        ROUND(SUM(p.november_amount), 2) AS november,
        SUM(p.november IN (1,9,10,11,12)) AS payments_november,

        ROUND(SUM(p.december_amount), 2) AS december,
        SUM(p.december IN (1,9,10,11,12)) AS payments_december,

        ROUND(SUM(
            p.enrollment_amount + p.january_amount + p.february_amount + p.march_amount +
            p.april_amount + p.may_amount + p.june_amount + p.july_amount +
            p.august_amount + p.september_amount + p.october_amount +
            p.november_amount + p.december_amount
        ), 2) AS total_raised
    FROM payments p
    WHERE
        (p_year IS NULL OR p.year = p_year)
        AND (p_school_id IS NULL OR p.school_id = p_school_id)
        AND (p_training_group_id IS NULL OR p.training_group_id = p_training_group_id)
    GROUP BY p.year;
END
