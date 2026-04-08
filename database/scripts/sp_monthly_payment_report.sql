DROP PROCEDURE IF EXISTS sp_monthly_payment_report;

DELIMITER $$

CREATE PROCEDURE sp_monthly_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED,
    IN p_training_group_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        v.payment_year AS year,

        ROUND(SUM(CASE
            WHEN v.is_enrollment = 1 THEN v.report_amount
            ELSE 0
        END), 2) AS total_enrollment,

        ROUND(SUM(CASE
            WHEN v.month_number = 1 THEN v.report_amount
            ELSE 0
        END), 2) AS january,
        SUM(CASE
            WHEN v.month_number = 1 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_january,

        ROUND(SUM(CASE
            WHEN v.month_number = 2 THEN v.report_amount
            ELSE 0
        END), 2) AS february,
        SUM(CASE
            WHEN v.month_number = 2 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_february,

        ROUND(SUM(CASE
            WHEN v.month_number = 3 THEN v.report_amount
            ELSE 0
        END), 2) AS march,
        SUM(CASE
            WHEN v.month_number = 3 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_march,

        ROUND(SUM(CASE
            WHEN v.month_number = 4 THEN v.report_amount
            ELSE 0
        END), 2) AS april,
        SUM(CASE
            WHEN v.month_number = 4 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_april,

        ROUND(SUM(CASE
            WHEN v.month_number = 5 THEN v.report_amount
            ELSE 0
        END), 2) AS may,
        SUM(CASE
            WHEN v.month_number = 5 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_may,

        ROUND(SUM(CASE
            WHEN v.month_number = 6 THEN v.report_amount
            ELSE 0
        END), 2) AS june,
        SUM(CASE
            WHEN v.month_number = 6 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_june,

        ROUND(SUM(CASE
            WHEN v.month_number = 7 THEN v.report_amount
            ELSE 0
        END), 2) AS july,
        SUM(CASE
            WHEN v.month_number = 7 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_july,

        ROUND(SUM(CASE
            WHEN v.month_number = 8 THEN v.report_amount
            ELSE 0
        END), 2) AS august,
        SUM(CASE
            WHEN v.month_number = 8 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_august,

        ROUND(SUM(CASE
            WHEN v.month_number = 9 THEN v.report_amount
            ELSE 0
        END), 2) AS september,
        SUM(CASE
            WHEN v.month_number = 9 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_september,

        ROUND(SUM(CASE
            WHEN v.month_number = 10 THEN v.report_amount
            ELSE 0
        END), 2) AS october,
        SUM(CASE
            WHEN v.month_number = 10 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_october,

        ROUND(SUM(CASE
            WHEN v.month_number = 11 THEN v.report_amount
            ELSE 0
        END), 2) AS november,
        SUM(CASE
            WHEN v.month_number = 11 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_november,

        ROUND(SUM(CASE
            WHEN v.month_number = 12 THEN v.report_amount
            ELSE 0
        END), 2) AS december,
        SUM(CASE
            WHEN v.month_number = 12 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS payments_december,

        ROUND(SUM(v.report_amount), 2) AS total_raised

    FROM vw_payments_report_detail v
    WHERE
        (p_year IS NULL OR v.payment_year = p_year)
        AND (p_school_id IS NULL OR v.school_id = p_school_id)
        AND (p_training_group_id IS NULL OR v.training_group_id = p_training_group_id)
    GROUP BY v.payment_year
    ORDER BY v.payment_year;
END$$

DELIMITER ;