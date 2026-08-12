-- Normaliza el prefijo heredado Categoria- al formato corto CAT-.
--
-- Alcance:
--   - Solo escuelas con CATEGORY_FORMAT = birth_year.
--   - Incluye registros eliminados logicamente porque no filtra deleted_at.
--   - No modifica categorias SUB-* ni categorias de otros dominios.
--   - Es idempotente: puede ejecutarse nuevamente sin cambiar valores CAT-*.
--
-- Despues de ejecutarlo, invalida las caches de la aplicacion:
--   docker exec php php artisan cache:clear

DROP TEMPORARY TABLE IF EXISTS tmp_birth_year_category_schools;

CREATE TEMPORARY TABLE tmp_birth_year_category_schools (
    school_id BIGINT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE = MEMORY;

INSERT IGNORE INTO tmp_birth_year_category_schools (school_id)
SELECT school_id
FROM setting_values
WHERE setting_key = 'CATEGORY_FORMAT'
  AND value = 'birth_year';

-- Vista previa: todos estos conteos deben quedar en cero despues del COMMIT.
SELECT 'players.category' AS field_name, COUNT(*) AS rows_to_update
FROM players p
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = p.school_id
WHERE p.category LIKE 'Categoria-%'
UNION ALL
SELECT 'inscriptions.category', COUNT(*)
FROM inscriptions i
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = i.school_id
WHERE i.category LIKE 'Categoria-%'
UNION ALL
SELECT 'training_groups.category', COUNT(*)
FROM training_groups tg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = tg.school_id
WHERE tg.category LIKE '%Categoria-%'
UNION ALL
SELECT 'competition_groups.category', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE cg.category LIKE '%Categoria-%'
UNION ALL
SELECT 'competition_groups.year', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE cg.year LIKE 'Categoria-%'
UNION ALL
SELECT 'competition_groups.categories', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE CAST(cg.categories AS CHAR) LIKE '%Categoria-%';

START TRANSACTION;

UPDATE players p
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = p.school_id
SET p.category = CONCAT('CAT-', SUBSTRING(p.category, CHAR_LENGTH('Categoria-') + 1))
WHERE p.category LIKE 'Categoria-%';

UPDATE inscriptions i
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = i.school_id
SET i.category = CONCAT('CAT-', SUBSTRING(i.category, CHAR_LENGTH('Categoria-') + 1))
WHERE i.category LIKE 'Categoria-%';

UPDATE training_groups tg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = tg.school_id
SET tg.category = REPLACE(tg.category, 'Categoria-', 'CAT-')
WHERE tg.category LIKE '%Categoria-%';

UPDATE competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
SET
    cg.categories = REPLACE(CAST(cg.categories AS CHAR), 'Categoria-', 'CAT-'),
    cg.category = REPLACE(cg.category, 'Categoria-', 'CAT-'),
    cg.year = REPLACE(cg.year, 'Categoria-', 'CAT-')
WHERE cg.category LIKE '%Categoria-%'
   OR cg.year LIKE 'Categoria-%'
   OR CAST(cg.categories AS CHAR) LIKE '%Categoria-%';

COMMIT;

-- Verificacion: el resultado esperado es cero en todas las filas.
SELECT 'players.category' AS field_name, COUNT(*) AS remaining_rows
FROM players p
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = p.school_id
WHERE p.category LIKE 'Categoria-%'
UNION ALL
SELECT 'inscriptions.category', COUNT(*)
FROM inscriptions i
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = i.school_id
WHERE i.category LIKE 'Categoria-%'
UNION ALL
SELECT 'training_groups.category', COUNT(*)
FROM training_groups tg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = tg.school_id
WHERE tg.category LIKE '%Categoria-%'
UNION ALL
SELECT 'competition_groups.category', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE cg.category LIKE '%Categoria-%'
UNION ALL
SELECT 'competition_groups.year', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE cg.year LIKE 'Categoria-%'
UNION ALL
SELECT 'competition_groups.categories', COUNT(*)
FROM competition_groups cg
INNER JOIN tmp_birth_year_category_schools s ON s.school_id = cg.school_id
WHERE CAST(cg.categories AS CHAR) LIKE '%Categoria-%';

DROP TEMPORARY TABLE IF EXISTS tmp_birth_year_category_schools;
