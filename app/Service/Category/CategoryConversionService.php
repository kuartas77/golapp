<?php

declare(strict_types=1);

namespace App\Service\Category;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Service\Groups\GroupCatalogCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryConversionService
{
    public function __construct(
        private readonly CategoryFormatService $formatter,
        private readonly GroupCatalogCache $groupCatalogCache,
    ) {}

    public function convertSchool(School $school, string $targetMode): void
    {
        $referenceYear = now()->year;

        Player::query()
            ->withTrashed()
            ->where('school_id', $school->id)
            ->select(['id', 'date_birth'])
            ->chunkById(200, function ($players) use ($targetMode, $referenceYear): void {
                $idsByCategory = $players->groupBy(fn (Player $player) => $this->formatter->formatBirthYearForMode(
                    Carbon::parse($player->date_birth)->year,
                    $targetMode,
                    $referenceYear
                ));

                foreach ($idsByCategory as $category => $playersForCategory) {
                    $playerIds = $playersForCategory->pluck('id')->all();

                    Player::query()->withTrashed()->whereIn('id', $playerIds)->update(['category' => $category]);
                    Inscription::query()->withTrashed()->whereIn('player_id', $playerIds)->update(['category' => $category]);
                }
            });

        TrainingGroup::query()
            ->withTrashed()
            ->where('school_id', $school->id)
            ->select(['id', 'category'])
            ->chunkById(200, function ($groups) use ($targetMode, $referenceYear): void {
                $groups->groupBy(function (TrainingGroup $group) use ($targetMode, $referenceYear): string {
                    return collect($group->category)
                        ->map(fn (string $category) => $this->formatter->convertLabel($category, $targetMode, $referenceYear))
                        ->implode(',');
                })->each(function ($groupsForCategory, string $category): void {
                    DB::table('training_groups')
                        ->whereIn('id', $groupsForCategory->pluck('id')->all())
                        ->update(['category' => $category]);
                });
            });

        CompetitionGroup::query()
            ->withTrashed()
            ->where('school_id', $school->id)
            ->select(['id', 'category', 'categories'])
            ->chunkById(200, function ($groups) use ($targetMode, $referenceYear): void {
                $groups->groupBy(function (CompetitionGroup $group) use ($targetMode, $referenceYear): string {
                    $categories = $group->categories ?: explode(',', (string) $group->category);
                    $converted = collect($categories)
                        ->map(fn (string $category) => $this->formatter->convertLabel($category, $targetMode, $referenceYear))
                        ->pipe(fn ($categories) => $this->formatter->normalizeCategories($categories->all()));

                    return json_encode($converted, JSON_THROW_ON_ERROR);
                })->each(function ($groupsForCategories, string $encodedCategories): void {
                    $categories = json_decode($encodedCategories, true, flags: JSON_THROW_ON_ERROR);

                    DB::table('competition_groups')
                        ->whereIn('id', $groupsForCategories->pluck('id')->all())
                        ->update([
                            'categories' => $encodedCategories,
                            'category' => implode(', ', $categories),
                            'year' => $categories[0] ?? '',
                        ]);
                });
            });
    }

    public function clearSchoolCaches(int $schoolId): void
    {
        $this->groupCatalogCache->invalidateSchool($schoolId);
        Cache::forget("KEY_CATEGORIES_SELECT_{$schoolId}");
        Cache::forget("KEY_CATEGORIES_{$schoolId}");
        Cache::forget("KEY_COMPETITION_GROUPS_{$schoolId}");
        Cache::forget("KEY_TRAINING_GROUPS_{$schoolId}");
        Cache::forget("KEY_TRAINING_GROUPS_ARR_{$schoolId}");
    }
}
