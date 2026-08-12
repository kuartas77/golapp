<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Service\Category\CategoryFormatService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => Setting::CATEGORY_FORMAT],
            ['public' => false, 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('schools')->select('id')->chunkById(100, function ($schools): void {
            foreach ($schools as $school) {
                DB::table('setting_values')->updateOrInsert(
                    ['school_id' => $school->id, 'setting_key' => Setting::CATEGORY_FORMAT],
                    ['value' => CategoryFormatService::SUB_AGE, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        Schema::table('competition_groups', function (Blueprint $table): void {
            $table->json('categories')->nullable()->after('category');
        });

        DB::table('competition_groups')
            ->select(['id', 'category'])
            ->whereNull('categories')
            ->chunkById(200, function ($groups): void {
                foreach ($groups as $group) {
                    $categories = collect(explode(',', (string) $group->category))
                        ->map(fn (string $category) => trim($category))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    DB::table('competition_groups')
                        ->where('id', $group->id)
                        ->update(['categories' => json_encode($categories, JSON_THROW_ON_ERROR)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('competition_groups', function (Blueprint $table): void {
            $table->dropColumn('categories');
        });

        DB::table('setting_values')->where('setting_key', Setting::CATEGORY_FORMAT)->delete();
        DB::table('settings')->where('key', Setting::CATEGORY_FORMAT)->delete();
    }
};
