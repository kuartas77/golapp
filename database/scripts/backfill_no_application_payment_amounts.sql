-- Backfill monthly payment amount columns for months marked as "No Aplica" (14).
-- Safe to run more than once: only months with status 14 are forced to amount 0.

START TRANSACTION;

SELECT COUNT(*) AS rows_to_fix
FROM payments
WHERE (january = '14' AND COALESCE(january_amount, -1) <> 0)
   OR (february = '14' AND COALESCE(february_amount, -1) <> 0)
   OR (march = '14' AND COALESCE(march_amount, -1) <> 0)
   OR (april = '14' AND COALESCE(april_amount, -1) <> 0)
   OR (may = '14' AND COALESCE(may_amount, -1) <> 0)
   OR (june = '14' AND COALESCE(june_amount, -1) <> 0)
   OR (july = '14' AND COALESCE(july_amount, -1) <> 0)
   OR (august = '14' AND COALESCE(august_amount, -1) <> 0)
   OR (september = '14' AND COALESCE(september_amount, -1) <> 0)
   OR (october = '14' AND COALESCE(october_amount, -1) <> 0)
   OR (november = '14' AND COALESCE(november_amount, -1) <> 0)
   OR (december = '14' AND COALESCE(december_amount, -1) <> 0);

UPDATE payments
SET
    january_amount = CASE WHEN january = '14' THEN 0 ELSE january_amount END,
    february_amount = CASE WHEN february = '14' THEN 0 ELSE february_amount END,
    march_amount = CASE WHEN march = '14' THEN 0 ELSE march_amount END,
    april_amount = CASE WHEN april = '14' THEN 0 ELSE april_amount END,
    may_amount = CASE WHEN may = '14' THEN 0 ELSE may_amount END,
    june_amount = CASE WHEN june = '14' THEN 0 ELSE june_amount END,
    july_amount = CASE WHEN july = '14' THEN 0 ELSE july_amount END,
    august_amount = CASE WHEN august = '14' THEN 0 ELSE august_amount END,
    september_amount = CASE WHEN september = '14' THEN 0 ELSE september_amount END,
    october_amount = CASE WHEN october = '14' THEN 0 ELSE october_amount END,
    november_amount = CASE WHEN november = '14' THEN 0 ELSE november_amount END,
    december_amount = CASE WHEN december = '14' THEN 0 ELSE december_amount END
WHERE (january = '14' AND COALESCE(january_amount, -1) <> 0)
   OR (february = '14' AND COALESCE(february_amount, -1) <> 0)
   OR (march = '14' AND COALESCE(march_amount, -1) <> 0)
   OR (april = '14' AND COALESCE(april_amount, -1) <> 0)
   OR (may = '14' AND COALESCE(may_amount, -1) <> 0)
   OR (june = '14' AND COALESCE(june_amount, -1) <> 0)
   OR (july = '14' AND COALESCE(july_amount, -1) <> 0)
   OR (august = '14' AND COALESCE(august_amount, -1) <> 0)
   OR (september = '14' AND COALESCE(september_amount, -1) <> 0)
   OR (october = '14' AND COALESCE(october_amount, -1) <> 0)
   OR (november = '14' AND COALESCE(november_amount, -1) <> 0)
   OR (december = '14' AND COALESCE(december_amount, -1) <> 0);

SELECT COUNT(*) AS remaining_rows_to_fix
FROM payments
WHERE (january = '14' AND COALESCE(january_amount, -1) <> 0)
   OR (february = '14' AND COALESCE(february_amount, -1) <> 0)
   OR (march = '14' AND COALESCE(march_amount, -1) <> 0)
   OR (april = '14' AND COALESCE(april_amount, -1) <> 0)
   OR (may = '14' AND COALESCE(may_amount, -1) <> 0)
   OR (june = '14' AND COALESCE(june_amount, -1) <> 0)
   OR (july = '14' AND COALESCE(july_amount, -1) <> 0)
   OR (august = '14' AND COALESCE(august_amount, -1) <> 0)
   OR (september = '14' AND COALESCE(september_amount, -1) <> 0)
   OR (october = '14' AND COALESCE(october_amount, -1) <> 0)
   OR (november = '14' AND COALESCE(november_amount, -1) <> 0)
   OR (december = '14' AND COALESCE(december_amount, -1) <> 0);

COMMIT;
