<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Resources\API\Inscriptions\InscriptionResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Http\Resources\API\Notification\Invoices\ItemInvoiceResource;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestStatistcsResource;
use App\Http\Resources\API\Players\PlayerResource;
use App\Http\Resources\API\SchoolCollection;
use App\Http\Resources\API\TournamentPays\TournamentPaymentCollection;
use App\Http\Resources\API\TournamentPays\TournamentPaymentResource;
use Illuminate\Http\Request;
use Tests\TestCase;

final class ApiResourcesCoverageTest extends TestCase
{
    public function testInvoiceStatisticsResourceSummarizesStatusesAndPaidAmount(): void
    {
        $resource = new InvoiceStatistcsResource(collect([
            (object) ['status' => 'pending', 'total_amount' => 1000],
            (object) ['status' => 'paid', 'total_amount' => 2500],
            (object) ['status' => 'paid', 'total_amount' => 3500],
            (object) ['status' => 'partial', 'total_amount' => 900],
            (object) ['status' => 'cancelled', 'total_amount' => 700],
        ]));

        $this->assertSame([
            'total' => 5,
            'pending' => 1,
            'paid' => 2,
            'partial' => 1,
            'cancelled' => 1,
            'total_amount' => 6000,
        ], $resource->toArray(Request::create('/')));
    }

    public function testUniformRequestStatisticsResourceSummarizesStatuses(): void
    {
        $resource = new UniformRequestStatistcsResource(collect([
            (object) ['status' => 'PENDING'],
            (object) ['status' => 'PENDING'],
            (object) ['status' => 'APPROVED'],
            (object) ['status' => 'CANCELLED'],
        ]));

        $this->assertSame([
            'total' => 4,
            'pending' => 2,
            'approved' => 1,
            'cancelled' => 1,
        ], $resource->toArray(Request::create('/')));
    }

    public function testItemInvoiceResourceKeepsInvoiceItemPayloadShape(): void
    {
        $resource = new ItemInvoiceResource((object) [
            'id' => 12,
            'invoice_id' => 34,
            'quantity' => 2,
            'unit_price' => 15000,
            'total' => 30000,
            'is_paid' => true,
            'description' => 'Balon oficial',
        ]);

        $this->assertSame([
            'id' => 12,
            'invoice_id' => 34,
            'quantity' => 2,
            'unit_price' => 15000,
            'total' => 30000,
            'is_paid' => true,
            'description' => 'Balon oficial',
        ], $resource->toArray(Request::create('/')));
    }

    public function testInscriptionResourceKeepsAttendancePayloadShape(): void
    {
        $attendance = [
            'assistance_one' => true,
            'assistance_two' => false,
            'assistance_three' => null,
            'assistance_four' => null,
            'assistance_five' => null,
            'assistance_six' => null,
            'assistance_seven' => null,
            'assistance_eight' => null,
            'assistance_nine' => null,
            'assistance_ten' => null,
            'assistance_eleven' => null,
            'assistance_twelve' => null,
            'assistance_thirteen' => null,
            'assistance_fourteen' => null,
            'assistance_fifteen' => null,
            'assistance_sixteen' => null,
            'assistance_seventeen' => null,
            'assistance_eighteen' => null,
            'assistance_nineteen' => null,
            'assistance_twenty' => null,
            'assistance_twenty_one' => null,
            'assistance_twenty_two' => null,
            'assistance_twenty_three' => null,
            'assistance_twenty_four' => null,
            'assistance_twenty_five' => null,
        ];

        $resource = new InscriptionResource((object) array_merge([
            'id' => 1,
            'school_id' => 2,
            'training_group_id' => 3,
            'inscription_id' => 4,
            'year' => 2026,
            'month' => 7,
            'observations' => 'Sin novedad',
        ], $attendance));

        $this->assertSame(array_merge([
            'id' => 1,
            'school_id' => 2,
            'training_group_id' => 3,
            'inscription_id' => 4,
            'year' => 2026,
            'month' => 7,
        ], $attendance, [
            'observations' => 'Sin novedad',
        ]), $resource->toArray(Request::create('/')));
    }

    public function testSchoolCollectionMapsSchoolSummaryFields(): void
    {
        $school = (object) [
            'id' => 7,
            'name' => 'Golapp Academy',
            'email' => 'academy@example.test',
            'created_at' => '2026-07-01 10:00:00',
            'address' => 'Calle 1',
            'agent' => 'Agent',
            'assists_count' => 1,
            'competition_groups_count' => 2,
            'incidents_count' => 3,
            'inscriptions_count' => 4,
            'matches_count' => 5,
            'payments_count' => 6,
            'phone' => '3001234567',
            'players_count' => 8,
            'skill_controls_count' => 9,
            'slug' => 'golapp-academy',
            'tournaments_count' => 10,
            'training_groups_count' => 11,
            'is_enable' => true,
            'deleted_at' => null,
            'deletion_status' => null,
            'logo_file' => '/logos/golapp.png',
        ];

        $this->assertSame([[
            'id' => 7,
            'name' => 'Golapp Academy',
            'email' => 'academy@example.test',
            'created_at' => '2026-07-01 10:00:00',
            'address' => 'Calle 1',
            'agent' => 'Agent',
            'assists_count' => 1,
            'competition_groups_count' => 2,
            'incidents_count' => 3,
            'inscriptions_count' => 4,
            'matches_count' => 5,
            'payments_count' => 6,
            'phone' => '3001234567',
            'players_count' => 8,
            'skill_controls_count' => 9,
            'slug' => 'golapp-academy',
            'tournaments_count' => 10,
            'training_groups_count' => 11,
            'is_enable' => true,
            'deleted_at' => null,
            'deletion_status' => null,
            'logo' => '/logos/golapp.png',
        ]], (new SchoolCollection(collect([$school])))->toArray(Request::create('/')));
    }

    public function testTournamentPaymentResourceIncludesNestedPlayer(): void
    {
        $payout = $this->fakeTournamentPayout();

        $payload = (new TournamentPaymentResource($payout))->resolve(Request::create('/'));

        $this->assertSame(21, $payload['id']);
        $this->assertSame(45000, $payload['value']);
        $this->assertSame('pending', $payload['status']);
        $this->assertSame(9, $payload['tournament_id']);
        $this->assertSame('TP-001', $payload['unique_code']);
        $this->assertSame(2026, $payload['year']);
        $this->assertInstanceOf(PlayerResource::class, $payload['player']);
        $this->assertSame([
            'id' => 15,
            'unique_code' => 'PL-001',
            'names' => 'Ana',
            'last_names' => 'Gomez',
            'category' => '2013-2014',
            'full_names' => 'Ana Gomez',
            'photo_url' => '/api/img/dynamic/players/ana.png',
            'inscription_id' => 99,
        ], $payload['player']->resolve(Request::create('/')));
    }

    public function testTournamentPaymentCollectionMapsRows(): void
    {
        $payload = (new TournamentPaymentCollection([
            'rows' => collect([$this->fakeTournamentPayout()]),
        ]))->toArray(Request::create('/'));

        $this->assertCount(1, $payload);
        $this->assertInstanceOf(TournamentPaymentResource::class, $payload[0]);
        $this->assertSame(21, $payload[0]->resolve(Request::create('/'))['id']);
    }

    private function fakeTournamentPayout(): object
    {
        return (object) [
            'id' => 21,
            'value' => 45000,
            'status' => 'pending',
            'tournament_id' => 9,
            'unique_code' => 'TP-001',
            'year' => 2026,
            'inscription' => (object) [
                'player' => (object) [
                    'id' => 15,
                    'unique_code' => 'PL-001',
                    'names' => 'Ana',
                    'last_names' => 'Gomez',
                    'category' => '2013-2014',
                    'full_names' => 'Ana Gomez',
                    'photo_url' => '/img/dynamic/players/ana.png',
                    'inscription_id' => 99,
                ],
            ],
        ];
    }
}
