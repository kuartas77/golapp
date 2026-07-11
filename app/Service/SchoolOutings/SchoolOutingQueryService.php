<?php

declare(strict_types=1);

namespace App\Service\SchoolOutings;

use App\Models\Inscription;
use App\Models\SchoolOuting;
use Illuminate\Support\Collection;

class SchoolOutingQueryService
{
    public function list(int $schoolId): Collection
    {
        return SchoolOuting::query()
            ->where('school_id', $schoolId)
            ->withCount('participants')
            ->withSum('participants', 'target_amount')
            ->withSum('contributions', 'amount')
            ->latest('departure_date')
            ->latest('id')
            ->get();
    }

    public function load(SchoolOuting $outing): SchoolOuting
    {
        return $outing->fresh()
            ->load([
                'activities' => fn ($query) => $query->orderByDesc('is_default')->orderBy('name'),
                'participants' => fn ($query) => $query
                    ->with(['player:id,names,last_names,unique_code,category,photo,school_id', 'inscription.trainingGroup:id,name,category,school_id'])
                    ->withSum('contributions', 'amount')
                    ->orderBy('id'),
                'contributions' => fn ($query) => $query
                    ->with([
                        'participant' => fn ($participantQuery) => $participantQuery
                            ->with('player:id,names,last_names,unique_code,school_id')
                            ->withSum('contributions', 'amount'),
                        'activity:id,name',
                    ])
                    ->latest('contribution_date')
                    ->latest('id'),
            ])
            ->loadCount('participants')
            ->loadSum('participants', 'target_amount')
            ->loadSum('contributions', 'amount');
    }

    public function eligibleInscriptions(int $schoolId, SchoolOuting $outing, array $filters): Collection
    {
        $assignedIds = $outing->participants()->pluck('inscription_id');

        return Inscription::query()
            ->select('inscriptions.*')
            ->with(['player:id,names,last_names,unique_code,category,photo,school_id', 'trainingGroup:id,name,category,school_id'])
            ->where('inscriptions.school_id', $schoolId)
            ->where('inscriptions.year', now()->year)
            ->whereNotIn('inscriptions.id', $assignedIds)
            ->whereHas('player')
            ->when($filters['training_group_id'] ?? null, fn ($query, $id) => $query->where('training_group_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('player', function ($playerQuery) use ($search): void {
                    $playerQuery->where('names', 'like', "%{$search}%")
                        ->orWhere('last_names', 'like', "%{$search}%")
                        ->orWhere('unique_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('category')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (Inscription $inscription) => [
                'id' => $inscription->id,
                'player_id' => $inscription->player_id,
                'player_name' => $inscription->player?->full_names,
                'unique_code' => $inscription->player?->unique_code,
                'category' => $inscription->category,
                'training_group_id' => $inscription->training_group_id,
                'training_group_name' => $inscription->trainingGroup?->full_group ?? $inscription->trainingGroup?->name,
            ])
            ->values();
    }
}
