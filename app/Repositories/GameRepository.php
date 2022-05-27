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
    public function createMatchSkill($request): Game
    {
        $match = $this->model;
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($request);
            $match = $this->model->create(
                $request->only([
                    'tournament_id',
                    'competition_group_id',
                    'date',
                    'hour',
                    'num_match',
                    'place',
                    'rival_name',
                    'final_score',
                    'general_concept'])
            );
            $inscriptions = $request->input('inscriptions_id');
            $skillControls = collect();
            for ($i = 0; $i < count($inscriptions); ++$i) {
                if (!empty($inscriptions[$i])) {
                    $skillControls->push(new SkillsControl($this->dataSkills($request, $i)));
                }
            }
            $match->skillsControls()->saveMany($skillControls);
            DB::commit();
            return $match;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("MatchRepository createMatchSkill", $exception);
            return $match;
        }

    }

    /**
     * @param $request
     * @param $i
     * @return array
     */
    private function dataSkills($request, $i): array
    {
        return [
            'inscription_id' => $request->input("inscriptions_id.{$i}"),
            'assistance' => $request->input("assistance.{$i}"),
            'titular' => $request->input("titular.{$i}"),
            'played_approx' => $request->input("played_approx.{$i}"),
            'position' => $request->input("position.{$i}"),
            'goals' => $request->input("goals.{$i}"),
            'red_cards' => $request->input("red_cards.{$i}"),
            'yellow_cards' => $request->input("yellow_cards.{$i}"),
            'qualification' => $request->input("qualification.{$i}"),
            'observation' => $request->input("observation.{$i}"),
        ];
    }

    /**
     * @param $request
     * @param Game $match
     * @return bool
     */
    public function updateMatchSkill($request, Game $match): bool
    {
        try {
            DB::beginTransaction();
            Master::saveAutoComplete($request);
            $match->fill(
                $request->only([
                    'tournament_id',
                    'competition_group_id',
                    'date',
                    'hour',
                    'num_match',
                    'place',
                    'rival_name',
                    'final_score',
                    'general_concept'])
            )->save();
            $ids = $request->input('ids');
            for ($i = 0; $i < count($ids); ++$i) {
                $data = $this->dataSkills($request, $i);
                if (!empty($ids[$i])) {
                    $skillControl = SkillsControl::find($ids[$i]);
                    $skillControl->fill($data)->save();
                } else {
                    $skillControl = new SkillsControl($data);
                    $match->skillsControls()->save($skillControl);
                }
            }
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("MatchRepository updateMatchSkill", $exception);
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
}
