<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('games')
            ->where(function ($query): void {
                $query->where('status', 'scheduled')
                    ->orWhereNull('status');
            })
            ->whereNotNull('final_score')
            ->select(['id', 'final_score'])
            ->orderBy('id')
            ->chunkById(200, function ($games): void {
                $gameIds = $games
                    ->filter(fn ($game): bool => $this->hasCompleteScore($game->final_score))
                    ->pluck('id');

                if ($gameIds->isEmpty()) {
                    return;
                }

                DB::table('games')
                    ->whereIn('id', $gameIds)
                    ->where(function ($query): void {
                        $query->where('status', 'scheduled')
                            ->orWhereNull('status');
                    })
                    ->update(['status' => 'played']);
            });
    }

    public function down(): void
    {
        // This data correction cannot be reversed without changing legitimate played matches.
    }

    private function hasCompleteScore(mixed $rawScore): bool
    {
        if (is_string($rawScore)) {
            $score = json_decode($rawScore, true);
        } elseif (is_object($rawScore)) {
            $score = (array) $rawScore;
        } else {
            $score = $rawScore;
        }

        if (! is_array($score)) {
            return false;
        }

        $schoolScore = $score['soccer'] ?? $score['local'] ?? null;
        $rivalScore = $score['rival'] ?? $score['visitor'] ?? null;

        return $this->isValidScore($schoolScore) && $this->isValidScore($rivalScore);
    }

    private function isValidScore(mixed $score): bool
    {
        if (! is_numeric($score)) {
            return false;
        }

        $numericScore = (float) $score;

        return $numericScore >= 0 && floor($numericScore) === $numericScore;
    }
};
