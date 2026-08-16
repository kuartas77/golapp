<?php

use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', Player::class)
            ->delete();

        DB::table('players')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($players): void {
                foreach ($players as $player) {
                    DB::table('players')
                        ->where('id', $player->id)
                        ->update(['password' => Hash::make(Str::random(64))]);
                }
            });
    }

    public function down(): void
    {
        // Credential rotation is intentionally irreversible.
    }
};
