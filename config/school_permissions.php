<?php

declare(strict_types=1);

return [
    'permissions' => [
        'school.module.players' => [
            'label' => 'Deportistas',
            'description' => 'Permite consultar y trabajar el módulo de deportistas.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.inscriptions' => [
            'label' => 'Inscripciones',
            'description' => 'Permite acceder al módulo de inscripciones.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.evaluations' => [
            'label' => 'Evaluaciones',
            'description' => 'Permite acceder al módulo de evaluaciones de jugadores.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.attendances' => [
            'label' => 'Asistencias',
            'description' => 'Permite acceder al módulo de asistencias.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.training_sessions' => [
            'label' => 'Sesiones de entrenamiento',
            'description' => 'Permite acceder al módulo de sesiones de entrenamiento.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.session_planning' => [
            'label' => 'Planificación de sesiones',
            'description' => 'Permite planificar sesiones con fases y diagramas de cancha.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.methodology' => [
            'label' => 'Metodología',
            'description' => 'Permite gestionar formatos metodológicos, planificaciones e informes.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.matches' => [
            'label' => 'Competencias',
            'description' => 'Permite acceder al módulo de competencias y partidos.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.module.payments' => [
            'label' => 'Mensualidades',
            'description' => 'Permite acceder al módulo de mensualidades.',
            'group' => 'Finanzas',
            'default' => false,
        ],
        'school.module.reports' => [
            'label' => 'Informes',
            'description' => 'Permite acceder a los informes del backoffice.',
            'group' => 'Finanzas',
            'default' => false,
        ],
        'school.module.billing' => [
            'label' => 'Facturación',
            'description' => 'Permite acceder al módulo de facturación.',
            'group' => 'Finanzas',
            'default' => false,
        ],
        'school.module.inventory' => [
            'label' => 'Inventario',
            'description' => 'Permite administrar productos, stock y movimientos de inventario.',
            'group' => 'Finanzas',
            'default' => false,
        ],
        'school.module.school_outings' => [
            'label' => 'Salidas',
            'description' => 'Permite planear salidas y controlar aportes internos por deportista.',
            'group' => 'Finanzas',
            'default' => false,
        ],
        'school.module.school_profile' => [
            'label' => 'Escuela',
            'description' => 'Permite administrar la información principal de la escuela.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.contracts' => [
            'label' => 'Contratos',
            'description' => 'Permite administrar las plantillas de contratos de la escuela.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.user_management' => [
            'label' => 'Usuarios',
            'description' => 'Permite administrar usuarios de la escuela.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.training_groups' => [
            'label' => 'Grupos de entrenamiento',
            'description' => 'Permite administrar grupos de entrenamiento.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.competition_groups' => [
            'label' => 'Grupos de competencia',
            'description' => 'Permite administrar grupos de competencia.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.club_documents' => [
            'label' => 'Documentos del club',
            'description' => 'Permite administrar documentos legales y administrativos del club.',
            'group' => 'Administración',
            'default' => false,
        ],
        'school.module.document_planning' => [
            'label' => 'Planificación documental',
            'description' => 'Permite administrar programas y documentos de planificación del club.',
            'group' => 'Operación deportiva',
            'default' => false,
        ],
        'school.feature.system_notify' => [
            'label' => 'Notificaciones del sistema',
            'description' => 'Habilita las notificaciones y procesos integrados con GOLAPPLINK.',
            'group' => 'Funciones adicionales',
            'default' => false,
        ],
    ],
];
