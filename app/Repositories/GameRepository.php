<?php

namespace App\Repositories;

use Exception;
use App\Models\Game;
use App\Models\Master;
use App\Traits\Fields;
use Mpdf\MpdfException;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use App\Models\SkillsControl;
use App\Models\CompetitionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class GameRepository
{
    use Fields;
    use PDFTrait;
    use ErrorTrait;

    /**
     * @var Game
     */
    private Game $model;

    public function __construct(Game $model)
    {
        $this->model = $model;
    }

    public function getDatatable($year)
    {
        return Game::with([
            'tournament' => fn ($q) => $q->withTrashed(),
            'competitionGroup' => fn ($q) => $q->with('professor')->withTrashed(),
        ])->whereYear('created_at', $year)->orderBy('date', 'desc')->get();
    }

    /**
     * @param Game|null $match
     * @return object
     */
    public function getInformationToMatch(Game $match = null): object
    {
        if (is_null($match)) {
            $competitionGroup = CompetitionGroup::query()->findOrFail(request('competition_group'));
            return $this->makeMatch($competitionGroup);
        }
        return $this->makeMatchEdit($match);
    }

    /**
     * @param $competitionGroup
     * @return object
     */
    public function makeMatch($competitionGroup): object
    {
        $competitionGroup->load([
            'inscriptions' => fn ($q) => $q->with('player'), 
            'tournament:id,name', 'professor:id,name'
        ]);
        $rows = "";
        $count = 0;
        foreach ($competitionGroup->inscriptions as $inscription) {
            $rows .= View::make("templates.competitions.row", [
                'index' => $count,
                'inscription' => $inscription
            ])->render();
            $count++;
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
     * @return object
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
            $count++;
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
     * @return Game
     */
    public function createMatchSkill(array $matchData, array $skillsData): Game
    {
        $match = $this->model;
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($matchData);
            $match = $this->model->create($matchData);
            $inscriptions = $skillsData['inscriptions_id'];
            $skillControls = collect();
            for ($i = 0; $i < count($inscriptions); ++$i) {
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
     * @return array
     */
    private function dataSkills(array $skillsData, $i, $school_id): array
    {
        return [
            'inscription_id' => $skillsData["inscriptions_id"][$i],
            'assistance' => $skillsData["assistance"][$i],
            'titular' => $skillsData["titular"][$i],
            'played_approx' => $skillsData["played_approx"][$i],
            'position' => $skillsData["position"][$i],
            'goals' => $skillsData["goals"][$i],
            'red_cards' => $skillsData["red_cards"][$i],
            'yellow_cards' => $skillsData["yellow_cards"][$i],
            'qualification' => $skillsData["qualification"][$i],
            'observation' => $skillsData["observation"][$i],
            'school_id' => $school_id
        ];
    }

    /**
     * @param $request
     * @param Game $match
     * @return bool
     */
    public function updateMatchSkill(array $matchData, array $skillsData, Game $match): bool
    {
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($matchData);
            $match->update($matchData);
            $ids = $skillsData['ids'];
            for ($i = 0; $i < count($ids); ++$i) {
                $data = $this->dataSkills($skillsData, $i, $matchData['school_id']);
                if (!empty($ids[$i])) {
                    SkillsControl::find($ids[$i])->update($data);
                } else {
                    $match->skillsControls()->save(new SkillsControl($data));
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
        $match = $this->model->query()->with([
            'tournament' => fn ($query) => $query->withTrashed(),
            'competitionGroup' => fn ($query) => 
                $query->with([
                    'professor' => fn ($query) => $query->withTrashed()
                ])->withTrashed(),
            'skillsControls' => fn ($query) => $query->with([
                'inscription' => fn ($query) => $query->with('player')->withTrashed()
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
            'inscriptions' => fn ($q) => $q->with('player')
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
            $count++;
        }

        return (object)[
            'count' => $count,
            'rows' => $rows,
        ];
    }
}
