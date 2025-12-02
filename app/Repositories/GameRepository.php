<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Requests\CompetitionRequest;
use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Master;
use App\Models\SkillsControl;
use App\Traits\ErrorTrait;
use App\Traits\PDFTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mpdf\MpdfException;

class GameRepository
{
    use PDFTrait;
    use ErrorTrait;

    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function getDatatable()
    {
        return Game::query()->schoolId()
            ->select(['games.*', 'tournaments.name AS tournament_name', 'competition_groups.name AS competition_group_name'])
            ->join('tournaments', 'tournaments.id', '=', 'games.tournament_id')
            ->join('competition_groups', 'competition_groups.id', '=', 'games.competition_group_id')
            ->whereYear('games.created_at', request('year', now()->year));
    }

    /**
     * @param Game|null $game
     */
    public function getInformationToMatch(?Game $game = null): object
    {
        if (is_null($game)) {
            $competitionGroup = CompetitionGroup::query()->findOrFail(request('competition_group'));
            return $this->makeMatch($competitionGroup);
        }

        return $this->makeMatchEdit($game);
    }

    /**
     * @param $competitionGroup
     */
    public function makeMatch($competitionGroup): object
    {
        $competitionGroup->load([
            'inscriptions' => fn($q) => $q->select(['id', 'player_id'])->where('year', now()->year)->with('player:id,names,last_names,unique_code'),
            'tournament:id,name',
            'professor:id,name'
        ]);

        $match = [];
        $match['competition_group'] = $competitionGroup;
        $match['competition_group_id'] = $competitionGroup->id;
        $match['date'] = null;
        $match['hour'] = null;
        $match['final_score']['soccer'] = 0;
        $match['final_score']['rival'] = 0;
        $match['general_concept'] = null;
        $match['num_match'] = null;
        $match['place'] = null;
        $match['rival_name'] = null;
        $match['school_id'] = $competitionGroup->school_id;
        $match['skills_controls'] = [];

        foreach ($competitionGroup->inscriptions as $inscription) {
            $control = [];
            $control['id'] = null;
            $control['game_id'] = null;
            $control['player'] = $inscription->player;
            $control['inscription_id'] = $inscription->id;
            $control['assistance'] = 0;
            $control['titular'] = 0;
            $control['played_approx'] = 0;
            $control['position'] = '';
            $control['goals'] = 0;
            $control['goal_assists'] = 0;
            $control['goal_saves'] = 0;
            $control['red_cards'] = 0;
            $control['yellow_cards'] = 0;
            $control['qualification'] = 0;
            $control['observation'] = 0;

            array_push($match['skills_controls'], $control);
        }

        return (object) $match;
    }

    /**
     * @param $match
     */
    public function makeMatchEdit($match): object
    {
        $match->loadMissing([
            'competitionGroup' => fn($q) => $q->with(['tournament:id,name', 'professor:id,name']),
            'skillsControls' => fn($q) => $q->with('inscription', fn($q) => $q->select(['id', 'player_id'])->with('player:id,names,last_names,unique_code'))
        ]);

        foreach ($match->skillsControls as $skilControl) {
            $skilControl->player = $skilControl->inscription->player;
            unset($skilControl->inscription);
        }

        return $match;
    }

    /**
     * @param $request
     */
    public function createMatchSkill(CompetitionRequest $request): bool
    {
        $result = false;
        try {
            [$matchData, $skillsData] = $this->getDataFromRequest($request);

            $skillControls = collect();
            DB::beginTransaction();

            $game = new Game($matchData);
            $game->save();

            foreach ($skillsData as $skill) {
                $skill['school_id'] = $matchData['school_id'];
                $skillControls->push(new SkillsControl($skill));
            }

            $game->skillsControls()->saveMany($skillControls);

            DB::commit();
            $result = $game->wasRecentlyCreated;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError(__METHOD__, $exception);
            $result = false;
        }

        return $result;
    }

    /**
     * @param $request
     */
    private function getDataFromRequest(CompetitionRequest $request): array
    {
        $matchData = $request->only([
            'tournament_id',
            'competition_group_id',
            'date',
            'hour',
            'num_match',
            'place',
            'rival_name',
            'final_score',
            'general_concept',
            'school_id'
        ]);
        $skillsData = $request->validated('skill_controls', []);

        return [$matchData, $skillsData];
    }

    /**
     * @param CompetitionRequest $request
     * @param Game $game
     */
    public function updateMatchSkill(CompetitionRequest $request, Game $game): bool
    {
        $result = false;
        try {

            [$matchData, $skillsData] = $this->getDataFromRequest($request);

            DB::beginTransaction();
            $game->update($matchData);

            foreach ($skillsData as $skill) {
                $skill['school_id'] = $matchData['school_id'];

                SkillsControl::query()->updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'inscription_id' => $skill['inscription_id']
                    ],
                    $skill
                );
            }

            DB::commit();
            $result = true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError(__METHOD__, $exception);
            $result = false;
        }

        return $result;
    }


    /**
     * @throws MpdfException
     */
    public function makePDF($matchId)
    {
        $match = $this->game->query()->with([
            'tournament' => fn($query) => $query->withTrashed(),
            'competitionGroup' => fn($query) => $query->with([
                'professor' => fn($query) => $query->withTrashed()
            ])->withTrashed(),
            'skillsControls' => fn($query) => $query->with([
                'inscription' => fn($query) => $query->with('player')->withTrashed()
            ])->withTrashed()

        ])->findOrFail($matchId);


        $data['school'] = getSchool(auth()->user());
        $data['match'] = $match;
        $data['count'] = $match->skillsControls->count() + 1;
        $data['result'] = (20 - $data['count']);
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'match.blade.php');

        return $this->stream("Control De Competencia.pdf");
    }

    public function exportMatchDetail($competitionGroupId)
    {
        $competitionGroup = CompetitionGroup::find($competitionGroupId)->load([
            'inscriptions' => fn($q) => $q->where('year', now()->year)->with('player')
        ]);

        return $competitionGroup->inscriptions;
    }

    public function loadDataFromFile($skillControls): array
    {
        return [
            'skills_controls' => $skillControls,
        ];
    }
}
