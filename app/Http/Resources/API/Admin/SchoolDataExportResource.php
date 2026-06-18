<?php

namespace App\Http\Resources\API\Admin;

use App\Models\SchoolDataExport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolDataExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SchoolDataExport $export */
        $export = $this->resource;

        return [
            'id' => $export->id,
            'school_id' => $export->school_id,
            'status' => $export->status,
            'filename' => $export->filename,
            'size_bytes' => $export->size_bytes,
            'size_label' => $this->sizeLabel($export->size_bytes),
            'error_message' => $export->error_message,
            'completed_at' => optional($export->completed_at)->toIso8601String(),
            'expires_at' => optional($export->expires_at)->toIso8601String(),
            'created_at' => optional($export->created_at)->toIso8601String(),
            'requested_by' => $export->requester ? [
                'id' => $export->requester->id,
                'name' => $export->requester->name,
                'email' => $export->requester->email,
            ] : null,
            'manifest_summary' => [
                'tables' => count((array) data_get($export->manifest, 'tables', [])),
                'files' => count((array) data_get($export->manifest, 'files', [])),
                'warnings' => count((array) data_get($export->manifest, 'warnings', [])),
            ],
            'download_url' => $export->isReadyForDownload()
                ? url("/api/v2/admin/schools/{$export->school?->slug}/data-exports/{$export->id}/download")
                : null,
        ];
    }

    private function sizeLabel(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . ' MB';
        }

        return round($bytes / 1024, 2) . ' KB';
    }
}
