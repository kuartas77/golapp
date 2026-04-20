CREATE OR REPLACE VIEW vw_attendance_monthly_report_detail AS
SELECT
    a.school_id,
    a.training_group_id,
    a.inscription_id,
    a.year,
    a.month,
    SUM(
        CASE WHEN a.assistance_one = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_two = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_three = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_four = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_five = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_six = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_seven = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eight = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_nine = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_ten = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eleven = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twelve = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_thirteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_fourteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_fifteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_sixteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_seventeen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eighteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_nineteen = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_one = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_two = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_three = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_four = 1 THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_five = 1 THEN 1 ELSE 0 END
    ) AS total_attendances,
    SUM(
        CASE WHEN a.assistance_one IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_two IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_three IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_four IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_five IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_six IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_seven IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eight IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_nine IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_ten IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eleven IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twelve IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_thirteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_fourteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_fifteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_sixteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_seventeen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_eighteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_nineteen IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_one IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_two IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_three IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_four IS NOT NULL THEN 1 ELSE 0 END +
        CASE WHEN a.assistance_twenty_five IS NOT NULL THEN 1 ELSE 0 END
    ) AS total_sessions_registered
FROM assists a
WHERE a.deleted_at IS NULL
GROUP BY
    a.school_id,
    a.training_group_id,
    a.inscription_id,
    a.year,
    a.month;
