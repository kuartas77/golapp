<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\InvoiceCustomItem;
use App\Models\Player;
use App\Models\School;
use App\Service\Inscription\InscriptionCustomChargeService;
use Tests\TestCase;

final class InscriptionCustomChargesTest extends TestCase
{
    public function test_service_creates_each_active_catalog_charge_only_once(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $existingCharge = $this->createCharge(
            $school,
            now()->year,
            InscriptionCustomCharge::STATUS_PENDING,
            'Cargo inicial'
        );
        $catalogItem = InvoiceCustomItem::query()->create([
            'type' => 'OTHER',
            'name' => 'Transporte',
            'unit_price' => 35000,
            'school_id' => $school->id,
        ]);
        $payload = [[
            'invoice_custom_item_id' => $catalogItem->id,
            'value' => 32000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]];

        $this->actingAs($this->user);
        $service = app(InscriptionCustomChargeService::class);
        $service->sync($existingCharge->inscription, $payload);
        $service->sync($existingCharge->inscription, $payload);

        $this->assertDatabaseCount('inscription_custom_charges', 2);
        $this->assertDatabaseHas('inscription_custom_charges', [
            'school_id' => $school->id,
            'inscription_id' => $existingCharge->inscription_id,
            'invoice_custom_item_id' => $catalogItem->id,
            'name' => 'Transporte',
            'value' => 32000,
            'status' => InscriptionCustomCharge::STATUS_PENDING,
        ]);
    }

    public function test_service_updates_and_deletes_only_mutable_charges(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $pendingCharge = $this->createCharge(
            $school,
            now()->year,
            InscriptionCustomCharge::STATUS_PENDING,
            'Cargo editable'
        );
        $paidCharge = $this->createCharge(
            $school,
            now()->year,
            InscriptionCustomCharge::STATUS_PAID,
            'Cargo pagado'
        );

        $this->actingAs($this->user);
        $service = app(InscriptionCustomChargeService::class);
        $service->sync($pendingCharge->inscription, [[
            'id' => $pendingCharge->id,
            'value' => 41000,
            'due_date' => now()->addMonth()->toDateString(),
        ]]);
        $service->sync($pendingCharge->inscription, [[
            'id' => $pendingCharge->id,
            '_delete' => true,
        ]]);
        $service->sync($paidCharge->inscription, [[
            'id' => $paidCharge->id,
            'value' => 1,
            '_delete' => true,
        ]]);

        $this->assertSoftDeleted('inscription_custom_charges', [
            'id' => $pendingCharge->id,
            'value' => 41000,
        ]);
        $this->assertDatabaseHas('inscription_custom_charges', [
            'id' => $paidCharge->id,
            'value' => 25000,
            'status' => InscriptionCustomCharge::STATUS_PAID,
            'deleted_at' => null,
        ]);
    }

    public function test_index_returns_datatable_payload_with_visible_custom_charges(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $currentCharge = $this->createCharge($school, now()->year, InscriptionCustomCharge::STATUS_PENDING, 'Cargo vigente');
        $priorDueCharge = $this->createCharge($school, now()->subYear()->year, InscriptionCustomCharge::STATUS_DUE, 'Cargo vencido');
        $this->createCharge($school, now()->subYear()->year, InscriptionCustomCharge::STATUS_PENDING, 'Cargo histórico pendiente');

        $otherSchool = School::query()->create(School::factory()->make([
            'email' => 'other-custom-charges@example.test',
        ])->toArray());
        $otherSchool->trainingGroups()->create([
            'name' => 'Provisional',
            'year' => now()->year,
            'category' => 'Todas las categorías',
            'days' => 'Grupo predeterminado',
            'schedules' => '10:00AM - 11:00AM',
        ]);
        $this->createCharge($otherSchool, now()->year, InscriptionCustomCharge::STATUS_PENDING, 'Cargo de otra escuela');

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/inscription-custom-charges?'.http_build_query($this->datatableParams()))
            ->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonFragment(['name' => $currentCharge->name])
            ->assertJsonFragment(['name' => $priorDueCharge->name])
            ->assertJsonPath('data.0.player_name', $priorDueCharge->player->full_names)
            ->assertJsonPath('data.0.player_unique_code', $priorDueCharge->player->unique_code)
            ->assertJsonPath('data.0.inscription_year', now()->subYear()->year)
            ->assertJsonPath('data.0.invoice_number', null)
            ->assertJsonMissing(['name' => 'Cargo histórico pendiente'])
            ->assertJsonMissing(['name' => 'Cargo de otra escuela']);

        $params = $this->datatableParams();
        $params['order'] = [['column' => 0, 'dir' => 'asc']];

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/inscription-custom-charges?'.http_build_query($params))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $params = $this->datatableParams();
        $params['columns'][0]['search']['value'] = $currentCharge->player->full_names;

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/inscription-custom-charges?'.http_build_query($params))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.player_name', $currentCharge->player->full_names);
    }

    private function datatableParams(): array
    {
        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['data' => 'player_name', 'name' => 'player_name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'inscription_year', 'name' => 'inscriptions.year', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'name', 'name' => 'inscription_custom_charges.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'value', 'name' => 'inscription_custom_charges.value', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'status', 'name' => 'inscription_custom_charges.status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'due_date', 'name' => 'inscription_custom_charges.due_date', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'invoice_number', 'name' => 'invoice_number', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'id', 'name' => 'inscription_custom_charges.id', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [
                ['column' => 7, 'dir' => 'desc'],
            ],
            'search' => [
                'value' => '',
                'regex' => 'false',
            ],
        ];
    }

    private function createCharge(School $school, int $year, string $status, string $name): InscriptionCustomCharge
    {
        $trainingGroup = $school->trainingGroups()->firstOrFail();
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'email' => strtolower(str_replace(' ', '-', $name)).'@example.test',
        ]);

        $inscription = Inscription::withoutEvents(fn () => Inscription::query()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $year,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'start_date' => now()->toDateString(),
            'category' => 'Sub 10',
            'photos' => false,
            'scholarship' => false,
            'copy_identification_document' => false,
            'eps_certificate' => false,
            'medic_certificate' => false,
            'study_certificate' => false,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
            'period_one' => null,
            'period_two' => null,
            'period_three' => null,
            'period_four' => null,
            'school_id' => $school->id,
        ]));

        $customItem = InvoiceCustomItem::query()->create([
            'type' => 'OTHER',
            'name' => $name,
            'unit_price' => 25000,
            'school_id' => $school->id,
        ]);

        return InscriptionCustomCharge::query()->create([
            'school_id' => $school->id,
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'invoice_custom_item_id' => $customItem->id,
            'name' => $name,
            'value' => 25000,
            'status' => $status,
            'due_date' => now()->addWeek()->toDateString(),
        ]);
    }
}
