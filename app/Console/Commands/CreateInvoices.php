<?php

namespace App\Console\Commands;

use App\Models\InscriptionCustomCharge;
use App\Models\School;
use App\Repositories\InvoiceRepository;
use App\Service\Notification\TopicNotificationStoreService;
use App\Service\Notification\TopicService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CreateInvoices extends Command
{
    protected $signature = 'create:invoices';
    protected $description = 'Create monthly invoices';

    public function __construct(
        private InvoiceRepository $repository,
        private TopicNotificationStoreService $notificationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $currentDate = now();

        School::query()
            ->with(['settingsValues'])
            ->withWhereHas('inscriptions', fn($q) => $q->select(['id', 'player_id', 'school_id'])->where('year', now()->year))
            ->where('is_enable',  true)
            ->where('auto_invoice',  true)
            ->chunkById(10, function ($schools) use ($currentDate) {

                foreach ($schools as $school) {
                    if (!$school->hasSchoolPermission('school.feature.system_notify')) {
                        continue;
                    }

                    $topics = [];
                    $playerIds = [];

                    foreach ($school->inscriptions as $inscriptionYear) {

                        [$inscription, $pendingMonths] = $this->repository->makeInvoice($inscriptionYear->id, $school);

                        $pendingUniformRequests = $this->repository->addUniformRequest($inscription->player_id, $school->id);

                        $customCharges = InscriptionCustomCharge::query()
                            ->where('school_id', $school->id)
                            ->where('inscription_id', $inscription->id)
                            ->where('status', InscriptionCustomCharge::STATUS_DUE)
                            ->whereNull('invoice_item_id')
                            ->orderBy('due_date')
                            ->get();

                        if (empty($pendingMonths) && empty($pendingUniformRequests) && $customCharges->isEmpty()) {
                            continue;
                        }

                        if (is_null($inscription->training_group_id)) {
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

                        $this->makeItemsInvoice(
                            $invoiceData,
                            $pendingMonths,
                            $pendingUniformRequests,
                            $customCharges);

                        if (empty($invoiceData['items'])) {
                            continue;
                        }

                        $invoiceData['idempotency_key'] = $this->repository->buildAutoInvoiceIdempotencyKey($invoiceData, $currentDate);

                        $result = $this->repository->storeInvoice($invoiceData);

                        if ($school->hasSchoolPermission('school.feature.system_notify') && $result['created'] === true) {
                            $topics[] = TopicService::generateTopic($inscription->unique_code, $school->slug);
                            $playerIds[] = $inscription->player_id;
                        }
                    }

                    if ($school->hasSchoolPermission('school.feature.system_notify') && !empty($topics) && !empty($playerIds)) {
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

    private function makeItemsInvoice(array &$invoiceData, array $pendingMonths, array $pendingUniformRequests, Collection $customCharges)
    {
        $invoiceData['items'] = array_map(function ($pendingMonth) {
            $pendingMonth['type'] = 'monthly';
            $pendingMonth['description'] = $pendingMonth['name'];
            $pendingMonth['quantity'] = 1;
            $pendingMonth['unit_price'] = $pendingMonth['amount'];
            return $pendingMonth;
        }, $pendingMonths);


        if (!empty($pendingUniformRequests)) {

            $pendingRequest = array_map(function ($pending) {
                $pending['type'] = 'additional';
                $pending['description'] = $pending['description'];
                $pending['quantity'] = $pending['quantity'];
                $pending['uniform_request_id'] = $pending['uniform_request_id'];
                $pending['unit_price'] = $pending['unit_price'];
                return $pending;
            }, $pendingUniformRequests);

            array_push($invoiceData['items'], ...$pendingRequest);
        }

        if ($customCharges->isNotEmpty()) {

            $moreCharges = $customCharges->map(function ($customCharge) {
                $pendingCharge['custom_charge_id'] = $customCharge['id'];
                $pendingCharge['type'] = 'additional';
                $pendingCharge['description'] = $customCharge['name'];
                $pendingCharge['quantity'] = 1;
                $pendingCharge['unit_price'] = $customCharge['value'];
                return $pendingCharge;
            });

            array_push($invoiceData['items'], ...$moreCharges);
        }
    }
}
