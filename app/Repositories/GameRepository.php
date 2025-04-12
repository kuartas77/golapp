<?php

declare(strict_types=1);

namespace App\Repositories;

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

    public function getDatatable($year)
    {
        return Game::query()->schoolId()->with([
            'tournament' => fn($q) => $q->withTrashed(),
            'competitionGroup' => fn($q) => $q->with('professor')->withTrashed(),
        ])->whereYear('created_at', $year)->orderBy('date', 'desc')->get();
    }

    /**
     * @param Game|null $game
     */
    public function getInformationToMatch(Game $game = null): object
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
            'inscriptions' => fn($q) => $q->where('year', now()->year)->with('player'),
            'tournament:id,name', 'professor:id,name'
        ]);
        $rows = "";
        $count = 0;
        foreach ($competitionGroup->inscriptions as $inscription) {
            $rows .= View::make("templates.competitions.row", [
                'index' => $count,
                'inscription' => $inscription
            ])->render();
            ++$count;
        }

        return (object)[
            'id' => $competitionGroup->id,
            'name' => $competitionGroup->full_name,
            'professor' => $competitionGroup->professor,
            'tournament' => $competitionGroup->tournament,
            'count' => $count,
            'rows' => $rows,
            'match' => new Game
        ];
    }

    /**
     * @param $match
     */
    public function makeMatchEdit($match): object
    {
        $match->loadMissing('competitionGroup', 'skillsControls.inscription.player');
        $rows = "";
        $count = 0;
        foreach ($match->skillsControls as $skillControl) {
            $rows .= View::make("templates.competitions.row_edit", [
                'index' => $count,
                'inscription' => $skillControl->inscription,
                'skillControl' => $skillControl
            ])->render();
            ++$count;
        }

        return (object)[
            'id' => $match->competitionGroup->id,
            'name' => $match->competitionGroup->full_name,
            'professor' => $match->competitionGroup->professor,
            'tournament' => $match->competitionGroup->tournament,
            'count' => $count,
            'rows' => $rows,
            'match' => $match
        ];
    }

    /**
     * @param $request
     */
    public function createMatchSkill(array $matchData, array $skillsData): Game
    {
        $match = $this->game;
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($matchData);
            $match = $this->game->create($matchData);
            $inscriptions = $skillsData['inscriptions_id'];
            $skillControls = collect();
            $counter = count($inscriptions);
            for ($i = 0; $i < $counter; ++$i) {
                if (!empty($inscriptions[$i])) {
                    $skillControls->push(new SkillsControl($this->dataSkills($skillsData, $i, $matchData['school_id'])));
                }
            }

            $match->skillsControls()->saveMany($skillControls);
            DB::commit();
            return $match;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError(__METHOD__, $exception);
            return $match;
        }
    }

    /**
     * @param $request
     * @param $i
     */
    private function dataSkills(array $skillsData, int $i, $school_id): array
    {
        return [
            'inscription_id' => $skillsData["inscriptions_id"][$i],
            'assistance' => $skillsData["assistance"][$i],
            'titular' => $skillsData["titular"][$i],
            'played_approx' => intval($skillsData["played_approx"][$i]),
            'position' => $skillsData["position"][$i],
            'goals' =>  intval($skillsData["goals"][$i]),
            'red_cards' => intval($skillsData["red_cards"][$i]),
            'yellow_cards' => intval($skillsData["yellow_cards"][$i]),
            'qualification' => $skillsData["qualification"][$i] == '' ? 1 : $skillsData["qualification"][$i],
            'observation' => $skillsData["observation"][$i],
            'school_id' => $school_id
        ];
    }

    /**
     * @param $request
     */
    public function updateMatchSkill(array $matchData, array $skillsData, Game $game): bool
    {
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($matchData);
            $game->update($matchData);
            $ids = $skillsData['ids'];
            $counter = count($ids);
            for ($i = 0; $i < $counter; ++$i) {
                $data = $this->dataSkills($skillsData, $i, $matchData['school_id']);
                if (!empty($ids[$i])) {
                    SkillsControl::find($ids[$i])->update($data);
                } else {
                    $game->skillsControls()->save(new SkillsControl($data));
                }
            }

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError(__METHOD__, $exception);
            return false;
        }
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
            'inscriptions' => fn($q) => $q->with('player')
        ]);

        return $competitionGroup->inscriptions;
    }

    public function loadDataFromFile($skillControls)
    {
        $rows = "";
        $count = 0;
        foreach ($skillControls as $skillControl) {
            $rows .= View::make("templates.competitions.row_edit", [
                'index' => $count,
                'inscription' => $skillControl->inscription,
                'skillControl' => $skillControl
            ])->render();
            ++$count;
        }

        return (object)[
            'count' => $count,
            'rows' => $rows,
        ];
    }
}
