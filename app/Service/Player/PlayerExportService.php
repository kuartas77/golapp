<?php

namespace App\Service\Player;

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
    public function makePDFInscriptionDetail($player_id, $inscription_id, bool $stream = true)
    {
        $player = Player::query()->with([
            'schoolData', 'inscriptions' => fn ($q) => $q->where('id', $inscription_id)->with('payments')
        ])->find($player_id);

        $player->inscriptions->setAppends(['format_average']);
        $data['player'] = $player;
        $data['school'] = $player->schoolData;
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
        $player->setAppends(['photo_url']);
        $player->photo_url = $player->photo_url;
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
