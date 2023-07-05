<?php

namespace App\Service\Player;

use Carbon\Carbon;
use App\Models\Player;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use Illuminate\Database\Eloquent\Collection;

class PlayerExportService
{
    use PDFTrait;
    use ErrorTrait;

    /**
     * @throws MpdfException
     */
    public function makePDFInscriptionDetail(int $player_id, int $inscription_id, string $year = null, string $quarter = '', bool $stream = true)
    {
        $from = null;
        $to = null;
        $year = $year ? (int)$year : now()->year;
        $quarter_text = 'Actuales';
        $months = [];
        $months_ = config('variables.KEY_MONTHS_INDEX');
        $observations_assists = [];
        $observations_skills = [];

        $this->quarter($quarter, $from, $to, $quarter_text, $year, $months);

        $player = Player::query()->with([
            'schoolData',
            'inscriptions' => fn ($q) => $q->where('id', $inscription_id)->with([
                'trainingGroup',
                'assistance' => fn($q) => $q->when($months, fn($q) => $q->whereIn('month', $months)),
                'payments',
                'skillsControls' => fn($q) => $q->when(($from && $to), fn($q) => $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to))
            ])
        ])->find($player_id);

        $player->inscriptions->each(function($inscription) use($months_, &$observations_assists, &$observations_skills){
            $observations_assists = $inscription->assistance->where('observations', '<>', null);
            $observations_skills = $inscription->skillsControls->where('observation', '<>', null);
            foreach ($inscription->assistance as $assistance) {
                $assistance->classDays = classDays(
                    $assistance->year,
                    array_search($assistance->month, $months_, true),
                    array_map('dayToNumber', $inscription->trainingGroup->explode_name['days'])
                );
            }
        });

        $player->inscriptions->setAppends(['format_average']);
        $data['player'] = $player;
        $data['school'] = $player->schoolData;
        $data['show_payments_assists'] = !($from && $to);
        $data['quarter_text'] = $quarter_text;
        $data['quarter'] = $quarter;
        $data['observations_assists'] = $observations_assists;
        $data['observations_skills'] = $observations_skills;
        $data['optionAssist'] = config('variables.KEY_ASSIST_LETTER');
        $filename = "Deportista {$player->unique_code}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'inscription_detail.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    /**
     * @throws MpdfException
     */
    public function makePDFPlayer(Player $player, bool $stream = true): mixed
    {
        $player->load(['schoolData', 'people','inscription' => fn($query) => $query->with(['trainingGroup','competitionGroup'])]);
        $player->setAppends(['photo_local']);
        $player->photo_local = $player->photo_local;
        $data['player'] = $player;
        $data['school'] = $player->schoolData;
        $filename = "Deportista {$player->unique_code}.pdf";

        $this->setConfigurationMpdf(['format' => [213, 140]]);
        $this->createPDF($data, 'inscription.blade.php');

        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    public function getExcel(): Collection
    {
        $collection = new Collection(['enabled' => collect(), 'disabled' => collect()]);

        $playersEnabled = Player::query()->schoolId()
        ->whereHas('inscription')
        ->with([
            'inscription.trainingGroup',
            'people' => fn($query)=> $query->where('tutor', true),
            'payments' => fn ($q) => $q->withTrashed()
        ])->get();

        $playersDisabled = Player::query()->schoolId()
        ->whereDoesntHave('inscription')
        ->with([
            'inscription.trainingGroup',
            'people' => fn($query)=> $query->where('tutor', true),
            'payments' => fn ($q) => $q->withTrashed()
        ])->get();

        $collection['enabled'] = $playersEnabled;
        $collection['disabled'] = $playersDisabled;

        return $collection;
    }

    private function quarter(string $quarter, &$from, &$to, string &$quarter_text, int $year, array &$months)
    {
        switch ($quarter) {
            case 'quarter_one':
                $quarter_text = 'Primer trimestre';
                $from = Carbon::parse('01-01-2023')->year($year);
                $to = Carbon::parse('31-03-2023')->year($year);
                $months = ['Enero', 'Febrero', 'Marzo'];
                break;
            case 'quarter_two':
                $quarter_text = 'Segundo trimestre';
                $from = Carbon::parse('01-04-2023')->year($year);
                $to = Carbon::parse('30-06-2023')->year($year);
                $months = ['Abril', 'Mayo', 'Junio'];
                break;
            case 'quarter_three':
                $quarter_text = 'Tercer trimestre';
                $from = Carbon::parse('01-07-2023')->year($year);
                $to = Carbon::parse('30-09-2023')->year($year);
                $months = ['Julio', 'Agosto', 'Septiembre'];
                break;
            case 'quarter_four':
                $quarter_text = 'Cuarto trimestre';
                $from = Carbon::parse('01-10-2023')->year($year);
                $to = Carbon::parse('31-12-2023')->year($year);
                $months = ['Octubre', 'Noviembre', 'Diciembre'];
                break;
            default:
                $from = null;
                $to = null;
                $months = [];
                break;
        }
    }
}
