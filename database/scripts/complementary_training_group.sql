INSERT INTO complementary_training_group_inscription (
    school_id,
    inscription_id,
    training_group_id,
    created_at,
    updated_at
)
SELECT
    i.school_id,
    i.id AS inscription_id,
    i.complementary_group_id AS training_group_id,
    NOW() AS created_at,
    NOW() AS updated_at
FROM inscriptions i
WHERE i.complementary_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM complementary_training_group_inscription ctgi
      WHERE ctgi.inscription_id = i.id
        AND ctgi.training_group_id = i.complementary_group_id
  )
ORDER BY i.id;