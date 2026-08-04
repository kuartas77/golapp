<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Models\School;

final class DataProcessingPolicyService
{
    public function evidenceFor(School $school): array
    {
        $policy = [
            'version' => (string) config('portal_data_processing_policy.version'),
            'controller' => [
                'school_id' => $school->id,
                'name' => $school->name,
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email_info ?: $school->email,
            ],
            'purposes' => array_values(config('portal_data_processing_policy.purposes', [])),
            'rights' => array_values(config('portal_data_processing_policy.rights', [])),
        ];

        $encodedPolicy = json_encode(
            $policy,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return [
            ...$policy,
            'sha256' => hash('sha256', $encodedPolicy),
        ];
    }
}
