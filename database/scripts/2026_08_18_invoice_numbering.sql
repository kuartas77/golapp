-- Numeración interna y electrónica de facturas por escuela.
-- Compatible con MariaDB. Ejecute este archivo por sentencias/bloques, no como una sentencia preparada única.
-- IMPORTANTE: tome un respaldo y use una ventana controlada. Las sentencias DDL de MariaDB hacen COMMIT implícito.
-- El script es idempotente y NO modifica la tabla `migrations` de Laravel.

-- Preflight: estas consultas no modifican datos.
SELECT COUNT(*) AS schools_total FROM schools;
SELECT COUNT(*) AS invoices_total FROM invoices;
SELECT COUNT(*) AS invoices_without_school FROM invoices WHERE school_id IS NULL;
SELECT school_id, invoice_number, COUNT(*) AS duplicate_total
FROM invoices
GROUP BY school_id, invoice_number
HAVING COUNT(*) > 1;

CREATE TABLE IF NOT EXISTS school_invoice_sequences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    next_number BIGINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY school_invoice_sequences_school_id_unique (school_id),
    CONSTRAINT school_invoice_sequences_school_id_foreign
        FOREIGN KEY (school_id) REFERENCES schools (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_number_ranges (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    resolution_number VARCHAR(100) NOT NULL,
    resolution_date DATE NOT NULL,
    prefix VARCHAR(4) NULL,
    range_start BIGINT UNSIGNED NOT NULL,
    range_end BIGINT UNSIGNED NOT NULL,
    next_number BIGINT UNSIGNED NOT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    technical_key TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    active_slot TINYINT UNSIGNED NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY invoice_ranges_one_active_per_school (school_id, active_slot),
    KEY invoice_ranges_school_prefix_index (school_id, prefix),
    CONSTRAINT invoice_number_ranges_school_id_foreign
        FOREIGN KEY (school_id) REFERENCES schools (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

DROP PROCEDURE IF EXISTS apply_invoice_numbering_schema$$
CREATE PROCEDURE apply_invoice_numbering_schema()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'schools'
          AND COLUMN_NAME = 'electronic_invoicing_enabled'
    ) THEN
        ALTER TABLE schools
            ADD COLUMN electronic_invoicing_enabled TINYINT(1) NOT NULL DEFAULT 0;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND COLUMN_NAME = 'numbering_type'
    ) THEN
        ALTER TABLE invoices
            ADD COLUMN numbering_type VARCHAR(20) NOT NULL DEFAULT 'legacy' AFTER invoice_number;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND COLUMN_NAME = 'consecutive_number'
    ) THEN
        ALTER TABLE invoices
            ADD COLUMN consecutive_number BIGINT UNSIGNED NULL AFTER numbering_type;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND COLUMN_NAME = 'invoice_number_range_id'
    ) THEN
        ALTER TABLE invoices
            ADD COLUMN invoice_number_range_id BIGINT UNSIGNED NULL AFTER consecutive_number;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND CONSTRAINT_NAME = 'invoices_invoice_number_range_id_foreign'
    ) THEN
        ALTER TABLE invoices
            ADD CONSTRAINT invoices_invoice_number_range_id_foreign
            FOREIGN KEY (invoice_number_range_id) REFERENCES invoice_number_ranges (id);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND INDEX_NAME = 'invoices_invoice_number_unique'
    ) THEN
        ALTER TABLE invoices DROP INDEX invoices_invoice_number_unique;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices'
          AND INDEX_NAME = 'invoices_school_invoice_number_unique'
    ) THEN
        ALTER TABLE invoices
            ADD UNIQUE KEY invoices_school_invoice_number_unique (school_id, invoice_number);
    END IF;
END$$

CALL apply_invoice_numbering_schema()$$
DROP PROCEDURE IF EXISTS apply_invoice_numbering_schema$$

DELIMITER ;

-- Backfill histórico. No cambia invoice_number ni intenta reconstruir consecutivos aleatorios anteriores.
UPDATE invoices
SET numbering_type = 'legacy'
WHERE numbering_type IS NULL OR numbering_type = '';

-- Semilla: todas las facturas cuentan, incluso las eliminadas lógicamente.
-- INSERT IGNORE garantiza que una segunda ejecución no recalcule ni reduzca contadores existentes.
INSERT IGNORE INTO school_invoice_sequences (school_id, next_number, created_at, updated_at)
SELECT
    s.id,
    COUNT(i.id) + 1,
    NOW(),
    NOW()
FROM schools s
LEFT JOIN invoices i ON i.school_id = s.id
GROUP BY s.id;

-- Verificación final.
SELECT
    s.id AS school_id,
    s.name AS school_name,
    COUNT(i.id) AS historical_invoices,
    sis.next_number,
    CONCAT('FAC-', LPAD(sis.next_number, GREATEST(6, CHAR_LENGTH(sis.next_number)), '0')) AS next_internal_invoice
FROM schools s
LEFT JOIN invoices i ON i.school_id = s.id
LEFT JOIN school_invoice_sequences sis ON sis.school_id = s.id
GROUP BY s.id, s.name, sis.next_number
ORDER BY s.id;

SELECT numbering_type, COUNT(*) AS total
FROM invoices
GROUP BY numbering_type;

SELECT INDEX_NAME, NON_UNIQUE, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_list
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'invoices'
  AND INDEX_NAME IN ('invoices_invoice_number_unique', 'invoices_school_invoice_number_unique')
GROUP BY INDEX_NAME, NON_UNIQUE;
