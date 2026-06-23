<?php

namespace App\Service\Notification;

use App\Models\CompetitionGroup;
use App\Models\Player;
use App\Models\User;
use App\Service\Groups\GroupCatalogCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopicService
{
    public static function generatePlayerTopics(Player $player): array
    {
        $player->loadMissing(['schoolData', 'inscription.trainingGroup', 'inscription.competitionGroup']);

        $team = $player->inscription?->trainingGroup?->name;
        $schoolSlug = $player->schoolData->slug;
        $topics = [];
        $topics[] = self::generateTopic('general', $schoolSlug);
        if (filled($player->category)) {
            $topics[] = self::generateTopic($player->category, $schoolSlug);
        }
        $topics[] = self::generateTopic($player->unique_code, $schoolSlug);
        if (filled($team)) {
            $topics[] = self::generateTopic($team, $schoolSlug);
        }
        foreach ($player->inscription?->competitionGroup ?? [] as $group) {
            $topics[] = self::generateTopic($group->name, $schoolSlug);
        }

        return $topics;
    }

    public static function generateTopic(string $topic, string $schoolSlug): string
    {
        return Str::slug("{$topic}-{$schoolSlug}");
    }

    public static function generateTopicBySchool(User $user)
    {
        $ttl = now()->addMinutes(5);
        $school = getSchool($user);

        $topicCategories = Cache::remember('KEY_TOPIC_CATEGORIES_SCHOOL'.$school->id, $ttl, function () use ($school) {
            $topics = [];
            $categories = DB::table('inscriptions')->select(['category'])->where('school_id', $school->id)->where('year', now()->year)->orderBy('category')->groupBy('category')->get();
            foreach ($categories as $category) {
                $topics[] = [
                    'name' => $category->category,
                    'search' => $category->category,
                    'topic' => self::generateTopic($category->category, $school->slug),
                ];
            }

            return $topics;
        });

        $catalogCache = app(GroupCatalogCache::class);
        $topicGroups = $catalogCache->remember(GroupCatalogCache::TRAINING, (int) $school->id, 'notification-topics', function () use ($school) {
            $topics = [];
            $groups = DB::table('training_groups')->select(['id', 'name'])->where('year_active', now()->year)->where('school_id', $school->id)->get();
            foreach ($groups as $group) {
                $topics[] = [
                    'search' => $group->id,
                    'name' => $group->name,
                    'topic' => self::generateTopic($group->name, $school->slug),
                ];
            }

            return $topics;
        });

        $topicUniqueCodes = Cache::remember('KEY_TOPIC_UNIQUE_CODES_SCHOOL'.$school->id, $ttl, function () use ($school) {
            $topics = [];
            $uniqueCodes = DB::table('inscriptions')
                ->join('players', 'players.id', '=', 'inscriptions.player_id')
                ->select(['players.id as player_id', 'players.unique_code', 'names', 'last_names'])->where('inscriptions.school_id', $school->id)->where('year', now()->year)->whereNull('inscriptions.deleted_at')->get();
            foreach ($uniqueCodes as $uniqueCode) {
                $topics[] = [
                    'search' => $uniqueCode->player_id,
                    'name' => $uniqueCode->names.' '.$uniqueCode->last_names,
                    'topic' => self::generateTopic($uniqueCode->unique_code, $school->slug),
                ];
            }

            return $topics;
        });

        $topicCompetitionGroups = $catalogCache->remember(GroupCatalogCache::COMPETITION, (int) $school->id, 'notification-topics', function () use ($school) {
            $topics = [];
            $competitionGroups = CompetitionGroup::query()
                ->where('competition_groups.school_id', $school->id)
                ->whereHas('inscriptions', fn ($q) => $q, '>', 0)->get();
            foreach ($competitionGroups as $group) {
                $topics[] = [
                    'search' => $group->id,
                    'name' => $group->name,
                    'topic' => self::generateTopic($group->name, $school->slug),
                ];
            }

            return $topics;
        });

        return [$topicCategories, $topicGroups, $topicUniqueCodes, $topicCompetitionGroups];
    }
}
