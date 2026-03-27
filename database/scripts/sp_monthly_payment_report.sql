DROP PROCEDURE IF EXISTS sp_monthly_payment_report;

DELIMITER $$
CREATE PROCEDURE sp_monthly_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED,
    IN p_training_group_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        p.year,

        ROUND(SUM(COALESCE(p.enrollment_amount, 0)), 2) AS total_enrollment,

        ROUND(SUM(CASE WHEN p.january IN (1,9,10,11,12) THEN COALESCE(p.january_amount, 0) ELSE 0 END), 2) AS january,
        SUM(CASE WHEN p.january IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_january,

        ROUND(SUM(CASE WHEN p.february IN (1,9,10,11,12) THEN COALESCE(p.february_amount, 0) ELSE 0 END), 2) AS february,
        SUM(CASE WHEN p.february IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_february,

        ROUND(SUM(CASE WHEN p.march IN (1,9,10,11,12) THEN COALESCE(p.march_amount, 0) ELSE 0 END), 2) AS march,
        SUM(CASE WHEN p.march IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_march,

        ROUND(SUM(CASE WHEN p.april IN (1,9,10,11,12) THEN COALESCE(p.april_amount, 0) ELSE 0 END), 2) AS april,
        SUM(CASE WHEN p.april IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_april,

        ROUND(SUM(CASE WHEN p.may IN (1,9,10,11,12) THEN COALESCE(p.may_amount, 0) ELSE 0 END), 2) AS may,
        SUM(CASE WHEN p.may IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_may,

        ROUND(SUM(CASE WHEN p.june IN (1,9,10,11,12) THEN COALESCE(p.june_amount, 0) ELSE 0 END), 2) AS june,
        SUM(CASE WHEN p.june IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_june,

        ROUND(SUM(CASE WHEN p.july IN (1,9,10,11,12) THEN COALESCE(p.july_amount, 0) ELSE 0 END), 2) AS july,
        SUM(CASE WHEN p.july IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_july,

        ROUND(SUM(CASE WHEN p.august IN (1,9,10,11,12) THEN COALESCE(p.august_amount, 0) ELSE 0 END), 2) AS august,
        SUM(CASE WHEN p.august IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_august,

        ROUND(SUM(CASE WHEN p.september IN (1,9,10,11,12) THEN COALESCE(p.september_amount, 0) ELSE 0 END), 2) AS september,
        SUM(CASE WHEN p.september IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_september,

        ROUND(SUM(CASE WHEN p.october IN (1,9,10,11,12) THEN COALESCE(p.october_amount, 0) ELSE 0 END), 2) AS october,
        SUM(CASE WHEN p.october IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_october,

        ROUND(SUM(CASE WHEN p.november IN (1,9,10,11,12) THEN COALESCE(p.november_amount, 0) ELSE 0 END), 2) AS november,
        SUM(CASE WHEN p.november IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_november,

        ROUND(SUM(CASE WHEN p.december IN (1,9,10,11,12) THEN COALESCE(p.december_amount, 0) ELSE 0 END), 2) AS december,
        SUM(CASE WHEN p.december IN (1,9,10,11,12) THEN 1 ELSE 0 END) AS payments_december,

        ROUND(SUM(
            COALESCE(p.enrollment_amount, 0) +
            CASE WHEN p.january IN (1,9,10,11,12) THEN COALESCE(p.january_amount, 0) ELSE 0 END +
            CASE WHEN p.february IN (1,9,10,11,12) THEN COALESCE(p.february_amount, 0) ELSE 0 END +
            CASE WHEN p.march IN (1,9,10,11,12) THEN COALESCE(p.march_amount, 0) ELSE 0 END +
            CASE WHEN p.april IN (1,9,10,11,12) THEN COALESCE(p.april_amount, 0) ELSE 0 END +
            CASE WHEN p.may IN (1,9,10,11,12) THEN COALESCE(p.may_amount, 0) ELSE 0 END +
            CASE WHEN p.june IN (1,9,10,11,12) THEN COALESCE(p.june_amount, 0) ELSE 0 END +
            CASE WHEN p.july IN (1,9,10,11,12) THEN COALESCE(p.july_amount, 0) ELSE 0 END +
            CASE WHEN p.august IN (1,9,10,11,12) THEN COALESCE(p.august_amount, 0) ELSE 0 END +
            CASE WHEN p.september IN (1,9,10,11,12) THEN COALESCE(p.september_amount, 0) ELSE 0 END +
            CASE WHEN p.october IN (1,9,10,11,12) THEN COALESCE(p.october_amount, 0) ELSE 0 END +
            CASE WHEN p.november IN (1,9,10,11,12) THEN COALESCE(p.november_amount, 0) ELSE 0 END +
            CASE WHEN p.december IN (1,9,10,11,12) THEN COALESCE(p.december_amount, 0) ELSE 0 END
        ), 2) AS total_raised

    FROM payments p
    WHERE
        (p_year IS NULL OR p.year = p_year)
        AND (p_school_id IS NULL OR p.school_id = p_school_id)
        AND (p_training_group_id IS NULL OR p.training_group_id = p_training_group_id)
    GROUP BY p.year;
END$$

DELIMITER ;