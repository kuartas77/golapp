<?php

declare(strict_types=1);

return [
    'types' => [
        'inscription' => [
            'db_code' => 'contract',
            'fallback_id' => 1,
            'fallback_name' => 'Contrato',
            'label' => 'Contrato de inscripcion',
            'description' => 'Documento base de inscripcion firmado por el acudiente.',
            'file_label' => 'CONTRATO DE INSCRIPCION',
            'pdf_view' => 'contracts/contract_inscription.blade.php',
            'acceptance_field' => 'contrato_insc',
            'portal' => [
                'requires_acceptance' => true,
                'requires_tutor_signature' => true,
                'requires_player_signature' => false,
            ],
        ],
        'affiliate' => [
            'db_code' => 'affiliate',
            'fallback_id' => 2,
            'fallback_name' => 'Afiliacion',
            'label' => 'Contrato de afiliacion y corresponsabilidad deportiva',
            'description' => 'Documento adicional opcional que requiere firma del deportista.',
            'file_label' => 'CONTRATO DE AFILIACION Y CORRESPONSABILIDAD DEPORTIVA',
            'pdf_view' => 'contracts/contract_affiliate.blade.php',
            'acceptance_field' => 'contrato_aff',
            'portal' => [
                'requires_acceptance' => true,
                'requires_tutor_signature' => true,
                'requires_player_signature' => true,
            ],
        ],
    ],
    'placeholders' => [
        'SCHOOL_LOGO' => [
            'label' => 'Logo de la escuela',
            'description' => 'Ruta local del logo para imagenes del PDF.',
            'example' => '<img src="[SCHOOL_LOGO]" width="80">',
        ],
        'SCHOOL_NAME' => [
            'label' => 'Nombre de la escuela',
            'description' => 'Nombre principal de la escuela en mayusculas.',
            'example' => '[SCHOOL_NAME]',
        ],
        'SCHOOL_NAMES' => [
            'label' => 'Alias del nombre de la escuela',
            'description' => 'Alias historico del nombre de la escuela en mayusculas.',
            'example' => '[SCHOOL_NAMES]',
        ],
        'SCHOOL_AGENT' => [
            'label' => 'Representante de la escuela',
            'description' => 'Nombre del representante o agente registrado en la escuela.',
            'example' => '[SCHOOL_AGENT]',
        ],
        'SCHOOL_SIGN' => [
            'label' => 'Firma institucional',
            'description' => 'Ruta local de una firma institucional de la escuela, si existe.',
            'example' => '<img src="[SCHOOL_SIGN]" width="120">',
        ],
        'DAY' => [
            'label' => 'Dia actual',
            'description' => 'Dia numerico de la fecha de generacion.',
            'example' => '[DAY]',
        ],
        'MONTH' => [
            'label' => 'Mes actual',
            'description' => 'Nombre del mes actual segun configuracion del sistema.',
            'example' => '[MONTH]',
        ],
        'YEAR' => [
            'label' => 'Ano actual',
            'description' => 'Ano actual de generacion del documento.',
            'example' => '[YEAR]',
        ],
        'DATE' => [
            'label' => 'Fecha actual',
            'description' => 'Fecha actual con formato dia-mes-ano.',
            'example' => '[DATE]',
        ],
        'SIGN_PLAYER' => [
            'label' => 'Firma del deportista',
            'description' => 'Ruta local de la firma del deportista capturada en el portal.',
            'example' => '<img src="[SIGN_PLAYER]" width="120">',
            'types' => ['affiliate'],
        ],
        'PLAYER_FULLNAMES' => [
            'label' => 'Nombre completo del deportista',
            'description' => 'Nombre completo del deportista en mayusculas.',
            'example' => '[PLAYER_FULLNAMES]',
        ],
        'PLAYER_DOC' => [
            'label' => 'Documento del deportista',
            'description' => 'Numero de documento de identidad del deportista.',
            'example' => '[PLAYER_DOC]',
        ],
        'PLAYER_DATE_BIRTH' => [
            'label' => 'Fecha de nacimiento',
            'description' => 'Fecha de nacimiento registrada para el deportista.',
            'example' => '[PLAYER_DATE_BIRTH]',
        ],
        'PLAYER_ADDRESS' => [
            'label' => 'Direccion del deportista',
            'description' => 'Direccion principal registrada para el deportista.',
            'example' => '[PLAYER_ADDRESS]',
        ],
        'PLAYER_EPS' => [
            'label' => 'EPS del deportista',
            'description' => 'EPS registrada para el deportista.',
            'example' => '[PLAYER_EPS]',
        ],
        'CATEGORY' => [
            'label' => 'Categoria',
            'description' => 'Categoria o ano base del deportista.',
            'example' => '[CATEGORY]',
        ],
        'TUTOR_NAME' => [
            'label' => 'Nombre del acudiente',
            'description' => 'Nombre completo del acudiente principal.',
            'example' => '[TUTOR_NAME]',
        ],
        'TUTOR_DOC' => [
            'label' => 'Documento del acudiente',
            'description' => 'Numero de documento del acudiente principal.',
            'example' => '[TUTOR_DOC]',
        ],
        'SIGN_TUTOR' => [
            'label' => 'Firma del acudiente',
            'description' => 'Ruta local de la firma capturada del acudiente.',
            'example' => '<img src="[SIGN_TUTOR]" width="120">',
        ],
        'TUTOR_MAIL' => [
            'label' => 'Correo del acudiente',
            'description' => 'Correo electronico del acudiente principal.',
            'example' => '[TUTOR_MAIL]',
        ],
        'TUTOR_PHONE' => [
            'label' => 'Telefono del acudiente',
            'description' => 'Telefono o movil del acudiente principal.',
            'example' => '[TUTOR_PHONE]',
        ],
    ],
];
