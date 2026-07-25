<?php

declare(strict_types=1);

$sharedModules = [
    'training_groups',
    'evaluations',
    'attendances',
    'training_sessions',
    'session_planning',
    'methodology',
    'document_planning',
];

$footballCompetitionModules = [
    'competition_groups',
    'matches',
    'player_stats',
    'competition_stats',
    'coachboard',
];

return [
    'default_organization_type' => 'school',
    'organization_types' => [
        'school' => 'Escuela',
        'club' => 'Club',
        'academy' => 'Academia',
        'foundation' => 'Fundación',
        'league' => 'Liga',
        'other' => 'Otra',
    ],
    'default_sport' => 'football',
    'shared_modules' => $sharedModules,
    'football_competition_modules' => $footballCompetitionModules,
    'sports' => [
        'football' => [
            'label' => 'Fútbol',
            'modules' => array_values(array_unique([
                ...$sharedModules,
                ...$footballCompetitionModules,
            ])),
        ],
        'futsal' => [
            'label' => 'Fútbol sala',
            'modules' => array_values(array_unique([
                ...$sharedModules,
                ...$footballCompetitionModules,
            ])),
        ],
        'basketball' => [
            'label' => 'Baloncesto',
            'modules' => $sharedModules,
        ],
        'volleyball' => [
            'label' => 'Voleibol',
            'modules' => $sharedModules,
        ],
    ],
];
