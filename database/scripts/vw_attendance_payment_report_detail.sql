CREATE OR REPLACE VIEW vw_attendance_payment_report_detail AS
SELECT
    a.school_id,
    a.training_group_id,
    a.inscription_id,
    a.year,
    a.month,
    a.total_attendances,
    a.total_sessions_registered,
    p.payment_id,
    p.status_code AS payment_status_code,
    COALESCE(p.status_label, 'Sin registro') AS payment_status_label,
    CASE
        WHEN a.total_attendances > 0 THEN 1
        ELSE 0
    END AS has_attendance,
    CASE
        WHEN a.total_attendances <= 0 THEN 0
        WHEN p.payment_id IS NULL THEN 1
        WHEN p.status_code IN (2, 3, 13) THEN 1
        ELSE 0
    END AS is_flagged,
    CASE
        WHEN a.total_attendances <= 0 THEN NULL
        WHEN p.payment_id IS NULL THEN 'Sin registro de mensualidad'
        WHEN p.status_code = 2 THEN 'Asistió con mensualidad en deuda'
        WHEN p.status_code = 3 THEN 'Asistió con mensualidad en abono'
        WHEN p.status_code = 13 THEN 'Asistió con acuerdo de pago'
        ELSE NULL
    END AS flag_reason
FROM vw_attendance_monthly_report_detail a
LEFT JOIN vw_payments_report_detail p
    ON p.school_id = a.school_id
    AND p.training_group_id = a.training_group_id
    AND p.inscription_id = a.inscription_id
    AND p.payment_year = a.year
    AND p.month_number = a.month
    AND p.is_monthly = 1
    AND p.deleted_at IS NULL;
