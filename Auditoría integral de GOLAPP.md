# Auditoría integral de GOLAPP

Fecha: 5 de agosto de 2026
Alcance: repositorio `/home/skidrunk/projects/golapp`
Resultado global: **57/100 — producto funcionalmente sólido, pero con una presentación y un sistema visual todavía desiguales.**

No se modificó ningún archivo. El árbol Git quedó limpio.

Tipos de evidencia:

- **[E] Ejecutada:** pruebas, build o HTTP real.
- **[R] Runtime:** datos obtenidos de la aplicación local.
- **[S] Estática:** conclusión derivada del código; requiere comprobación visual o con usuarios.

## 1. Resumen ejecutivo

GOLAPP ya transmite valor profesional por la amplitud real del dominio, sus controles de acceso, su protección financiera y la estabilidad demostrada por 430 pruebas backend y 147 pruebas Vue. No es una aplicación “básica” funcionalmente.

La percepción económica o improvisada proviene principalmente de otra capa: el tema heredado, la fragmentación visual y la falta de reglas transversales. Conviven una experiencia moderna en portal, evaluaciones y QR con pantallas antiguas basadas en paneles, badges usados como botones, acciones sólo con iconos, jerarquías `h4/h5` variables y estilos locales.

Los riesgos más importantes son:

1. **Accesibilidad de teclado:** el CSS elimina globalmente los indicadores de foco.
2. **Confianza financiera:** “Totales” en facturas calcula únicamente las filas cargadas de la página, aunque no lo explica.
3. **Estados engañosos:** al menos 12 listados convierten errores HTTP en tablas vacías.
4. **Identidad dispersa:** la variable primaria es `#183894`, aunque la identidad declarada es `#0F1C46`; existen 277 hexadecimales diferentes en Vue/SCSS.
5. **Escalabilidad visual:** 353 botones directos frente a un `AppButton` consumido por una sola pantalla y varios componentes superiores a 1.000 líneas.

No encontré un problema `S4` demostrado que impida operar el producto. Sí existen varios `S3` capaces de generar errores de interpretación, exclusión de usuarios o pérdida de confianza.

### Cinco prioridades de mayor impacto

1. Recuperar foco visible, contraste AA y nombres accesibles.
2. Definir tokens y componentes básicos sin rediseñar la marca.
3. Separar claramente error, vacío inicial y “sin coincidencias”.
4. Corregir totales financieros y estandarizar fechas, monedas y estados.
5. Migrar primero jugadores, inscripciones, asistencias, mensualidades y facturas al nuevo patrón.

## 2. Mapa del producto

### Aplicaciones y superficies encontradas

| Superficie | Estado |
|---|---|
| Backoffice Laravel + Vue SPA | Implementado |
| Portal público de escuelas e inscripción | Implementado |
| Portal web de acudientes | Implementado |
| API móvil de acudientes/notificaciones | Implementada |
| Aplicación Android Jetpack Compose | **No está en este repositorio** |
| Evaluaciones deportivas configurables | Implementadas |
| Evaluaciones médicas/fisioterapéuticas independientes | No encontradas; sólo antecedentes, certificados y prescripciones puntuales |
| Administración formal de suscripciones SaaS | No encontrada; existe límite de inscripciones por escuela |

La API pública local respondió `200` y reportó una escuela con inscripción habilitada, portal de acudientes y límite `5/200`. La aplicación entregó también catálogos y endpoints reales.

### Tecnologías

- Laravel 12.61, PHP 8.2+, Sanctum, Spatie Permission, Yajra DataTables.
- Vue 3.5, Vue Router, Pinia, Vite 6.
- Bootstrap 5.1, Sass, DataTables Bootstrap, SweetAlert2, Flatpickr.
- Vee Validate + Yup.
- ApexCharts.
- Restos de jQuery y una segunda librería de DataTable declarada en dependencias.
- PHPUnit/Pest, Vitest y Vue Test Utils.

Evidencia: [composer.json](/home/skidrunk/projects/golapp/composer.json:7), [package.json](/home/skidrunk/projects/golapp/package.json:11), [router](/home/skidrunk/projects/golapp/resources/js/router/index.js:11).

Se registraron **436 rutas Laravel**: 288 API y 148 no API. La navegación SPA usa imports dinámicos por pantalla.

### Roles reales

| Perfil | Objetivo | Acciones frecuentes | Riesgo principal |
|---|---|---|---|
| `super-admin` | Administrar escuelas y configuración global | Escuelas, permisos, plantillas, exportaciones | Confundir contexto global y escuela seleccionada |
| `school` | Operación integral de una escuela | Deportistas, inscripciones, cobros, facturas, grupos | Cambios financieros o de afiliación poco claros |
| `instructor` | Gestión deportiva asignada | Asistencia, sesiones, metodología, competencias | Editar otro grupo o periodos cerrados |
| Acudiente web | Consultar y actualizar información familiar | Jugadores, pagos, asistencia, evaluaciones | No entender estados, deuda o acceso |
| Acudiente/jugador móvil | Consumir notificaciones y experiencia deportiva | Login, actividad, comprobantes, solicitudes | Compatibilidad de contrato API |
| Visitante público | Iniciar inscripción | Completar datos, OTP, contratos y documentos | Abandono o pérdida de información |

No encontré roles independientes de “personal médico”, “fisioterapeuta”, “deportista web” o “personal administrativo”. Las capacidades se resuelven principalmente mediante los tres roles y permisos por escuela.

### Módulos confirmados

Dashboard/KPI, escuelas, usuarios, deportistas, inscripciones, grupos de entrenamiento y competencia, horarios, asistencias, asistencia QR, sesiones, planificación, metodología, competencias, evaluaciones, mensualidades, recibos, facturas, cargos, comprobantes, inventario, salidas, saldos a favor, notificaciones, reportes, contratos, documentos y portal de acudientes.

El menú está agrupado parcialmente por usuario, pero todavía presenta hasta 19 destinos superiores o destacados, con abreviaturas como “Plan. documental”, “S. Entrenamiento” y “Comp. de pago”. Evidencia: [sidebar.vue](/home/skidrunk/projects/golapp/resources/js/components/layout/sidebar.vue:180).

### Flujos críticos

| Flujo | Etapas visibles | Fricción observada |
|---|---:|---|
| Crear/editar deportista | Lista → acción → wizard de 3 pasos → guardar | Formulario largo; sin protección transversal de cambios no guardados |
| Inscribir desde backoffice | Lista → modal → seleccionar deportista/tarifa/grupos/cargos → guardar | Mucha decisión financiera y deportiva en un modal |
| Inscripción pública | Abrir escuela → wizard de 3 a 5 pasos → verificar correo → guardar | Correctamente conserva borrador; títulos y catálogos necesitan normalización |
| Asignar grupo | Configuración → grupo → tablero → mover deportista | “Configuración” no es el modelo mental más directo para una tarea operativa |
| Registrar asistencia | Filtros → cargar/crear periodo → marcar → guardar/bulk | Tabla densa; varios controles usan badges como botones |
| Consultar asistencia histórica | Seleccionar año/mes/grupo → consultar | Historial y toma de asistencia comparten una superficie compleja |
| Mensualidad | Filtros → localizar inscripción → modificar estado → guardar | Alta densidad; múltiples acciones pequeñas y semánticas visuales |
| Consultar deuda | Informes → deudores → filtros → generar/exportar | Flujo razonable, pero formatos y estados deben centralizarse |
| Crear factura | Inscripción → seleccionar conceptos → revisar total → confirmar | Buen bloqueo de doble envío e idempotencia |
| Registrar pago | Factura → seleccionar ítems → método/monto → confirmar | Correcto detalle por ítem; la pantalla acumula muchas acciones |
| Notificación | Lista → modal → audiencia/contenido → enviar | Necesita validación directa de confirmación y trazabilidad percibida |
| Evaluación | Listado → contexto/plantilla → criterios → guardar | Editor potente, pero algunas tablas tienen hasta 10 columnas |
| Cambiar escuela | Selector de encabezado → recargar contexto | Técnicamente protegido; debe comprobarse visualmente la claridad del contexto |
| Suscripción | No localizado | Sólo existe `max_inscriptions`; no hay flujo SaaS de plan/ciclo de cobro |

## 3. Scorecard

| Categoría | Peso | Nota | Evidencia resumida |
|---|---:|---:|---|
| Consistencia visual | 15 | 48 | 277 colores hex, 61 bloques de estilo Vue y tres azules “primarios” |
| Jerarquía y legibilidad | 10 | 62 | Portal moderno; backoffice alterna paneles, `h4`, `h5`, breadcrumbs y headers locales |
| Navegación | 10 | 68 | Guardas y permisos sólidos; menú amplio y algunas tareas operativas dentro de Configuración |
| Formularios | 10 | 66 | Yup/Vee Validate, carga y bloqueo; formularios muy grandes y protección de salida incompleta |
| Estados y feedback | 10 | 56 | Loaders/toasts abundantes; errores convertidos en estados vacíos |
| Responsive/adaptabilidad | 10 | 62 | Bootstrap y DataTables responsive; tablas deliberadamente superiores a 1.100 px |
| Accesibilidad | 10 | 32 | Foco global eliminado, contraste insuficiente, iconos sin nombre y sin reducción de movimiento |
| Rendimiento percibido | 10 | 70 | Lazy routes, pipeline y skeletons; CSS global de 697 KB y chunk de gráficas de 583 KB |
| Confianza y claridad | 10 | 64 | Idempotencia, auditoría y mensajes buenos; totales y microcopy inconsistentes |
| Mantenibilidad visual | 5 | 45 | Componentes compartidos parciales; varios SFC >1.000 líneas y `AppButton` casi sin adopción |

**Puntuación ponderada: 57/100.**

## 4. Inventario priorizado de hallazgos

| ID | Aplicación / módulo | Hallazgo y evidencia | Sev. | Impacto | Recomendación | Esf. | Criterio de aceptación |
|---|---|---|---:|---|---|---:|---|
| A11Y-01 | Web global | `[S]` `:focus`, enlaces, botones y textarea eliminan `outline`; `.btn:focus` elimina sombra. [main.scss](/home/skidrunk/projects/golapp/resources/js/assets/sass/main.scss:32) | S3 | Usuarios de teclado pierden ubicación | Foco `:focus-visible` tokenizado y contrastante | S | Todos los controles presentan foco visible con teclado |
| A11Y-02 | Web global | `[S]` Texto base `#888ea8` sobre `#f1f2f3` tiene contraste 2,89:1. [main.scss](/home/skidrunk/projects/golapp/resources/js/assets/sass/main.scss:12) | S3 | Legibilidad deficiente | Elevar contraste de texto secundario | S | Texto normal ≥4,5:1; texto grande ≥3:1 |
| A11Y-03 | Facturas/listados | `[S]` Acciones renderizadas como HTML sólo con iconos y sin nombre accesible. [invoicesList.js](/home/skidrunk/projects/golapp/resources/js/composables/invoices/invoicesList.js:40) | S3 | Acciones indescifrables para lector de pantalla | Texto oculto o `aria-label`; mantener tooltip | S | Cada acción tiene nombre y propósito anunciado |
| A11Y-04 | Web global | `[S]` No se encontró `prefers-reduced-motion`, skip link ni prueba Axe; 353 botones frente a 64 `aria-label` | S2 | Cobertura AA incompleta | Añadir baseline WCAG y pruebas automatizadas | M | Axe sin violaciones críticas en flujos principales |
| UI-01 | Tema global | `[S]` Identidad conocida `#0F1C46`, pero `$primary` es `#183894`; SweetAlert usa `#4361ee`. [colores](/home/skidrunk/projects/golapp/resources/js/assets/base/_color_variables.scss:7), [main.js](/home/skidrunk/projects/golapp/resources/js/main.js:36) | S3 | Marca inconsistente | Tokens de marca y semánticos únicos | M | Primario/acento provienen exclusivamente de tokens |
| UI-02 | Web global | `[S]` 277 hex distintos, 61 bloques `<style>` y 54 estilos inline en Vue | S2 | Deriva visual continua | Inventario y tokens incrementales | M | Cero nuevos colores no tokenizados; reducción verificable |
| UI-03 | Componentes | `[S]` `AppButton` sólo tiene un consumidor; existen 353 botones directos. [AppButton.vue](/home/skidrunk/projects/golapp/resources/js/components/general/AppButton.vue:1) | S2 | Alturas, estados y prioridades distintas | Completar API y migrar flujos críticos | M | Primarios/destructivos/loading comparten componente |
| STATE-01 | 12 listados | `[S]` Errores HTTP se devuelven como `data: []`; facturas lo hace sin mensaje. [invoicesList.js](/home/skidrunk/projects/golapp/resources/js/composables/invoices/invoicesList.js:73) | S3 | Error parece “sin registros” | Estado de error con reintento independiente | M | Red caída nunca muestra vacío normal |
| TRUST-01 | Facturas | `[S]` Footer “Totales” suma únicamente datos cargados por DataTables server-side. [Invoices.vue](/home/skidrunk/projects/golapp/resources/js/pages/invoices/Invoices.vue:97), [invoicesList.js](/home/skidrunk/projects/golapp/resources/js/composables/invoices/invoicesList.js:86) | S3 | Decisiones financieras con cifra ambigua | Total backend filtrado o etiqueta “Total de esta página” | S/M | Alcance del total es correcto y explícito |
| COPY-01 | Global | `[R/S]` Fechas mezclan `YYYY-M-D`, `YYYY-MM-DD`, `DD-MM-YYYY` y `DD/MM/YYYY`; el DataTable compartido usa fecha técnica. [DatatableTemplate.vue](/home/skidrunk/projects/golapp/resources/js/components/general/DatatableTemplate.vue:11) | S2 | Lectura lenta y ambigüedad | `AppDate`/utilidad central con formato colombiano | S | Una convención visible por tipo de fecha |
| COPY-02 | Portal público | `[R]` Catálogo real devuelve “Tarjeta de Indentidad”, “BISABEULA”, “MAMA” y “PAPA”; la UI alterna tutor/acudiente y jugador/deportista | S2 | Menor confianza y aspecto improvisado | Vocabulario y catálogo corregido en fuente | S | Catálogos con ortografía y capitalización acordadas |
| FORM-01 | Deportistas/inscripciones/evaluaciones | `[S]` Componentes de 1.055–1.812 líneas; sólo el portal público persiste borrador. [pasos portal](/home/skidrunk/projects/golapp/resources/js/pages/portal/PortalSchoolInscriptionModal.vue:1215) | S2 | Mayor riesgo de regresión o pérdida | Separar secciones; guard de cambios según riesgo | L | Salida accidental solicita confirmación donde corresponda |
| RESP-01 | Partidos/evaluaciones | `[S]` Tabla de partido exige `min-width:1120px`; editor de evaluación tiene 10 columnas. [FormMatch.vue](/home/skidrunk/projects/golapp/resources/js/pages/matches/FormMatch.vue:985), [Editor.vue](/home/skidrunk/projects/golapp/resources/js/pages/admin/evaluation-templates/Editor.vue:175) | S2 | Scroll lateral y campos difíciles en móvil | Maestro-detalle o edición por fila en móvil | L | Tarea completa a 360 px sin perder acciones |
| NAV-01 | Sidebar | `[S]` Separación por permisos es sólida, pero menú extenso y abreviado; grupos/horarios operativos viven en Configuración | S2 | Descubrimiento y modelo mental débiles | Investigación de card sorting y accesos frecuentes | M | Usuarios encuentran tareas críticas sin memorizar sección |
| PERF-01 | Web global | `[E]` Build: CSS principal 697 KB/117 KB gzip; charts 583 KB/158 KB gzip; UI 280 KB/95 KB gzip | S2 | Primera carga y parseo CSS | Depurar tema heredado y cargar CSS por superficie | L | Presupuesto de assets y mejora medida de LCP/parse |
| ARCH-01 | Frontend | `[S]` Dos librerías DataTable declaradas, Bootstrap, jQuery, SweetAlert y componentes propios; abstracciones parciales | S2 | Duplicación y comportamiento desigual | Consolidar por migración, no reescritura total | L | Una solución de tabla/form/modal por caso de uso |
| QA-01 | CI | `[E/S]` Tests pasan localmente, pero scripts frontend sólo ofrecen build/Vitest y despliegue no ejecuta pruebas/lint. [package.json](/home/skidrunk/projects/golapp/package.json:3), [deploy.yml](/home/skidrunk/projects/golapp/.github/workflows/deploy.yml:119) | S3 | Regresiones visuales llegan a despliegue | Quality gate previo a deploy | M | CI ejecuta tests, Pint, build y checks de rutas |
| QA-02 | Backend | `[E]` `Pint --test`: 688 archivos, 416 incidencias de estilo; PHPStan está instalado pero sin configuración reproducible | S2 | Línea base ruidosa | Baseline gradual por directorio tocado | M | Código nuevo y módulos migrados pasan el gate |
| AND-01 | Android | No hay fuentes Gradle/Kotlin/Compose en el repositorio | — | No se puede comparar web/Android | Auditar repositorio Android aparte | — | Acceso al proyecto y build Android disponibles |

## 5. Problemas transversales

### Sistema visual

No existe todavía un sistema de diseño completo. Existe una mezcla de:

- Variables Sass heredadas.
- Bootstrap como estructura.
- Componentes compartidos parciales.
- Estilos locales por pantalla.
- Tokens locales de portales modernos.
- Overrides separados para tema oscuro.

Los componentes que presentan duplicación demostrada y sí conviene centralizar son:

- `AppButton`
- `AppPageHeader`
- `AppTable` o evolución de `DatatableTemplate`
- `AppEmptyState`
- `AppErrorState`
- `AppLoadingState`
- `AppConfirmDialog`
- `AppMoney`
- `AppDate`
- `AppStatus`
- `AppFilterBar`

`AppInput`, `AppSelect` y `AppModal` deben partir de los componentes ya existentes, sin crear una tercera implementación.

### Tokens mínimos propuestos

| Grupo | Tokens mínimos | Problema que resuelve |
|---|---|---|
| Marca | `brand-primary #0F1C46`, `brand-accent #FFCA00`, `brand-on-*` | Evita azules primarios competidores |
| Semánticos | success, info, warning, danger + foreground/background/border | Estados consistentes y accesibles |
| Superficies | canvas, surface, elevated, overlay | Light/dark sin parches locales |
| Texto | primary, secondary, muted, inverse, disabled | Contraste AA |
| Bordes | subtle, default, strong, focus | Separación y foco predecibles |
| Espaciado | escala 4/8/12/16/24/32/48 | Reduce márgenes arbitrarios |
| Tipografía | display, page-title, section-title, body, label, caption, numeric | Jerarquía clara |
| Radios | 4, 8, 12, pill | Evita mezcla accidental |
| Elevación | none, low, modal, overlay | Sombras con función |
| Breakpoints | Bootstrap documentados + reglas de densidad | Responsive coherente |
| Motion | fast, normal, slow, easing y reduced-motion | Transiciones consistentes |
| Datos | money, date, datetime, percentage, status | Claridad financiera y temporal |

## 6. Fortalezas demostradas

- 430 pruebas backend y 147 frontend pasaron.
- Composer válido y build de producción exitoso.
- Lazy loading por ruta y separación manual de chunks.
- DataTables server-side con pipeline de cinco páginas.
- Permisos por rol y por escuela tanto en router como en API.
- Facturación protegida mediante transacción e idempotencia. [InvoiceRepository.php](/home/skidrunk/projects/golapp/app/Repositories/InvoiceRepository.php:155)
- Portal público con pasos condicionales, OTP, conservación local del formulario y mensajes específicos.
- Portal de acudientes con loading, vacío, error y microcopy contextual. [GuardianLogin.vue](/home/skidrunk/projects/golapp/resources/js/pages/portal/guardians/GuardianLogin.vue:35)
- Adaptación responsive real en varias superficies nuevas.
- Formato monetario COP global y validaciones de dominio robustas.
- API móvil conserva contratos y aislamiento de jugadores.

## 7. Quick wins

1. Reemplazar la eliminación global de foco por `:focus-visible`.
2. Oscurecer el texto secundario para AA.
3. Corregir “Guia”, “Indentidad”, “BISABEULA”, capitalización y acentos.
4. Etiquetar cierres como “Cerrar”, no `aria-label="Close"`.
5. Añadir nombre accesible a acciones de tabla.
6. Cambiar “Totales” por “Total de esta página” mientras se implementa el total filtrado real.
7. Crear `formatDisplayDate()` y reemplazar `YYYY-M-D`.
8. Mostrar error/reintento en las 12 tablas que hoy retornan vacío.
9. Adoptar `AppButton` en los encabezados de cinco flujos críticos.
10. Añadir presupuesto de bundles y tests al workflow.

## 8. Roadmap recomendado

### Etapa 0: medición y protección — 1 a 2 semanas

- Capturas baseline en 360, 768, 1024, 1366 y 1920 px.
- Axe, teclado, zoom 200/400% y lector de pantalla.
- Métricas LCP, INP y CLS con datos reales.
- Pruebas visuales de los cinco flujos críticos.
- Gate CI sin aplicar Pint masivamente al legado.

### Etapa 1: fundamentos — 2 a 4 semanas

- Tokens y contraste.
- Foco visible.
- Tipografía y formatos.
- `AppPageHeader`, estados, botón, fecha, dinero y status.
- Compatibilidad light/dark desde el origen.

### Etapa 2: flujos críticos — 4 a 8 semanas

1. Facturas y mensualidades.
2. Asistencias.
3. Inscripciones.
4. Deportistas.
5. Portal público/acudientes.

### Etapa 3: secundarios — 3 a 6 semanas

Notificaciones, evaluaciones, reportes, inventario, salidas, configuración y administración global.

### Etapa 4: accesibilidad y rendimiento continuo

- Auditoría WCAG 2.2 AA.
- Presupuesto de assets.
- RUM/Core Web Vitals.
- Auditoría Android cuando esté disponible.
- Regresión visual en CI.

## 9. Estrategia de implementación

La primera iniciativa debe ser **“Fundamentos de confianza y accesibilidad”**, no un rediseño completo.

Orden recomendado:

1. Añadir tokens compatibles con las clases actuales.
2. Corregir foco y contraste sin cambiar layouts.
3. Introducir componentes base mediante wrappers sobre Bootstrap.
4. Migrar una pantalla piloto: listado de facturas.
5. Validar light/dark, móvil, teclado y contratos API.
6. Extender el patrón a pagos, asistencias, inscripciones y jugadores.
7. Retirar implementaciones antiguas sólo cuando no tengan consumidores.

Archivos iniciales:

- [color_variables.scss](/home/skidrunk/projects/golapp/resources/js/assets/base/_color_variables.scss:7)
- [main.scss](/home/skidrunk/projects/golapp/resources/js/assets/sass/main.scss:8)
- [dark.scss](/home/skidrunk/projects/golapp/resources/js/assets/sass/dark.scss:1)
- [AppButton.vue](/home/skidrunk/projects/golapp/resources/js/components/general/AppButton.vue:1)
- [DatatableTemplate.vue](/home/skidrunk/projects/golapp/resources/js/components/general/DatatableTemplate.vue:1)
- [datatableUtils.js](/home/skidrunk/projects/golapp/resources/js/utils/datatableUtils.js:3)
- `resources/js/utils/formatters.js`, como nueva fuente única propuesta.
- CI de despliegue.

No deberían modificarse todavía:

- Reglas de pagos, tarifas o deuda.
- Contratos móviles/acudientes.
- Rutas y nombres públicos.
- Estructura de datos de grupos.
- Idempotencia y trazabilidad financiera.
- Formularios completos antes de tener capturas, métricas y pruebas visuales.

## 10. Backlog listo para desarrollo

Escala de fórmula: impacto, frecuencia, alcance y reducción de riesgo de 1–5; esfuerzo XS=1, S=2, M=3, L=5, XL=8.

| Pri. | Tarea | Fórmula | Alcance y solución | Fuera de alcance | Pruebas / aceptación | Riesgo |
|---:|---|---:|---|---|---|---|
| P0 | Recuperar foco visible global | `5×5×5×5÷2 = 312,5` | Tokens de foco y `:focus-visible`; retirar resets destructivos | Rediseño de controles | Teclado completo, contraste ≥3:1 | Bajo |
| P0 | Diferenciar error, vacío y sin coincidencias | `5×5×5×5÷3 = 208,3` | Estado compartido con reintento en DataTables | Cambiar APIs de datos | Fallo HTTP nunca se representa como vacío | Medio |
| P0 | Corregir totales de facturas | `5×4×3×5÷2 = 150` | Total filtrado desde API o etiqueta temporal de página | Reglas contables | Test con más de una página y filtros | Medio |
| P0 | Corregir contraste AA | `5×5×5×4÷2 = 250` | Texto, controles, disabled y estados light/dark | Cambio de marca | Axe + matriz de contraste | Bajo |
| P1 | Fundar tokens de GOLAPP | `5×5×5×4÷3 = 166,7` | Marca, semánticos, superficies, spacing, type, radius, motion | Rediseño masivo | No aparecen nuevos hex directos | Medio |
| P1 | Centralizar fecha, dinero y estados | `4×5×5×4÷2 = 200` | `AppDate`, `AppMoney`, `AppStatus` | Cambiar valores API | Formatos coherentes `es-CO`, fallback seguro | Bajo |
| P1 | Acciones de tabla accesibles | `4×5×5×4÷3 = 133,3` | Etiquetas, hit area, menús y orden de foco | Replantear columnas | Nombre accesible y 44×44 px cuando aplique | Medio |
| P1 | Estandarizar encabezados y botones | `4×5×5×3÷3 = 100` | `AppPageHeader` y completar `AppButton` | Nueva navegación | Acción primaria única y consistente | Medio |
| P1 | Normalizar vocabulario y catálogos | `4×4×5×3÷2 = 120` | Deportista/acudiente, ortografía, mayúsculas | Renombrar modelos/API | Catálogo aprobado y sin textos técnicos | Bajo |
| P2 | Responsive de partidos/evaluaciones | `4×3×3×3÷5 = 21,6` | Vista resumida/edición por fila para móvil | Cambiar estadísticas | Flujo completo a 360 y 768 px | Alto |
| P2 | Dividir componentes >1.000 líneas | `3×4×4×3÷5 = 28,8` | Extraer secciones y composables con contrato explícito | Reescritura funcional | Cobertura existente permanece verde | Medio |
| P2 | Reducir CSS y dependencias globales | `3×4×5×3÷5 = 36` | Auditar tema, fuentes e imports por superficie | Sustituir Bootstrap de una vez | Presupuesto CSS/JS y medición antes/después | Alto |
| P2 | Quality gate CI | `4×5×5×5÷3 = 166,7` | Composer, PHPUnit, Vitest, build, Pint incremental, rutas | Formatear 688 archivos en un PR | Deploy bloqueado ante regresión | Medio |
| P3 | Investigación de arquitectura de información | `3×4×4×2÷3 = 32` | Card sorting con escuela e instructores | Rehacer sidebar anticipadamente | ≥80% encuentra tareas críticas | Bajo |
| P3 | Auditoría Android separada | No calculable | Repositorio, build, Compose, accesibilidad y rendimiento | Inferir desde APIs | Proyecto Android disponible y ejecutable | Dependencia externa |

Cada tarea debe conservar roles, permisos por escuela, contratos API, español visible y compatibilidad light/dark.

## 11. Respuestas explícitas

1. **¿Qué lo hace profesional?** Cobertura funcional, seguridad por escuela, pruebas, transacciones, idempotencia, portales y flujos con mensajes específicos.
2. **¿Qué parece económico?** Tema heredado, colores dispersos, jerarquía variable, iconos sin etiqueta, microcopy desigual y tablas densas.
3. **Cinco inconsistencias visuales:** primarios distintos, botones, page headers, formatos de fecha y estados/loading.
4. **Cinco problemas de usabilidad:** foco invisible, error presentado como vacío, total financiero ambiguo, tablas móviles densas y formularios largos sin guard transversal.
5. **Módulos inmediatos:** facturación, mensualidades, asistencias, inscripciones y deportistas.
6. **Sin rediseño completo:** foco, contraste, tokens, copy, estados, formatos y componentes base.
7. **Componentes a centralizar:** botón, page header, estados, confirmación, dinero, fecha, status, filtros y tabla.
8. **¿Identidad coherente?** Reconocible, pero no aplicada consistentemente.
9. **¿Organización por usuario o técnica?** Mixta; permisos y secciones mejoraron, pero Configuración todavía absorbe tareas operativas.
10. **¿Web y Android se sienten iguales?** No se puede determinar: Android no está incluido.
11. **Mayor aumento de confianza:** totales claros, feedback inequívoco, trazabilidad visible, copy consistente y accesibilidad.
12. **Camino a SaaS maduro:** design system gobernado, quality gates, métricas reales, WCAG y migración incremental.
13. **Primera iniciativa:** fundamentos de confianza y accesibilidad.
14. **Qué no modificar aún:** reglas financieras, rutas, contratos móviles, dominio de grupos y formularios antes de medir.
15. **Qué requiere usuarios:** arquitectura del menú, densidad de tablas, conveniencia del wizard, lenguaje de cobro y prioridades por rol.

## 12. Validaciones realizadas y límites

- `composer validate`: aprobado.
- Backend: **430 tests, 2.418 aserciones, todos aprobados**.
- Vue: **147 tests, todos aprobados**.
- Build de producción: aprobado en `/tmp/golapp-audit-build`.
- Pint read-only: **688 archivos, 416 incidencias**.
- HTTP interno: portal público, ingreso y API pública respondieron `200`.
- Git: sin modificaciones.
- No se instalaron dependencias ni se ejecutaron migraciones.
- No se midieron Core Web Vitals, screenshots, navegación real por teclado, lector de pantalla ni breakpoints visuales porque el entorno no tiene navegador, Playwright o Chromium.
- La valoración Android no fue posible porque no hay fuentes Android en el repositorio.