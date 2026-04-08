DROP PROCEDURE IF EXISTS sp_general_payment_report;

DELIMITER $$

CREATE PROCEDURE sp_general_payment_report(
    IN p_year INTEGER,
    IN p_school_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        v.payment_year AS year,

        COUNT(DISTINCT v.inscription_id) AS total_inscriptions,

        ROUND(SUM(CASE
            WHEN v.is_enrollment = 1 THEN v.report_amount
            ELSE 0
        END), 2) AS total_enrollment,

        ROUND(SUM(v.report_amount), 2) AS total_raised,

        SUM(CASE
            WHEN v.is_monthly = 1 AND v.status_code = 2 THEN 1
            ELSE 0
        END) AS monthly_payments_debt,

        ROUND(
            (
                SUM(CASE
                    WHEN v.is_monthly = 1 AND v.sums_in_reports = 1 THEN 1
                    ELSE 0
                END) * 100
            ) / NULLIF(
                (
                    SUM(CASE
                        WHEN v.is_monthly = 1 THEN 1
                        ELSE 0
                    END)
                    -
                    SUM(CASE
                        WHEN v.is_monthly = 1 AND v.status_code IN (4,14,8) THEN 1
                        ELSE 0
                    END)
                ),
                0
            ),
            2
        ) AS total_compliance_percentage

    FROM vw_payments_report_detail v
    WHERE
        (p_year IS NULL OR v.payment_year = p_year)
        AND (p_school_id IS NULL OR v.school_id = p_school_id)
    GROUP BY v.payment_year
    ORDER BY v.payment_year DESC;
END$$

DELIMITER ;