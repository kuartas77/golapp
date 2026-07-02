# GolApp

GolApp es una plataforma administrativa para escuelas de futbol. Centraliza la operacion diaria de la escuela: deportistas, inscripciones, grupos, asistencias, mensualidades, facturacion, contratos, evaluaciones, competencias, reportes, notificaciones, inventario y portales de acceso para acudientes.

Este README funciona como base de conocimiento tecnica del proyecto. No reemplaza la documentacion especifica de cada modulo, pero si debe servir como primer mapa para entender la aplicacion, levantar el entorno local y ubicar las piezas principales.

## Vision General

El sistema esta construido como una aplicacion Laravel con una experiencia principal SPA en Vue. Laravel resuelve autenticacion, permisos, reglas de negocio, APIs, tareas programadas, exportaciones, PDFs y persistencia. Vue maneja la navegacion del backoffice, el portal publico y el portal de acudientes.

Modelo mental del proyecto:

- Laravel sirve el backend, APIs, autenticacion, permisos, exportaciones y tareas programadas.
- Vue 3 maneja las pantallas SPA del backoffice, portal publico y portal de acudientes.
- Sanctum protege las APIs internas, el acceso SPA y las superficies moviles/de acudientes.
- Los permisos por escuela habilitan o bloquean modulos completos por instalacion.
- Muchas pantallas nuevas usan APIs `v2`, mientras algunas rutas web antiguas siguen vivas para compatibilidad, exportaciones o flujos legacy.

## Stack Tecnico

| Capa | Tecnologias principales |
| --- | --- |
| Backend | PHP 8.3, Laravel 12, Sanctum 4, Spatie Permission 6, Yajra DataTables 12 |
| Frontend | Vue 3, Vue Router, Pinia, Vite, Bootstrap 5, Sass |
| Formularios/UI | vee-validate, yup, flatpickr, SweetAlert2, componentes Vue propios |
| Datos y reportes | MySQL 8, Laravel Excel, mPDF, DataTables |
| Notificaciones | Firebase PHP, canales Laravel, mail, Mailpit en local |
| Seguridad | Sanctum, roles/permisos, permisos por escuela, reCAPTCHA v3, secure headers |
| Testing | PHPUnit/Pest para backend, Vitest y Vue Test Utils para frontend |
| Infra local | Docker Compose con `php`, `mysql`, `nginx` y `mailpit` |

Dependencias fuente:

- Backend: `composer.json`
- Frontend: `package.json`
- Entorno: `.env.example`
- Docker: `docker-compose.yml` y `docker/php/Dockerfile`

## Roles y Accesos

| Rol / superficie | Descripcion |
| --- | --- |
| `super-admin` | Administra escuelas, permisos, plantillas globales, configuracion y superficies de backoffice global. |
| `school` | Administra la operacion de una escuela: usuarios, deportistas, inscripciones, grupos, pagos, facturas, reportes y modulos habilitados. |
| `instructor` | Accede a grupos y asistencias asociadas a su asignacion. Algunas APIs validan acceso por grupo. |
| Portal publico | Superficie publica para escuelas e inscripcion cuando esta habilitada. Vive en rutas SPA bajo `/portal`. |
| Portal de acudientes | Acceso independiente para acudientes bajo `/portal/acudientes`; permite consultar jugadores, perfil, pagos, evaluaciones y solicitudes segun permisos. |
| APIs moviles/notificacion | Rutas protegidas por Sanctum y abilities para notificaciones, pagos y solicitudes de acudientes/jugadores. |

## Modulos Funcionales

| Modulo | Funcionalidad | Superficies principales |
| --- | --- | --- |
| Dashboard y KPIs | Resumen operativo, indicadores y metricas de escuela. | `resources/js/pages/home`, `resources/js/pages/kpi`, `DashboardController`, `KpiDashboardService` |
| Escuelas | Creacion, edicion, seleccion de escuela, configuracion, permisos y datos de campus. | `resources/js/pages/admin/school`, `BackOffice/SchoolController`, `API/Admin/SchoolController` |
| Usuarios | Gestion de usuarios de escuela y backoffice, roles y activacion. | `resources/js/pages/admin/users`, `Admin/UserController`, `API/Admin/UsersController` |
| Deportistas | Registro, ficha, historial, importacion, documentos, exportacion PDF y estadisticas. | `resources/js/pages/players`, `Players/PlayerController`, `PlayerStatsController`, `ImportPlayers` |
| Inscripciones | Inscripcion anual, estado activo/inactivo, resumen, contratos, cargos y limite por escuela. | `resources/js/pages/inscriptions`, `InscriptionController`, `CreateInscriptionAction`, `InscriptionLimitService` |
| Grupos de entrenamiento | CRUD, horarios, asignacion de deportistas, cupos y tablero de movimientos. | `resources/js/pages/admin/groups/training`, `TrainingGroupController`, `GroupAssignmentService` |
| Grupos de competencia | CRUD, torneos, asignacion de deportistas y control de cupos. | `resources/js/pages/admin/groups/competition`, `CompetitionGroupController`, `TournamentController` |
| Asistencias | Control mensual por grupo, marcacion masiva, historico y exportacion. | `resources/js/pages/attendances`, `AssistController`, `AssistRepository`, `AssistExportService` |
| Asistencia QR | Toma rapida de asistencia desde QR por `unique_code`. | `resources/js/pages/attendances/qr`, `AttendanceQrController`, `MODULE-QR.md` |
| Sesiones de entrenamiento | Planeacion, detalles, seguimiento y exportacion de sesiones. | `resources/js/pages/training-sessions`, `TrainingSessionsController`, `TrainingSessionRepository` |
| Mensualidades | Control de pagos mensuales por inscripcion/grupo, estados y reportes. | `resources/js/pages/payments`, `PaymentController`, `PaymentRepository`, `PaymentAmountResolver` |
| Facturacion | Facturas, items, pagos recibidos, cargos personalizados y comprobantes. | `resources/js/pages/invoices`, `InvoiceController`, `InvoiceRepository`, `PaymentReceived` |
| Contratos | Plantillas y previsualizacion de contratos por escuela/tipo. | `resources/js/pages/admin/contracts`, `ContractController`, `ContractTemplateService` |
| Evaluaciones | Plantillas, periodos, evaluaciones de deportistas, comparativas y PDFs. | `resources/js/pages/player-evaluations`, `resources/js/pages/admin/evaluation-templates`, `app/Service/Evaluations` |
| Competencias y partidos | Control de partidos, importacion de detalle, habilidades/estadisticas y exportacion. | `resources/js/pages/matches`, `GameController`, `GameRepository`, `ImportMatchDetail` |
| Reportes | Asistencias, pagos, cartera/deudores y asistencia vs pago. | `resources/js/pages/reports`, `Reports/*Controller`, `app/Service/Reports` |
| Notificaciones | Temas, Firebase, notificaciones de sistema, solicitudes de pago y uniformes. | `resources/js/pages/notifications`, `TopicNotificationsController`, `FirebaseTopicNotificationService` |
| Portal de acudientes | Login de acudiente, perfil, jugadores, pagos, evaluaciones y solicitudes. | `resources/js/pages/portal/guardians`, `API/Portal/*`, `GuardianAccessService` |
| Inventario | Productos, movimientos, existencias y operaciones de inventario. | `resources/js/pages/inventory`, `InventoryProductController`, `InventoryMovementService` |
| Exportaciones | PDFs, Excel, contratos, fichas, reportes, asistencias y partidos. | `ExportController`, `app/Exports`, `app/Service/*ExportService` |

## Permisos por Escuela

Los permisos de modulo se centralizan en `resources/js/config/school-permissions.js` y se refuerzan en backend con middleware como `school.permission:*`.

| Key | Modulo / feature |
| --- | --- |
| `school.module.players` | Deportistas |
| `school.module.inscriptions` | Inscripciones |
| `school.module.evaluations` | Evaluaciones |
| `school.module.attendances` | Asistencias |
| `school.module.training_sessions` | Sesiones de entrenamiento |
| `school.module.matches` | Competencias y partidos |
| `school.module.payments` | Mensualidades |
| `school.module.reports` | Informes |
| `school.module.billing` | Facturacion |
| `school.module.inventory` | Inventario |
| `school.module.school_profile` | Perfil/configuracion de escuela |
| `school.module.contracts` | Contratos |
| `school.module.user_management` | Usuarios |
| `school.module.training_groups` | Grupos de entrenamiento |
| `school.module.competition_groups` | Grupos de competencia |
| `school.feature.system_notify` | Notificaciones del sistema, solicitudes y comprobantes |

Cuando se agregue una nueva pantalla o accion sensible, debe validarse en dos lugares:

- Frontend: visibilidad/navegacion con `requiresSchoolPermission` o componentes como `Can`.
- Backend: middleware, request authorization o validacion explicita en controlador/servicio.

## Arquitectura Backend

Directorios principales:

| Ruta | Uso |
| --- | --- |
| `app/Http/Controllers` | Controladores web, API v2, portal, backoffice, reportes, facturas y modulos administrativos. |
| `app/Http/Requests` | Validacion de entrada por flujo. Es el primer lugar para contratos de payload. |
| `app/Http/Resources` | Transformacion de respuestas API. |
| `app/Repositories` | Consultas y persistencia reutilizable para modelos centrales. |
| `app/Service` | Reglas de negocio, calculos, reportes, exportaciones, notificaciones y flujos compuestos. |
| `app/Models` | Modelos Eloquent y relaciones principales. |
| `app/Observers` | Reacciones a cambios de modelos como asistencias, pagos, inscripciones y partidos. |
| `app/Console/Commands` | Tareas operativas: asistencias mensuales, facturas, pagos vencidos, backfills y verificaciones. |
| `app/Modules` | Flujos modulares; actualmente destaca el pipeline de creacion de inscripciones. |
| `app/Exports` | Exportaciones Excel/PDF apoyadas por servicios y controladores. |
| `database/migrations` | Esquema historico y evolutivo de escuelas, deportistas, pagos, facturas, evaluaciones, inventario, etc. |
| `tests/Feature` y `tests/Unit` | Cobertura backend para modulos y regresiones. |

Patrones frecuentes:

- Los controladores delegan reglas complejas a servicios o repositorios.
- Las pantallas SPA consumen mayormente `/api/v2/*`.
- Algunas rutas web se mantienen para exportaciones binarias, compatibilidad o redirects hacia SPA.
- Los reportes y listados grandes suelen usar DataTables server-side.
- Los PDFs y Excel deben mantener contratos estables porque suelen ser consumidos por usuarios finales.

## Arquitectura Frontend

Directorios principales:

| Ruta | Uso |
| --- | --- |
| `resources/js/main.js` | Bootstrap de Vue. |
| `resources/js/router/index.js` | Rutas SPA, guards, roles, permisos y superficies de portal. |
| `resources/js/layouts` | Layouts de app, autenticacion, portal publico y portal de acudientes. |
| `resources/js/pages` | Pantallas por modulo funcional. |
| `resources/js/components` | Componentes compartidos de formulario, layout, tablas, botones, loaders y utilidades. |
| `resources/js/composables` | Logica reutilizable por modulo, consumo de API y estado local de pantallas. |
| `resources/js/store` | Stores Pinia para usuario autenticado, acudiente, app-state y settings. |
| `resources/js/utils` | Axios compartido, acceso por rutas, utilidades DataTable, dayjs y yup. |
| `resources/js/config` | Configuracion frontend, incluyendo permisos por escuela. |
| `resources/js/tutorials` | Guias/tutoriales por modulo. |
| `resources/js/tests` | Tests Vue/Vitest. |

Superficies SPA relevantes:

- Backoffice autenticado: `/`, `/inicio`, `/administracion`, `/facturas`, `/inventario`, etc.
- Login SPA: `/ingreso`.
- Portal publico: `/portal`.
- Portal de acudientes: `/portal/acudientes`.
- Catch-all Laravel: `Route::get('/{any}', [AppController::class, 'index'])->where('any', '.*');`

## Rutas y APIs

| Archivo | Proposito |
| --- | --- |
| `routes/web.php` | Rutas web autenticadas, exports, redirects SPA, legacy y catch-all. |
| `routes/api.php` | API principal, login SPA, API v2, modulos administrativos, reportes, DataTables y endpoints internos. |
| `routes/notification.php` | APIs de notificaciones, acudientes/jugadores, pagos y solicitudes por Sanctum abilities. |
| `routes/backoffice.php` | Superficies de backoffice historicas y configuracion super-admin. |
| `routes/portal.php` | Portal legacy comentado; el portal actual vive principalmente en SPA/API. |

Convenciones:

- Preferir `/api/v2/*` para nuevas superficies SPA.
- Mantener exportaciones binarias en rutas web cuando dependan de descarga directa del navegador.
- Si se conserva una ruta legacy, documentar o comentar su equivalente SPA/API cerca de la ruta.
- Para endpoints sensibles, validar rol, escuela activa y permiso de modulo.

## Entorno Local con Docker

Servicios definidos en `docker-compose.yml`:

| Servicio | Uso | Puertos |
| --- | --- | --- |
| `php` | PHP-FPM y ejecucion de Artisan/Composer dentro del contenedor. | `9000` |
| `mysql` | Base de datos MySQL 8. | `${FORWARD_DB_PORT:-3306}:3306` |
| `nginx` | Servidor web local y proxy hacia PHP/Vite. | `80`, `${VITE_PORT:-5173}` |
| `mailpit` | Captura de correos en desarrollo. | `1025`, `8025` |

Preparacion recomendada:

```bash
cp .env.example .env
docker compose up -d
docker exec php composer install
docker exec php php artisan key:generate
docker exec php php artisan migrate
npm install
npm run dev
```

Notas:

- `.env.example` es la referencia para variables locales. No documentar secretos reales en el README.
- El `APP_URL` de ejemplo es `http://golapp.local`; si se usa ese host, debe apuntar al entorno local.
- El proyecto usa `mailpit` en Docker, aunque algunas variables conservan nombres `MAILHOG` por compatibilidad.
- En este repo suele ser mas confiable ejecutar comandos PHP dentro del contenedor `php`.

## Comandos Frecuentes

| Tarea | Comando |
| --- | --- |
| Levantar servicios | `docker compose up -d` |
| Ver contenedores | `docker ps` |
| Instalar dependencias PHP | `docker exec php composer install` |
| Ejecutar Artisan | `docker exec php php artisan <comando>` |
| Migrar base de datos | `docker exec php php artisan migrate` |
| Limpiar caches Laravel | `docker exec php php artisan optimize:clear` |
| Tinker | `docker exec php php artisan tinker` |
| Instalar dependencias JS | `npm install` |
| Servidor Vite | `npm run dev` |
| Build frontend | `npm run build` |
| Tests Vue | `npm run test:vue` |
| Tests Vue en watch | `npm run test:vue:watch` |
| Tests backend | `docker exec php php artisan test` |
| Test backend filtrado | `docker exec php php artisan test --filter=NombreDelTest` |

## Testing y Verificacion

Backend:

- PHPUnit/Pest esta configurado en `phpunit.xml`.
- Los tests usan SQLite en memoria (`DB_DATABASE=:memory:`).
- La cobertura funcional vive principalmente en `tests/Feature`.
- Para cambios backend, correr al menos el test filtrado del modulo afectado.

Frontend:

- Vitest esta configurado desde los scripts de `package.json`.
- Los tests Vue viven en `resources/js/tests`.
- Para cambios de pantallas/composables, correr `npm run test:vue` o el test especifico si aplica.

Verificacion manual recomendada:

- Confirmar que la ruta SPA carga sin errores.
- Validar permisos y visibilidad por rol/escuela.
- Revisar que las APIs protegidas rechacen usuarios sin rol o permiso.
- Para reportes/exportaciones, descargar un archivo real y validar contenido basico.
- Para cambios con DataTables, probar busqueda, paginacion, filtros y acciones por fila.

## Convenciones Utiles

### Permisos

- Usar las keys de `resources/js/config/school-permissions.js` como fuente frontend.
- En backend, proteger con `school.permission:<key>` cuando el modulo sea configurable por escuela.
- No confiar solo en ocultar botones en Vue; las acciones deben estar protegidas server-side.

### Datos y listados

- Para listados grandes, preferir patrones DataTables server-side ya existentes.
- Para selectores, buscar primero componentes reutilizables como `CustomSelect2`, `SelectSearchable` o `PaymentSelect`.
- Evitar hacer fanout de muchas peticiones cuando exista o pueda existir endpoint masivo.

### API y Axios

- El cliente compartido esta en `resources/js/utils/axios.js`.
- Los cambios de comportamiento global de requests pueden requerir actualizar tests Vitest.
- Mantener contratos de payload alineados entre `Requests`, `Resources`, composables y componentes.

### Archivos y assets

- Los assets dinamicos se sirven por rutas como `img/dynamic/{file}`.
- Modelos con rutas de assets pueden usar helpers/traits como `ResolvesLocalAssetPath`.
- No exponer rutas locales internas directamente al frontend.

### Exportaciones

- PDF y Excel se concentran en `ExportController`, controladores de modulo y `app/Exports`.
- Muchas exportaciones siguen en rutas web por descarga directa.
- Validar permisos antes de generar archivos.

### Tareas programadas

Comandos importantes en `app/Console/Commands`:

- `CreateAssistsOnEndMonth`
- `VerifyInscriptionStatus`
- `VerifyMonthlyPaymentsDue`
- `CreateInvoices`
- `MarkCustomChargesDue`
- `UpdatePaymentsStartMonth`
- `UpdateCategoryPlayers`
- `BackfillInvoiceIdempotencyKeys`
- `PortalGuardiansBackfill`

Antes de modificar comandos operativos, revisar tests existentes y casos de cambio de mes/anio.

## Documentos Auxiliares

| Documento | Uso recomendado |
| --- | --- |
| `MODULE-QR.md` | Referencia funcional y tecnica del modulo de asistencia QR. |
| `estructura-notify.md` | Borrador de estructura para una app movil/Kotlin. No es fuente principal para el proyecto Laravel/Vue actual. |

## Checklist para Cambios

Antes de cerrar un cambio:

- Identificar si afecta backend, frontend, permisos, rutas, datos o exportaciones.
- Revisar el modulo relacionado en este README y en las rutas del repo.
- Validar permisos de rol y permisos por escuela cuando aplique.
- Correr tests filtrados del modulo o una verificacion manual proporcional al riesgo.
- Si se modifica un contrato API, actualizar el request/resource/composable/test correspondiente.
- Si se agrega un modulo o superficie grande, actualizar este README para mantenerlo como base de conocimiento viva.
