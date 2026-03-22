CREATE OR REPLACE VIEW vw_assists_detail AS
SELECT
    a.id AS assist_id,
    a.training_group_id,
    a.inscription_id,
    a.school_id,
    a.year,
    a.month,
    jt.session_number,
    jt.status_id,
    CASE jt.status_id
        WHEN 1 THEN 'Asistencia'
        WHEN 2 THEN 'Falta'
        WHEN 3 THEN 'Excusa'
        WHEN 4 THEN 'Retiro'
        WHEN 5 THEN 'Incapacidad'
    END AS status_name,
    a.observations,
    a.created_at,
    a.updated_at
FROM assists a
JOIN JSON_TABLE(
    JSON_ARRAY(
        a.assistance_one,
        a.assistance_two,
        a.assistance_three,
        a.assistance_four,
        a.assistance_five,
        a.assistance_six,
        a.assistance_seven,
        a.assistance_eight,
        a.assistance_nine,
        a.assistance_ten,
        a.assistance_eleven,
        a.assistance_twelve,
        a.assistance_thirteen,
        a.assistance_fourteen,
        a.assistance_fifteen,
        a.assistance_sixteen,
        a.assistance_seventeen,
        a.assistance_eighteen,
        a.assistance_nineteen,
        a.assistance_twenty,
        a.assistance_twenty_one,
        a.assistance_twenty_two,
        a.assistance_twenty_three,
        a.assistance_twenty_four,
        a.assistance_twenty_five
    ),
    '$[*]' COLUMNS (
        session_number FOR ORDINALITY,
        status_id INT PATH '$' NULL ON EMPTY NULL ON ERROR
    )
) jt
WHERE jt.status_id IS NOT NULL;