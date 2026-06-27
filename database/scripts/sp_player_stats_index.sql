DROP PROCEDURE IF EXISTS sp_player_stats;

DELIMITER $$

CREATE OR REPLACE PROCEDURE sp_player_stats(
    IN p_school_id BIGINT,
    IN p_year INT,
    IN p_position VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_player_id BIGINT,
    IN p_category VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_limit_rows INT
)
BEGIN
    DECLARE v_limit_rows INT DEFAULT 10;

    IF p_school_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El parámetro p_school_id es obligatorio';
    END IF;

    SET v_limit_rows = GREATEST(IFNULL(p_limit_rows, 10), 1);

    SELECT
        i.player_id,
        CONCAT(p.names, ' ', p.last_names) AS player_name,
        p.photo,

        COUNT(sc.id) AS total_partidos,
        COALESCE(SUM(sc.assistance), 0) AS asistencias_partidos,
        COALESCE(SUM(sc.titular), 0) AS veces_titular,

        ROUND(
            AVG(
                CASE
                    WHEN sc.qualification REGEXP '^[0-9]+([.][0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(10, 2))
                    ELSE 0
                END
            ),
            2
        ) AS promedio_calificacion,

        COALESCE(SUM(sc.goals), 0) AS total_goles,

        ROUND(
            CASE
                WHEN COALESCE(SUM(sc.assistance), 0) > 0
                    THEN COALESCE(SUM(sc.goals), 0) / SUM(sc.assistance)
                ELSE 0
            END,
            2
        ) AS promedio_goles_partido,

        COALESCE(SUM(sc.yellow_cards), 0) AS total_amarillas,
        ROUND(COALESCE(AVG(sc.yellow_cards), 0), 2) AS promedio_amarillas_partido,

        COALESCE(SUM(sc.red_cards), 0) AS total_rojas,
        ROUND(COALESCE(AVG(sc.red_cards), 0), 2) AS promedio_rojas_partido,

        COALESCE(SUM(sc.played_approx), 0) AS minutos_jugados,

        ROUND(
            CASE
                WHEN COALESCE(SUM(sc.assistance), 0) > 0
                    THEN COALESCE(SUM(sc.played_approx), 0) / SUM(sc.assistance)
                ELSE 0
            END,
            2
        ) AS promedio_minutos_partido,

        COALESCE(SUM(sc.goal_assists), 0) AS total_asistencias_gol,
        COALESCE(SUM(sc.goal_saves), 0) AS total_atajadas,

        (
            SELECT sc2.position
            FROM skills_control sc2
            INNER JOIN games g2
                ON g2.id = sc2.game_id
            INNER JOIN inscriptions i2
                ON i2.id = sc2.inscription_id
            WHERE i2.player_id = i.player_id
              AND sc2.deleted_at IS NULL
              AND g2.deleted_at IS NULL
              AND g2.status = 'played'
              AND sc2.assistance = 1
              AND sc2.school_id = p_school_id
              AND sc2.position IS NOT NULL
              AND (p_year IS NULL OR i2.year = p_year)
              AND (
                    p_category IS NULL
                    OR p_category = ''
                    OR i2.category COLLATE utf8mb4_unicode_ci = p_category
                  )
            GROUP BY sc2.position
            ORDER BY COUNT(*) DESC, sc2.position ASC
            LIMIT 1
        ) AS posicion_principal,

        (
            COALESCE(SUM(sc.goals), 0) * 10
            + COALESCE(SUM(sc.goal_assists), 0) * 7
            + COALESCE(SUM(sc.goal_saves), 0) * 5
            + ROUND(
                AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+([.][0-9]+)?$'
                            THEN CAST(sc.qualification AS DECIMAL(10, 2))
                        ELSE 0
                    END
                ),
                2
            ) * 3
            + (COALESCE(SUM(sc.played_approx), 0) * 0.1)
            + COALESCE(SUM(CASE WHEN sc.titular = 1 THEN 3 ELSE 0 END), 0)
            - (COALESCE(SUM(sc.yellow_cards), 0) * 2)
            - (COALESCE(SUM(sc.red_cards), 0) * 5)
        ) AS puntaje_escalafon

    FROM skills_control sc
    INNER JOIN games g
        ON g.id = sc.game_id
    INNER JOIN inscriptions i
        ON i.id = sc.inscription_id
    INNER JOIN players p
        ON p.id = i.player_id

    WHERE sc.deleted_at IS NULL
      AND g.deleted_at IS NULL
      AND g.status = 'played'
      AND sc.assistance = 1
      AND sc.school_id = p_school_id
      AND (p_year IS NULL OR i.year = p_year)
      AND (p_player_id IS NULL OR i.player_id = p_player_id)
      AND (
            p_position IS NULL
            OR p_position = ''
            OR sc.position COLLATE utf8mb4_unicode_ci = p_position
          )
      AND (
            p_category IS NULL
            OR p_category = ''
            OR i.category COLLATE utf8mb4_unicode_ci = p_category
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