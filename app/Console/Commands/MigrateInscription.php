<?php

namespace App\Console\Commands;

use App\Custom\Migration;
use Illuminate\Console\Command;

class MigrateInscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inscriptions:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change table in data base';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Migration::InscriptionMigration();
        return 0;
    }
}
