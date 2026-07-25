<?php

declare(strict_types=1);

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
    'sports' => [
        'football' => [
            'label' => 'Fútbol',
            'modules' => [
                'training_groups',
                'competition_groups',
                'matches',
                'player_stats',
                'competition_stats',
                'coachboard',
                'methodology',
            ],
        ],
    ],
];
