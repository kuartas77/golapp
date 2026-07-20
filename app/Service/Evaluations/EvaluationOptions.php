<?php

namespace App\Service\Evaluations;

class EvaluationOptions
{
    public static function statuses(): array
    {
        return [
            ['value' => 'draft', 'label' => 'Borrador'],
            ['value' => 'completed', 'label' => 'Completada'],
            ['value' => 'closed', 'label' => 'Cerrada'],
        ];
    }

    public static function templateStatuses(): array
    {
        return [
            ['value' => 'draft', 'label' => 'Borrador'],
            ['value' => 'active', 'label' => 'Activa'],
            ['value' => 'inactive', 'label' => 'Inactiva'],
        ];
    }

    public static function templateStatusActions(): array
    {
        return [
            ['value' => 'active', 'label' => 'Activar'],
            ['value' => 'inactive', 'label' => 'Inactivar'],
        ];
    }

    public static function scoreTypes(): array
    {
        return [
            ['value' => 'numeric', 'label' => 'Numérico'],
            ['value' => 'scale', 'label' => 'Escala'],
        ];
    }

    public static function evaluationTypes(): array
    {
        return [
            ['value' => 'initial', 'label' => 'Inicial'],
            ['value' => 'periodic', 'label' => 'Periódica'],
            ['value' => 'final', 'label' => 'Final'],
            ['value' => 'special', 'label' => 'Especial'],
        ];
    }
}
