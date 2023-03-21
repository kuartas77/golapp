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
    public function makePDFInscriptionDetail($player_id, $inscription_id, $year = null, $quarter = null, bool $stream = true)
    {
        $from = null;
        $to = null;
        $quarter_text = 'Actuales';
        switch ($quarter) {
            case 'quarter_one':
                $quarter_text = 'Trimestre 1';
                $from = Carbon::parse('01-01-2023')->year((int)$year);
                $to = Carbon::parse('31-03-2023')->year((int)$year);
                break;
            case 'quarter_two':
                $quarter_text = 'Trimestre 2';
                $from = Carbon::parse('01-04-2023')->year((int)$year);
                $to = Carbon::parse('30-06-2023')->year((int)$year);
                break;
            case 'quarter_three':
                $quarter_text = 'Trimestre 3';
                $from = Carbon::parse('01-07-2023')->year((int)$year);
                $to = Carbon::parse('30-09-2023')->year((int)$year);
                break;
            case 'quarter_four':
                $quarter_text = 'Trimestre 4';
                $from = Carbon::parse('01-10-2023')->year((int)$year);
                $to = Carbon::parse('31-12-2023')->year((int)$year);
                break;
            default:
                $from = null;
                $to = null;
                break;
        }

        $player = Player::query()->with([
            'schoolData', 
            'inscriptions' => fn ($q) => $q->where('id', $inscription_id)->with([
                'payments',
                'skillsControls' => fn($q) => $q->when(($from && $to), fn($q) => $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to))
            ])
        ])->find($player_id);

        $player->inscriptions->setAppends(['format_average']);
        $data['player'] = $player;
        $data['school'] = $player->schoolData;
        $data['show_payments_assists'] = !($from && $to);
        $data['quarter_text'] = $quarter_text;
        $filename = "Deportista {$player->unique_code}.pdf";
        $this->setConfigurationMpdf(['format' => ($from && $to) ? [213, 140] : 'A4-L']);        
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
}
