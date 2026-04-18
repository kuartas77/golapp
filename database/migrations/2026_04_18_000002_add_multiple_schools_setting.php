<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => Setting::MULTIPLE_SCHOOLS],
            ['public' => false, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', Setting::MULTIPLE_SCHOOLS)
            ->delete();
    }
};
