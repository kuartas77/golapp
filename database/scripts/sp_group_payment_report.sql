DROP PROCEDURE IF EXISTS sp_group_payment_report;

DELIMITER $$

CREATE PROCEDURE sp_group_payment_report(
    IN p_year INT,
    IN p_school_id BIGINT UNSIGNED,
    IN p_training_group_id BIGINT UNSIGNED
)
BEGIN
    SELECT
        tg.name AS grupo,
        v.training_group_id,
        v.payment_year AS year,

        COUNT(DISTINCT v.inscription_id) AS total_inscriptions,

        ROUND(SUM(CASE
            WHEN v.is_monthly = 1 THEN v.report_amount
            ELSE 0
        END), 2) AS total_raised,

        ROUND(SUM(CASE
            WHEN v.is_enrollment = 1 THEN v.report_amount
            ELSE 0
        END), 2) AS total_enrollment,

        SUM(CASE
            WHEN v.is_monthly = 1 AND v.sums_in_reports = 1 THEN 1
            ELSE 0
        END) AS monthly_payments_paid,

        SUM(CASE
            WHEN v.is_monthly = 1 AND v.status_code = 2 THEN 1
            ELSE 0
        END) AS monthly_payments_debt,

        SUM(CASE
            WHEN v.is_monthly = 1 AND v.status_code = 8 THEN 1
            ELSE 0
        END) AS monthly_payments_scholarship,

        SUM(CASE
            WHEN v.is_monthly = 1 AND v.status_code IN (4,14) THEN 1
            ELSE 0
        END) AS monthly_payments_others,

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
        ) AS percentage_compliance

    FROM vw_payments_report_detail v
    INNER JOIN training_groups tg
        ON tg.id = v.training_group_id
    WHERE
        (p_year IS NULL OR v.payment_year = p_year)
        AND (p_school_id IS NULL OR v.school_id = p_school_id)
        AND (p_training_group_id IS NULL OR v.training_group_id = p_training_group_id)
    GROUP BY
        tg.id,
        tg.name,
        v.training_group_id,
        v.payment_year
    ORDER BY
        tg.id DESC,
        v.payment_year DESC,
        total_raised DESC;
END$$

DELIMITER ;