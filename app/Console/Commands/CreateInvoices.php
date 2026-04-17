<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Repositories\InvoiceRepository;
use App\Service\Notification\TopicNotificationStoreService;
use App\Service\Notification\TopicService;
use Illuminate\Console\Command;

class CreateInvoices extends Command
{
    protected $signature = 'create:invoices';
    protected $description = 'Create monthly invoices';

    public function __construct(
        private InvoiceRepository $repository,
        private TopicNotificationStoreService $notificationService
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $currentDate = now();

        School::query()
            ->withWhereHas('inscriptions', fn($q) => $q->select(['id', 'player_id', 'school_id'])->where('year', now()->year))
            ->where('is_enable',  true)
            ->chunkById(10, function ($schools) use($currentDate) {

                foreach ($schools as $school) {
                    if (!$school->hasSchoolPermission('school.feature.system_notify')) {
                        continue;
                    }

                    $topics = [];
                    $playerIds = [];

                    foreach ($school->inscriptions as $inscriptionYear) {
                        # code...
                        [$inscription, $pendingMonths] = $this->repository->makeInvoice($inscriptionYear->id, $school);

                        if(empty($pendingMonths)) {
                            continue;
                        }

                        if(is_null($inscription->training_group_id)) {
                            logger("training_group_id is null", [$inscription->id, $inscription->training_group_id]);
                            continue;
                        }

                        $invoiceData = [];
                        $invoiceData['inscription_id'] = $inscription->id;
                        $invoiceData['training_group_id'] = $inscription->training_group_id;
                        $invoiceData['year'] = $currentDate->year;
                        $invoiceData['student_name'] = $inscription->player->full_names;
                        $invoiceData['due_date'] = $currentDate->copy()->addDays(15)->toDateString();
                        $invoiceData['school_id'] = $school->id;
                        $invoiceData['notes'] = 'Generada automaticamente';
                        $invoiceData['items'] = array_map(function($pendingMonth){
                            $pendingMonth['type'] = 'monthly';
                            $pendingMonth['description'] = $pendingMonth['name'];
                            $pendingMonth['quantity'] = 1;
                            $pendingMonth['unit_price'] = $pendingMonth['amount'];
                            return $pendingMonth;
                        }, $pendingMonths);

                        $this->repository->storeInvoice($invoiceData);

                        $topics[] = TopicService::generateTopic($inscription->unique_code, $school->slug);
                        $playerIds[] = $inscription->player_id;
                    }

                    if(!empty($topics) && !empty($playerIds)) {
                        $data = [
                            'school_id' => $school->id,
                            'notification_title' => 'Nueva factura de mensualidad',
                            'notification_body' => 'Esta factura se ha generado automaticamente por el sistema',
                        ];
                        $this->notificationService->saveNotification($data, $topics, $playerIds);
                    }
                }
            });
    }
}
