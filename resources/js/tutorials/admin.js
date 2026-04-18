export const updateSchoolTutorial = {
    steps: [
        {
            id: 'admin-school-brand',
            selector: '[data-tour="admin-school-brand"]',
            title: 'Actualiza la informacion base',
            text: 'Aqui modificas logo, nombre, representante y datos de contacto de la escuela.',
        },
        {
            id: 'admin-school-settings',
            selector: '[data-tour="admin-school-settings"]',
            title: 'Configura valores del negocio',
            text: 'Este bloque agrupa matricula, mensualidad, anualidad y dia de notificacion.',
        },
        {
            id: 'admin-school-flags',
            selector: '[data-tour="admin-school-flags"]',
            title: 'Activa modulos y funciones',
            text: 'Los checks habilitan opciones como inscripciones, plataforma de tutores y otros comportamientos.',
        },
        {
            id: 'admin-school-actions',
            selector: '[data-tour="admin-school-actions"]',
            title: 'Guarda la configuracion',
            text: 'Al final del formulario guardas los cambios de la escuela.',
        },
    ],
}

export const usersListTutorial = {
    steps: [
        {
            id: 'admin-users-actions',
            selector: '[data-tour="admin-users-actions"]',
            title: 'Crea usuarios del backoffice',
            text: 'Este boton abre el modal para registrar cuentas de instructores o usuarios school.',
        },
        {
            id: 'admin-users-table',
            selector: '[data-tour="admin-users-table"]',
            title: 'Gestiona el listado',
            text: 'Desde la tabla puedes consultar las cuentas existentes y abrir sus acciones.',
        },
    ],
}

export const trainingGroupsTutorial = {
    steps: [
        {
            id: 'admin-training-groups-actions',
            selector: '[data-tour="admin-training-groups-actions"]',
            title: 'Crea grupos de entrenamiento',
            text: 'Los grupos de entrenamiento son la base para inscripciones, pagos y asistencias.',
        },
        {
            id: 'admin-training-groups-table',
            selector: '[data-tour="admin-training-groups-table"]',
            title: 'Administra los grupos',
            text: 'La tabla lista los grupos y te deja entrar a sus acciones de gestion.',
        },
    ],
}

export const adminTrainingGroupTutorial = {
    steps: [
        {
            id: 'admin-training-board-pool',
            selector: '[data-tour="admin-training-board-pool"]',
            title: 'Ubica los elementos disponibles',
            text: 'Este panel concentra los elementos que puedes mover dentro del tablero de organizacion.',
        },
        {
            id: 'admin-training-board-target',
            selector: '[data-tour="admin-training-board-target"]',
            title: 'Reorganiza con arrastrar y soltar',
            text: 'En esta zona mueves los elementos para administrar la distribucion del grupo.',
        },
    ],
}

export const competitionGroupsTutorial = {
    steps: [
        {
            id: 'admin-competition-groups-actions',
            selector: '[data-tour="admin-competition-groups-actions"]',
            title: 'Crea grupos de competencia',
            text: 'Estos grupos se usan para organizar torneos, partidos y estadisticas competitivas.',
        },
        {
            id: 'admin-competition-groups-table',
            selector: '[data-tour="admin-competition-groups-table"]',
            title: 'Gestiona la tabla',
            text: 'La tabla resume los grupos creados y sus acciones disponibles.',
        },
    ],
}

export const evaluationTemplatesIndexTutorial = {
    steps: [
        {
            id: 'admin-evaluation-templates-actions',
            selector: '[data-tour="admin-evaluation-templates-actions"]',
            title: 'Crea o navega plantillas',
            text: 'Desde aqui entras al editor para construir nuevas plantillas de evaluacion.',
        },
        {
            id: 'admin-evaluation-templates-filters',
            selector: '[data-tour="admin-evaluation-templates-filters"]',
            title: 'Filtra el catalogo',
            text: 'Puedes buscar por texto, ano, estado o grupo de entrenamiento.',
        },
        {
            id: 'admin-evaluation-templates-summary',
            selector: '[data-tour="admin-evaluation-templates-summary"]',
            title: 'Lee el resumen paginado',
            text: 'Este bloque muestra total filtrado, pagina actual y rango visible.',
        },
        {
            id: 'admin-evaluation-templates-table',
            selector: '[data-tour="admin-evaluation-templates-table"]',
            title: 'Gestiona las versiones',
            text: 'En la tabla puedes editar, duplicar, activar, inactivar o eliminar una plantilla.',
        },
    ],
}

export const evaluationTemplateEditorTutorial = {
    steps: [
        {
            id: 'admin-evaluation-template-editor-actions',
            selector: '[data-tour="admin-evaluation-template-editor-actions"]',
            title: 'Controla la vista del editor',
            text: 'La cabecera te deja volver al listado y duplicar la plantilla cuando estas en modo edicion.',
        },
        {
            id: 'admin-evaluation-template-editor-config',
            selector: '[data-tour="admin-evaluation-template-editor-config"]',
            title: 'Configura la plantilla',
            text: 'Aqui defines nombre, ano, estado, grupo y descripcion de la plantilla.',
        },
        {
            id: 'admin-evaluation-template-editor-criteria',
            selector: '[data-tour="admin-evaluation-template-editor-criteria"]',
            title: 'Construye los criterios',
            text: 'La tabla de criterios te deja definir dimension, tipo, rango, peso y obligatoriedad.',
        },
        {
            id: 'admin-evaluation-template-editor-actions-footer',
            selector: '[data-tour="admin-evaluation-template-editor-actions-footer"]',
            title: 'Guarda la plantilla',
            text: 'Al final puedes cancelar o guardar la version activa de la plantilla.',
        },
    ],
}

export const schoolsListTutorial = {
    steps: [
        {
            id: 'admin-schools-table',
            selector: '[data-tour="admin-schools-table"]',
            title: 'Consulta el listado de escuelas',
            text: 'Esta tabla centraliza las escuelas disponibles dentro del entorno administrativo.',
        },
        {
            id: 'admin-schools-permissions',
            selector: '[data-tour="admin-schools-permissions"]',
            title: 'Ajusta permisos por escuela',
            text: 'Desde la accion de permisos puedes habilitar o restringir modulos del backoffice por escuela.',
        },
    ],
}

export const schoolsInfoTutorial = {
    steps: [
        {
            id: 'admin-schools-info-table',
            selector: '[data-tour="admin-schools-info-table"]',
            title: 'Revisa la informacion consolidada',
            text: 'Esta vista sirve para consultar el detalle general de las escuelas registradas.',
        },
    ],
}
