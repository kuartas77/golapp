<?php

namespace App\Console\Commands;

use App\Models\Inscription;
use App\Models\Player;
use App\Traits\ErrorTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCategoryPlayers extends Command
{
    use ErrorTrait;

    protected $signature = 'check:categories';

    protected $description = 'check category players.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): Int
    {
        try {

            Player::query()->whereRelation('inscriptions', 'year', '=', now()->year)->chunkByIdDesc(50, function($players){
                foreach ($players as $player) {
                    DB::transaction(function() use($player){
                        $categoryName = categoriesName(Carbon::parse($player->date_birth)->year);
                        $player->category = $categoryName;
                        $player->save();
                        Inscription::query()
                            ->where('player_id', $player->id)
                            ->where('year', now()->year)
                            ->update(['category' => $categoryName]);
                    });
                }
            });
        } catch (\Throwable $th) {
            $this->logError(__CLASS__, $th);
        } finally {
            return 1;
        }
    }
}
