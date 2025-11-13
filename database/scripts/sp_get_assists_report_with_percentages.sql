CREATE PROCEDURE sp_get_assists_report_with_percentages (
    IN p_year INTEGER,
    IN p_month TINYINT,
    IN p_training_group_id BIGINT UNSIGNED,
    IN p_school_id BIGINT UNSIGNED
)
BEGIN
    /*
      Reporte por training_group_id, school_id, year, month.
      Los porcentajes se calculan sobre total_valids (solo columnas assistance_ != NULL).
      Valores:
        1 => Asistencia
        2 => Falta
        3 => Excusa
        4 => Retiro
        5 => Incapacidad
    */

    SELECT
        t.training_group_id,
        t.school_id,
        t.year,
        t.month,
        t.total_registrations,
        t.total_valids,
        t.total_attendances,
        t.total_absences,
        t.total_excuses,
        t.total_retreat,
        t.total_disabilities,
        -- porcentajes calculados sobre total_valids (NULLIF para evitar division por cero)
        COALESCE(ROUND(100 * t.total_attendances  / NULLIF(t.total_valids, 0), 2), 0) AS percentage_attendances,
        COALESCE(ROUND(100 * t.total_absences      / NULLIF(t.total_valids, 0), 2), 0) AS percentage_absences,
        COALESCE(ROUND(100 * t.total_excuses    / NULLIF(t.total_valids, 0), 2), 0) AS percentage_excuses,
        COALESCE(ROUND(100 * t.total_retreat    / NULLIF(t.total_valids, 0), 2), 0) AS percentage_retreat,
        COALESCE(ROUND(100 * t.total_disabilities / NULLIF(t.total_valids, 0), 2), 0) AS percentage_disabilities
    FROM (
        SELECT
            a.training_group_id,
            a.school_id,
            a.year,
            a.month,
            COUNT(*) AS total_registrations,

            /* total_valids = suma de columnas assistance_* que NO son NULL */
            SUM(
                IF(assistance_one IS NOT NULL,1,0)  + IF(assistance_two IS NOT NULL,1,0)  +
                IF(assistance_three IS NOT NULL,1,0)+ IF(assistance_four IS NOT NULL,1,0) +
                IF(assistance_five IS NOT NULL,1,0) + IF(assistance_six IS NOT NULL,1,0)  +
                IF(assistance_seven IS NOT NULL,1,0)+ IF(assistance_eight IS NOT NULL,1,0)+
                IF(assistance_nine IS NOT NULL,1,0) + IF(assistance_ten IS NOT NULL,1,0)   +
                IF(assistance_eleven IS NOT NULL,1,0)+ IF(assistance_twelve IS NOT NULL,1,0)+
                IF(assistance_thirteen IS NOT NULL,1,0)+IF(assistance_fourteen IS NOT NULL,1,0)+
                IF(assistance_fifteen IS NOT NULL,1,0)+IF(assistance_sixteen IS NOT NULL,1,0)+
                IF(assistance_seventeen IS NOT NULL,1,0)+IF(assistance_eighteen IS NOT NULL,1,0)+
                IF(assistance_nineteen IS NOT NULL,1,0)+IF(assistance_twenty IS NOT NULL,1,0)+
                IF(assistance_twenty_one IS NOT NULL,1,0)+IF(assistance_twenty_two IS NOT NULL,1,0)+
                IF(assistance_twenty_three IS NOT NULL,1,0)+IF(assistance_twenty_four IS NOT NULL,1,0)+
                IF(assistance_twenty_five IS NOT NULL,1,0)
            ) AS total_valids,

            /* totales por tipo */
            SUM(
                IF(assistance_one = 1,1,0)  + IF(assistance_two = 1,1,0)  +
                IF(assistance_three = 1,1,0)+ IF(assistance_four = 1,1,0) +
                IF(assistance_five = 1,1,0) + IF(assistance_six = 1,1,0)  +
                IF(assistance_seven = 1,1,0)+ IF(assistance_eight = 1,1,0)+
                IF(assistance_nine = 1,1,0) + IF(assistance_ten = 1,1,0)   +
                IF(assistance_eleven = 1,1,0)+ IF(assistance_twelve = 1,1,0)+
                IF(assistance_thirteen = 1,1,0)+IF(assistance_fourteen = 1,1,0)+
                IF(assistance_fifteen = 1,1,0)+IF(assistance_sixteen = 1,1,0)+
                IF(assistance_seventeen = 1,1,0)+IF(assistance_eighteen = 1,1,0)+
                IF(assistance_nineteen = 1,1,0)+IF(assistance_twenty = 1,1,0)+
                IF(assistance_twenty_one = 1,1,0)+IF(assistance_twenty_two = 1,1,0)+
                IF(assistance_twenty_three = 1,1,0)+IF(assistance_twenty_four = 1,1,0)+
                IF(assistance_twenty_five = 1,1,0)
            ) AS total_attendances,

            SUM(
                IF(assistance_one = 2,1,0)  + IF(assistance_two = 2,1,0)  +
                IF(assistance_three = 2,1,0)+ IF(assistance_four = 2,1,0) +
                IF(assistance_five = 2,1,0) + IF(assistance_six = 2,1,0)  +
                IF(assistance_seven = 2,1,0)+ IF(assistance_eight = 2,1,0)+
                IF(assistance_nine = 2,1,0) + IF(assistance_ten = 2,1,0)   +
                IF(assistance_eleven = 2,1,0)+ IF(assistance_twelve = 2,1,0)+
                IF(assistance_thirteen = 2,1,0)+IF(assistance_fourteen = 2,1,0)+
                IF(assistance_fifteen = 2,1,0)+IF(assistance_sixteen = 2,1,0)+
                IF(assistance_seventeen = 2,1,0)+IF(assistance_eighteen = 2,1,0)+
                IF(assistance_nineteen = 2,1,0)+IF(assistance_twenty = 2,1,0)+
                IF(assistance_twenty_one = 2,1,0)+IF(assistance_twenty_two = 2,1,0)+
                IF(assistance_twenty_three = 2,1,0)+IF(assistance_twenty_four = 2,1,0)+
                IF(assistance_twenty_five = 2,1,0)
            ) AS total_absences,

            SUM(
                IF(assistance_one = 3,1,0)  + IF(assistance_two = 3,1,0)  +
                IF(assistance_three = 3,1,0)+ IF(assistance_four = 3,1,0) +
                IF(assistance_five = 3,1,0) + IF(assistance_six = 3,1,0)  +
                IF(assistance_seven = 3,1,0)+ IF(assistance_eight = 3,1,0)+
                IF(assistance_nine = 3,1,0) + IF(assistance_ten = 3,1,0)   +
                IF(assistance_eleven = 3,1,0)+ IF(assistance_twelve = 3,1,0)+
                IF(assistance_thirteen = 3,1,0)+IF(assistance_fourteen = 3,1,0)+
                IF(assistance_fifteen = 3,1,0)+IF(assistance_sixteen = 3,1,0)+
                IF(assistance_seventeen = 3,1,0)+IF(assistance_eighteen = 3,1,0)+
                IF(assistance_nineteen = 3,1,0)+IF(assistance_twenty = 3,1,0)+
                IF(assistance_twenty_one = 3,1,0)+IF(assistance_twenty_two = 3,1,0)+
                IF(assistance_twenty_three = 3,1,0)+IF(assistance_twenty_four = 3,1,0)+
                IF(assistance_twenty_five = 3,1,0)
            ) AS total_excuses,

            SUM(
                IF(assistance_one = 4,1,0)  + IF(assistance_two = 4,1,0)  +
                IF(assistance_three = 4,1,0)+ IF(assistance_four = 4,1,0) +
                IF(assistance_five = 4,1,0) + IF(assistance_six = 4,1,0)  +
                IF(assistance_seven = 4,1,0)+ IF(assistance_eight = 4,1,0)+
                IF(assistance_nine = 4,1,0) + IF(assistance_ten = 4,1,0)   +
                IF(assistance_eleven = 4,1,0)+ IF(assistance_twelve = 4,1,0)+
                IF(assistance_thirteen = 4,1,0)+IF(assistance_fourteen = 4,1,0)+
                IF(assistance_fifteen = 4,1,0)+IF(assistance_sixteen = 4,1,0)+
                IF(assistance_seventeen = 4,1,0)+IF(assistance_eighteen = 4,1,0)+
                IF(assistance_nineteen = 4,1,0)+IF(assistance_twenty = 4,1,0)+
                IF(assistance_twenty_one = 4,1,0)+IF(assistance_twenty_two = 4,1,0)+
                IF(assistance_twenty_three = 4,1,0)+IF(assistance_twenty_four = 4,1,0)+
                IF(assistance_twenty_five = 4,1,0)
            ) AS total_retreat,

            SUM(
                IF(assistance_one = 5,1,0)  + IF(assistance_two = 5,1,0)  +
                IF(assistance_three = 5,1,0)+ IF(assistance_four = 5,1,0) +
                IF(assistance_five = 5,1,0) + IF(assistance_six = 5,1,0)  +
                IF(assistance_seven = 5,1,0)+ IF(assistance_eight = 5,1,0)+
                IF(assistance_nine = 5,1,0) + IF(assistance_ten = 5,1,0)   +
                IF(assistance_eleven = 5,1,0)+ IF(assistance_twelve = 5,1,0)+
                IF(assistance_thirteen = 5,1,0)+IF(assistance_fourteen = 5,1,0)+
                IF(assistance_fifteen = 5,1,0)+IF(assistance_sixteen = 5,1,0)+
                IF(assistance_seventeen = 5,1,0)+IF(assistance_eighteen = 5,1,0)+
                IF(assistance_nineteen = 5,1,0)+IF(assistance_twenty = 5,1,0)+
                IF(assistance_twenty_one = 5,1,0)+IF(assistance_twenty_two = 5,1,0)+
                IF(assistance_twenty_three = 5,1,0)+IF(assistance_twenty_four = 5,1,0)+
                IF(assistance_twenty_five = 5,1,0)
            ) AS total_disabilities

        FROM assists a
        WHERE
            (p_year IS NULL OR a.year = p_year) AND
            (p_month IS NULL OR a.month = p_month) AND
            (p_training_group_id IS NULL OR a.training_group_id = p_training_group_id) AND
            (p_school_id IS NULL OR a.school_id = p_school_id)
        GROUP BY a.training_group_id, a.school_id, a.year, a.month
    ) AS t
    ORDER BY t.year DESC, t.month DESC, t.training_group_id DESC;
END
