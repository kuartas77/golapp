# Actualización de Laravel 10 a 12.61.1

## Resumen

- Entregar un único PR con tres commits verificables: línea base, Laravel 11 y Laravel 12.
- Mantener PHP 8.3 y la estructura clásica con `Http/Kernel.php` y `Exceptions/Handler.php`; no migrar al nuevo skeleton.
- Dejar `laravel/framework` en `~12.61.1`, con `composer.lock` resolviendo inicialmente 12.61.1.
- Conservar URLs, payloads, almacenamiento y esquema productivo existentes.

## Cambios de implementación

1. **Línea base verde**
   - Actualizar las pruebas de rutas legacy para esperar la redirección `/administracion` → `/configuracion` y validar después la ruta canónica.
   - Alinear las expectativas del reporte de deudores con las etiquetas simplificadas actuales, sin cambiar su comportamiento.
   - Corregir el fixture de `VerifyInscriptionStatusTest` para no referenciar un grupo de competencia inexistente.
   - Exigir 268 pruebas PHP aprobadas antes de tocar Composer.

2. **Checkpoint Laravel 11**
   - Subir PHP en Composer a `^8.2`, Laravel a `^11`, Sanctum a `^4`, Collision a `^8.1`, Spatie Permission a `^6.23` y Yajra DataTables a `^12`.
   - Retirar `queueworker/sansdaemon`: el scheduler ya utiliza el comando nativo `queue:work --stop-when-empty`.
   - Retirar `jenssegers/date`, sin uso real, porque bloquea Carbon 3.
   - Retirar `doctrine/dbal`, ya innecesario y sin referencias locales.
   - Cambiar el namespace de middleware de Spatie de `Middlewares` a `Middleware`.
   - Actualizar Sanctum para usar `validate_csrf_token` y los middleware base indicados por Sanctum 4; conservar la migración existente de tokens porque ya incluye `expires_at`.
   - Normalizar las declaraciones históricas `float`/`double` retirando argumentos de escala obsoletos, sin ejecutar alteraciones sobre columnas productivas.

3. **Checkpoint Laravel 12**
   - Fijar Laravel en `~12.61.1`, PHPUnit en `^11` y Pest en `^3`; actualizar `phpunit.xml` al esquema PHPUnit 11.
   - Resolver los nombres de ruta duplicados antes del cambio de precedencia de Laravel 12: conservar los nombres web actuales y asignar prefijos explícitos `api.v2.*` a las rutas API. Las URLs no cambian.
   - Auditar Carbon 3, validación de imágenes, filesystem local y UUID; no cambiar comportamiento donde el repositorio ya es compatible.
   - Actualizar README para reflejar PHP 8.3, Laravel 12 y las nuevas versiones principales.

## Interfaces y compatibilidad

- `composer.json`: Laravel `~12.61.1`; PHP mínimo `^8.2`, ejecutado y desplegado con PHP 8.3.
- Los nombres simbólicos de rutas API pasarán a `api.v2.*`; los nombres web usados por Blade y modelos se conservarán.
- No habrá cambios en endpoints, respuestas JSON, tablas productivas ni ubicación de `Storage::disk('local')`.
- Las sesiones SPA, tokens Sanctum, permisos Spatie, DataTables y trabajos en cola deben continuar siendo compatibles.

## Validación y despliegue

- En cada checkpoint ejecutar: resolución Composer, `composer validate`, descubrimiento de paquetes, suite PHP completa, caché de configuración y caché de rutas.
- Añadir una prueba que garantice nombres de ruta únicos y cubrir login SPA, autenticación Sanctum de guardianes, roles/permisos, DataTables, PDF/Excel, archivos locales y cola programada.
- Ejecutar migraciones desde cero sobre una base MariaDB 10.11 desechable y mantener SQLite para la suite.
- Ejecutar pruebas Vue y build de producción.
- Desplegar con respaldo previo, `composer install --no-dev`, migraciones forzadas, regeneración de cachés y reinicio de workers/PHP-FPM.
- Hacer smoke test de login, API autenticada, permisos, listados y generación de archivos. Ante fallo, restaurar el release y `composer.lock` anteriores; no se prevén migraciones destructivas.
