DROP PROCEDURE IF EXISTS sp_player_stats;

DELIMITER $$

CREATE PROCEDURE sp_player_stats(
    IN p_school_id BIGINT,
    IN p_year INT,
    IN p_position VARCHAR(255),
    IN p_player_id BIGINT,
    IN p_category VARCHAR(255),
    IN p_limit_rows INT
)
BEGIN
    DECLARE v_limit_rows INT DEFAULT 10;

    IF p_school_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El parámetro p_school_id es obligatorio';
    END IF;

    SET v_limit_rows = IFNULL(p_limit_rows, 10);

    SELECT
        i.player_id,
        CONCAT(p.names, ' ', p.last_names) AS player_name,
        p.photo,
        COUNT(sc.id) AS total_partidos,
        SUM(sc.assistance) AS asistencias_partidos,
        SUM(sc.titular) AS veces_titular,
        ROUND(
            AVG(
                CASE
                    WHEN sc.qualification REGEXP '^[0-9]+(\\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(10,2))
                    ELSE 0
                END
            ),
            2
        ) AS promedio_calificacion,
        SUM(sc.goals) AS total_goles,
        ROUND(
            CASE
                WHEN SUM(sc.assistance) > 0
                    THEN SUM(sc.goals) / SUM(sc.assistance)
                ELSE 0
            END,
            2
        ) AS promedio_goles_partido,
        SUM(sc.yellow_cards) AS total_amarillas,
        ROUND(AVG(sc.yellow_cards), 2) AS promedio_amarillas_partido,
        SUM(sc.red_cards) AS total_rojas,
        ROUND(AVG(sc.red_cards), 2) AS promedio_rojas_partido,
        SUM(sc.played_approx) AS minutos_jugados,
        ROUND(
            CASE
                WHEN SUM(sc.assistance) > 0
                    THEN SUM(sc.played_approx) / SUM(sc.assistance)
                ELSE 0
            END,
            2
        ) AS promedio_minutos_partido,
        SUM(sc.goal_assists) AS total_asistencias_gol,
        SUM(sc.goal_saves) AS total_atajadas,

        (
            SELECT sc2.position
            FROM skills_control sc2
            INNER JOIN inscriptions i2
                ON sc2.inscription_id = i2.id
            WHERE i2.player_id = i.player_id
              AND sc2.deleted_at IS NULL
              AND sc2.assistance = 1
              AND sc2.school_id = p_school_id
              AND sc2.position IS NOT NULL
              AND (p_year IS NULL OR i2.year = p_year)
              AND (
                p_category IS NULL
                OR p_category = ''
                OR i2.category COLLATE utf8mb4_unicode_ci = p_category COLLATE utf8mb4_unicode_ci
            )
            GROUP BY sc2.position
            ORDER BY COUNT(*) DESC, sc2.position ASC
            LIMIT 1
        ) AS posicion_principal,

        (
            SUM(sc.goals) * 10 +
            SUM(sc.goal_assists) * 7 +
            SUM(sc.goal_saves) * 5 +
            ROUND(
                AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\\.[0-9]+)?$'
                            THEN CAST(sc.qualification AS DECIMAL(10,2))
                        ELSE 0
                    END
                ),
                2
            ) * 3 +
            (SUM(sc.played_approx) * 0.1) +
            SUM(CASE WHEN sc.titular = 1 THEN 3 ELSE 0 END) -
            SUM(sc.yellow_cards) * 2 -
            SUM(sc.red_cards) * 5
        ) AS puntaje_escalafon

    FROM skills_control sc
    INNER JOIN inscriptions i
        ON sc.inscription_id = i.id
    INNER JOIN players p
        ON i.player_id = p.id

    WHERE sc.deleted_at IS NULL
      AND sc.assistance = 1
      AND sc.school_id = p_school_id
      AND (p_year IS NULL OR i.year = p_year)
      AND (p_player_id IS NULL OR i.player_id = p_player_id)
        AND (
            p_position IS NULL
            OR p_position = ''
            OR sc.position COLLATE utf8mb4_unicode_ci = p_position COLLATE utf8mb4_unicode_ci
        )
        AND (
            p_category IS NULL
            OR p_category = ''
            OR i.category COLLATE utf8mb4_unicode_ci = p_category COLLATE utf8mb4_unicode_ci
        )

    GROUP BY
        i.player_id,
        p.names,
        p.last_names,
        p.photo

    ORDER BY puntaje_escalafon DESC
    LIMIT v_limit_rows;
END $$

DELIMITER ;