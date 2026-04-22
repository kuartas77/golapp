# Módulo QR de Asistencia Rápida Mobile-First

## Resumen
Se implementará un módulo Vue nuevo y separado del tablero masivo de asistencias para toma rápida desde celular. El QR contendrá sólo `unique_code` y abrirá una ruta SPA dedicada del backoffice. El flujo exigirá autenticación previa; si el usuario no está logueado, irá a login y volverá automáticamente al QR escaneado.

La v1 permitirá uso por `instructor`, `school` y `super-admin` con permiso `school.module.attendances`. El formulario rápido sólo permitirá marcar `Asistencia` (`value = 1`) para una clase del mes actual. Los QR podrán visualizarse/descargarse desde ficha del deportista, acciones de inscripción y ficha del deportista en portal de acudientes.

## Cambios de Implementación
### UX y superficies
- Crear dos rutas Vue nuevas:
  - `/asistencias/qr`
  - `/asistencias/qr/:unique_code`
- Definir nombres de ruta `attendances-qr` y `attendances-qr-detail`.
- La pantalla será mobile-first: una sola columna, tarjetas grandes, selector táctil de clase, CTA fijo inferior, soporte `safe-area`, sin DataTable ni tablas densas.
- Mostrar en la vista rápida: foto, nombre, `unique_code`, grupo actual, mes actual, lista de clases válidas y estado actual de cada clase.
- Estados visuales de clase:
  - `hoy`
  - `seleccionada`
  - `ya marcada`
  - `disponible`
- Añadir item de menú `Asistencia QR` junto a `Asistencias`.
- La landing `/asistencias/qr` tendrá búsqueda manual por `unique_code` como fallback sin escaneo.

### Generación y exposición del QR
- Crear un componente Vue reutilizable de QR.
- El componente construirá la URL client-side con el `unique_code` ya disponible en cada superficie; no requiere endpoint adicional para “generar” el QR.
- Superficies de exposición:
  - ficha del deportista backoffice
  - acciones por fila en inscripciones
  - ficha del deportista en portal de acudientes
- Acciones del componente:
  - ver QR en modal/card
  - copiar enlace
  - descargar PNG
- El QR siempre apuntará a la URL del backoffice `/asistencias/qr/:unique_code`.
- No incluir escáner embebido en la plataforma en v1.

### Backend y resolución del contexto
- Añadir endpoints API dedicados; no reutilizar la respuesta cruda del módulo masivo.
- Resolver contexto desde `Inscription`, no desde `Player`:
  - buscar por `school_id + unique_code + year = now()->year`
  - cargar `player` y `trainingGroup`
  - buscar `Assist` por `inscription_id + school_id + year = now()->year + month = now()->month`
  - calcular `classDays` del grupo para el mes actual
- Ordenar `classDays` por fecha ascendente y devolver sólo clases del mes actual.
- `current_values` devolverá únicamente las columnas presentes en esos `classDays`.
- Si falta inscripción vigente o falta el `Assist` mensual, responder con error funcional específico para mostrar mensaje claro en UI.

### Autorización y guardado
- Endpoints protegidos con `auth:sanctum`.
- Requieren `school.module.attendances`.
- Si el usuario autenticado es instructor, validar acceso con la misma regla de `instructorCanAccessTrainingGroup`.
- El endpoint de guardado aceptará sólo:
  - `assist_id`
  - `column`
- Validar que la `column` recibida pertenezca realmente a los `classDays` resueltos para ese `assist`.
- El guardado siempre sobrescribirá con `value = 1`.
- No habrá confirmación al reemplazar un valor existente.
- Este módulo no manejará observaciones ni otros estados en v1.

### Rutas web y convención del proyecto
- No crear Blade nueva; la experiencia vive dentro de la SPA actual y entra por el catch-all existente.
- Añadir en `web.php` los comentarios de convención indicando que el flujo QR Vue consume `/api/v2/attendance-qr/*`.
- Si se agrega alguna entrada explícita web para documentación o navegación, comentar su equivalente API en `web.php` como pide el proyecto.

### Ajuste del comando mensual
- Corregir `CreateAssistsOnEndMonth` para separar dos comportamientos:
  - Si el último día del mes no es diciembre: crear registros del siguiente mes usando inscripciones del mismo año actual.
  - Si es `31 de diciembre`: el periodo objetivo será `year = siguiente año`, `month = 1`.
- En diciembre, sólo crear enero para grupos que ya tengan inscripciones del siguiente año.
- Si no existen inscripciones del siguiente año, no crear ningún `Assist` de enero.
- No usar en diciembre las inscripciones del año que termina para poblar enero del año nuevo.

## APIs / Interfaces Nuevas
- Ruta Vue `attendances-qr`: pantalla de ingreso manual.
- Ruta Vue `attendances-qr-detail`: pantalla rápida por `unique_code`.

- `GET /api/v2/attendance-qr/{unique_code}`
  - Resuelve el contexto del QR.
  - Respuesta mínima:
    - `player`
    - `unique_code`
    - `inscription_id`
    - `assist_id`
    - `training_group`
    - `year`
    - `month`
    - `class_days`
    - `current_values`

- `POST /api/v2/attendance-qr/{assist_id}/take`
  - Body: `{ column: "assistance_four" }`
  - Fuerza `value = 1` en esa columna.
  - Respuesta mínima:
    - `saved: true`
    - `assist_id`
    - `column`
    - `current_value`

- `class_days` incluirá al menos:
  - `label`
  - `date`
  - `day`
  - `column`
  - `is_today`
  - `current_value`

## Plan de Pruebas
- Feature: usuario no autenticado no puede consumir API y la ruta SPA conserva `redirect` al login.
- Feature: instructor autorizado puede resolver QR de su grupo.
- Feature: instructor sin acceso al grupo recibe rechazo.
- Feature: `school/super-admin` con permiso pueden usar el módulo.
- Feature: `unique_code` resuelve sólo inscripción de `now()->year` dentro de la escuela activa.
- Feature: si no existe inscripción vigente, se retorna error controlado.
- Feature: si no existe `Assist` del mes actual, se retorna error controlado.
- Feature: guardar desde QR actualiza exactamente la columna elegida con `1`.
- Feature: si la columna ya tenía valor, se reemplaza directamente.
- Feature: si la columna no pertenece a los `classDays` del grupo/mes actual, el endpoint rechaza la petición.
- Feature: portal de acudientes muestra el QR/enlace del deportista sin exponer acciones de toma de asistencia.
- Feature del comando:
  - con fecha fin de mes distinta de diciembre, crea siguiente mes en el mismo año
  - con `--date=YYYY-12-31`, sólo crea enero del año siguiente si existen inscripciones de ese año
  - con `--date=YYYY-12-31` y sin inscripciones del año siguiente, no crea registros
- QA manual móvil:
  - 360x640 y 390x844
  - escaneo o apertura directa del link
  - login intermedio con retorno al QR
  - selección táctil de clase
  - CTA visible con teclado móvil y safe area
  - contraste correcto en `dark/light`

## Supuestos y Defaults
- V1 sólo marca `Asistencia`.
- Faltas, excusas, retiros, incapacidades y observaciones siguen en el módulo normal.
- El escaneo lo hace la cámara o lector del dispositivo, no la app.
- El QR es un atajo de contexto, no un mecanismo de autorización.
- El módulo rápido complementa al actual y no reemplaza el flujo masivo.
- La experiencia principal es móvil; desktop queda funcional pero no optimizada como prioridad.
