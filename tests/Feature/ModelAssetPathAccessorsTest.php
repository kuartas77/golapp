<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Player;
use App\Models\School;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ModelAssetPathAccessorsTest extends TestCase
{
    public function testSchoolLogoLocalResolvesExistingPublicDiskPath(): void
    {
        $logoPath = 'tests/pdf/school-logo.jpg';
        Storage::disk('public')->put($logoPath, 'school-logo');

        try {
            $school = new School();
            $school->forceFill(['logo' => $logoPath]);

            $this->assertSame(storage_path('app/public/' . $logoPath), $school->logo_local);
        } finally {
            Storage::disk('public')->delete($logoPath);
        }
    }

    public function testSchoolLogoLocalFallsBackWhenStoredValueIsInvalid(): void
    {
        $school = new School();
        $school->forceFill(['logo' => 'Mr. Kelvin Ledner']);

        $this->assertSame(storage_path('standard/ballon.webp'), $school->logo_local);
    }

    public function testPlayerPhotoPdfLocalResolvesExistingPublicDiskPath(): void
    {
        $photoPath = 'tests/pdf/player-photo.jpg';
        Storage::disk('public')->put($photoPath, 'player-photo');

        try {
            $player = new Player();
            $player->forceFill(['photo' => $photoPath]);

            $this->assertSame(storage_path('app/public/' . $photoPath), $player->photo_pdf_local);
        } finally {
            Storage::disk('public')->delete($photoPath);
        }
    }

    public function testPlayerPhotoPdfLocalResolvesPublicAssetUrlsToFilesystemPaths(): void
    {
        $player = new Player();
        $player->forceFill(['photo' => url('img/user.webp')]);

        $this->assertSame(public_path('img/user.webp'), $player->photo_pdf_local);
    }
}
