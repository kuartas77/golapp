<?php

declare(strict_types=1);

return [
    'permissions' => [
        'school.module.players' => [
            'label' => 'Deportistas',
            'description' => 'Permite consultar y trabajar el módulo de deportistas.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.inscriptions' => [
            'label' => 'Inscripciones',
            'description' => 'Permite acceder al módulo de inscripciones.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.evaluations' => [
            'label' => 'Evaluaciones',
            'description' => 'Permite acceder al módulo de evaluaciones de jugadores.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.attendances' => [
            'label' => 'Asistencias',
            'description' => 'Permite acceder al módulo de asistencias.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.training_sessions' => [
            'label' => 'Sesiones de entrenamiento',
            'description' => 'Permite acceder al módulo de sesiones de entrenamiento.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.matches' => [
            'label' => 'Competencias',
            'description' => 'Permite acceder al módulo de competencias y partidos.',
            'group' => 'Operación deportiva',
            'default' => true,
        ],
        'school.module.payments' => [
            'label' => 'Mensualidades',
            'description' => 'Permite acceder al módulo de mensualidades.',
            'group' => 'Finanzas',
            'default' => true,
        ],
        'school.module.reports' => [
            'label' => 'Informes',
            'description' => 'Permite acceder a los informes del backoffice.',
            'group' => 'Finanzas',
            'default' => true,
        ],
        'school.module.billing' => [
            'label' => 'Facturación',
            'description' => 'Permite acceder al módulo de facturación.',
            'group' => 'Finanzas',
            'default' => true,
        ],
        'school.module.school_profile' => [
            'label' => 'Escuela',
            'description' => 'Permite administrar la información principal de la escuela.',
            'group' => 'Administración',
            'default' => true,
        ],
        'school.module.user_management' => [
            'label' => 'Usuarios',
            'description' => 'Permite administrar usuarios de la escuela.',
            'group' => 'Administración',
            'default' => true,
        ],
        'school.module.training_groups' => [
            'label' => 'Grupos de entrenamiento',
            'description' => 'Permite administrar grupos de entrenamiento.',
            'group' => 'Administración',
            'default' => true,
        ],
        'school.module.competition_groups' => [
            'label' => 'Grupos de competencia',
            'description' => 'Permite administrar grupos de competencia.',
            'group' => 'Administración',
            'default' => true,
        ],
        'school.feature.system_notify' => [
            'label' => 'Notificaciones del sistema',
            'description' => 'Habilita las notificaciones y procesos integrados con GOLAPPLINK.',
            'group' => 'Funciones adicionales',
            'default' => false,
        ],
    ],
];
