-- Ejecuta este archivo por bloques/sentencias, no como una sola sentencia preparada.
-- Bloque 1: detecta posiciones que no coinciden con config/variables.php: KEY_POSITIONS.
SELECT
    position,
    COUNT(*) AS total
FROM skills_control
WHERE position IS NOT NULL
  AND TRIM(position) <> ''
  AND position NOT IN (
      'Portero',
      'Defensa (Central)',
      'Defensa (Derecho)(Izquierdo)',
      'Defensa (Izquierdo)',
      'Defensa (Derecho)',
      'Defensa',
      'Volante (Defensivo Izquierdo)',
      'Volante (Defensivo Derecho)',
      'Volante (Defensivo Central)',
      'Volante (Ofensivo Izquierdo)',
      'Volante (Ofensivo Derecho)',
      'Volante (Ofensivo Central)',
      'Volante (Extremo Izquierdo)',
      'Volante (Extremo Derecho)',
      'Volante (Primera línea)',
      'Volante (Segunda línea)',
      'Volante (Primera linea)',
      'Volante (Segunda linea)',
      'Volante (Extremo)',
      'Volante (Central)',
      'Delantero (Izquierdo)',
      'Delantero (Derecho)',
      'Delantero (Central)',
      'Delantero'
  )
GROUP BY position
ORDER BY total DESC, position;

-- Bloque 2: normaliza las variantes conocidas al texto exacto de KEY_POSITIONS.
UPDATE skills_control
SET position = CASE TRIM(position)
    WHEN 'Defensa(Central)' THEN 'Defensa (Central)'
    WHEN 'Defensa(Derecho)(Izquierdo)' THEN 'Defensa (Derecho)(Izquierdo)'
    WHEN 'Defensa(Izquierdo)' THEN 'Defensa (Izquierdo)'
    WHEN 'Defensa(Derecho)' THEN 'Defensa (Derecho)'
    WHEN 'Volante(Defensivo Izquierdo)' THEN 'Volante (Defensivo Izquierdo)'
    WHEN 'Volante(Defensivo Derecho)' THEN 'Volante (Defensivo Derecho)'
    WHEN 'Volante(Defensivo Central)' THEN 'Volante (Defensivo Central)'
    WHEN 'Volante(Ofensivo Izquierdo)' THEN 'Volante (Ofensivo Izquierdo)'
    WHEN 'Volante(Ofensivo Derecho)' THEN 'Volante (Ofensivo Derecho)'
    WHEN 'Volante(Ofensivo Central)' THEN 'Volante (Ofensivo Central)'
    WHEN 'Volante(Extremo Izquierdo)' THEN 'Volante (Extremo Izquierdo)'
    WHEN 'Volante(Extremo Derecho)' THEN 'Volante (Extremo Derecho)'
    WHEN 'Volante(Primera línea)' THEN 'Volante (Primera línea)'
    WHEN 'Volante(Segunda línea)' THEN 'Volante (Segunda línea)'
    WHEN 'Volante(Primera linea)' THEN 'Volante (Primera linea)'
    WHEN 'Volante(Segunda linea)' THEN 'Volante (Segunda linea)'
    WHEN 'Volante(Extremo)' THEN 'Volante (Extremo)'
    WHEN 'Volante(Central)' THEN 'Volante (Central)'
    WHEN 'Delantero(Izquierdo)' THEN 'Delantero (Izquierdo)'
    WHEN 'Delantero(Derecho)' THEN 'Delantero (Derecho)'
    WHEN 'Delantero(Central)' THEN 'Delantero (Central)'
    ELSE TRIM(position)
END
WHERE position IS NOT NULL
  AND TRIM(position) IN (
      'Defensa(Central)',
      'Defensa(Derecho)(Izquierdo)',
      'Defensa(Izquierdo)',
      'Defensa(Derecho)',
      'Volante(Defensivo Izquierdo)',
      'Volante(Defensivo Derecho)',
      'Volante(Defensivo Central)',
      'Volante(Ofensivo Izquierdo)',
      'Volante(Ofensivo Derecho)',
      'Volante(Ofensivo Central)',
      'Volante(Extremo Izquierdo)',
      'Volante(Extremo Derecho)',
      'Volante(Primera línea)',
      'Volante(Segunda línea)',
      'Volante(Primera linea)',
      'Volante(Segunda linea)',
      'Volante(Extremo)',
      'Volante(Central)',
      'Delantero(Izquierdo)',
      'Delantero(Derecho)',
      'Delantero(Central)'
  );

-- Bloque 3: verifica si aún quedan valores fuera del listado.
SELECT
    position,
    COUNT(*) AS total
FROM skills_control
WHERE position IS NOT NULL
  AND TRIM(position) <> ''
  AND position NOT IN (
      'Portero',
      'Defensa (Central)',
      'Defensa (Derecho)(Izquierdo)',
      'Defensa (Izquierdo)',
      'Defensa (Derecho)',
      'Defensa',
      'Volante (Defensivo Izquierdo)',
      'Volante (Defensivo Derecho)',
      'Volante (Defensivo Central)',
      'Volante (Ofensivo Izquierdo)',
      'Volante (Ofensivo Derecho)',
      'Volante (Ofensivo Central)',
      'Volante (Extremo Izquierdo)',
      'Volante (Extremo Derecho)',
      'Volante (Primera línea)',
      'Volante (Segunda línea)',
      'Volante (Primera linea)',
      'Volante (Segunda linea)',
      'Volante (Extremo)',
      'Volante (Central)',
      'Delantero (Izquierdo)',
      'Delantero (Derecho)',
      'Delantero (Central)',
      'Delantero'
  )
GROUP BY position
ORDER BY total DESC, position;
