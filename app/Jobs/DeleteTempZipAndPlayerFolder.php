<?php

namespace App\Jobs;

use App\Traits\ErrorTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteTempZipAndPlayerFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ErrorTrait;

    /**
     * Ruta RELATIVA en el disk local. Ej: tmp/zips/ABC_uuid.zip
     */
    public function __construct(public string $relativePath, public string $playerFolder)
    {
        //
    }

    public function handle(): void
    {
        retry(2, function() {

            // Borrar el archivo (si ya no existe, no falla)
            Storage::disk('local')->delete($this->relativePath);

            Storage::disk('local')->deleteDirectory($this->playerFolder);

        }, fn(\Exception $e) => $this->logError('Eliminando archivos '. $this->relativePath, $e));



    }
}
