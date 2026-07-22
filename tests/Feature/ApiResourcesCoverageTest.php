<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Resources\API\Inscriptions\InscriptionResource;
use App\Http\Resources\API\LoginPlayerResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\InvoiceResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Http\Resources\API\Notification\Invoices\ItemInvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\ItemInvoiceResource;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationCollection;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationResource;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestCollection;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestResource;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestStatistcsResource;
use App\Http\Resources\API\Players\PlayerResource;
use App\Http\Resources\API\SchoolCollection;
use App\Http\Resources\API\TournamentPays\TournamentPaymentCollection;
use App\Http\Resources\API\TournamentPays\TournamentPaymentResource;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Player;
use App\Models\TopicNotification;
use App\Models\UniformRequest;
use Carbon\Carbon;
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

    public function testUniformRequestResourceIncludesRejectedAtAndPlayerWhenLoaded(): void
    {
        $uniformRequest = new UniformRequest([
            'type' => 'Camiseta',
            'quantity' => 2,
            'size' => 'M',
            'additional_notes' => 'Sin estampado',
            'status' => 'CANCELLED',
            'rejected_at' => '2026-07-03 08:15:00',
            'rejection_reason' => 'Sin inventario',
        ]);
        $uniformRequest->id = 18;
        $uniformRequest->created_at = Carbon::parse('2026-07-01 10:00:00');
        $uniformRequest->updated_at = Carbon::parse('2026-07-02 11:30:00');

        $player = new Player([
            'names' => 'Maria',
            'last_names' => 'Ruiz',
            'unique_code' => 'U-001',
        ]);
        $player->id = 31;
        $uniformRequest->setRelation('player', $player);

        $payload = (new UniformRequestResource($uniformRequest))->resolve(Request::create('/'));

        $this->assertSame(18, $payload['id']);
        $this->assertSame('Camiseta', $payload['type']);
        $this->assertSame('CANCELLED', $payload['status']);
        $this->assertSame('Sin inventario', $payload['rejection_reason']);
        $this->assertSame([
            'id' => 31,
            'full_names' => 'Maria Ruiz',
            'unique_code' => 'U-001',
        ], $payload['player']);
        $this->assertIsNumeric($payload['rejected_at']);
        $this->assertGreaterThan(0, $payload['rejected_at']);
    }

    public function testUniformRequestCollectionMapsRequestsToResources(): void
    {
        $payload = (new UniformRequestCollection(collect([
            new UniformRequest(['type' => 'Medias', 'status' => 'PENDING']),
        ])))->toArray(Request::create('/'));

        $this->assertCount(1, $payload);
        $this->assertInstanceOf(UniformRequestResource::class, $payload[0]);
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

    public function testItemInvoiceCollectionMapsItemsToResources(): void
    {
        $payload = (new ItemInvoiceCollection(collect([
            new InvoiceItem(['description' => 'Mensualidad', 'quantity' => 1, 'unit_price' => 50000]),
        ])))->toArray(Request::create('/'));

        $this->assertCount(1, $payload);
        $this->assertInstanceOf(ItemInvoiceResource::class, $payload[0]);
    }

    public function testInvoiceResourceIncludesItemsAndPlayerWhenRelationsAreLoaded(): void
    {
        $invoice = new Invoice([
            'invoice_number' => 'INV-2026-001',
            'total_amount' => 85000,
            'status' => 'partial',
            'due_date' => Carbon::parse('2026-07-31 00:00:00'),
        ]);
        $invoice->id = 44;
        $invoice->created_at = Carbon::parse('2026-07-01 10:00:00');
        $invoice->updated_at = Carbon::parse('2026-07-02 11:30:00');

        $item = new InvoiceItem([
            'invoice_id' => 44,
            'quantity' => 1,
            'unit_price' => 85000,
            'total' => 85000,
            'is_paid' => false,
            'description' => 'Mensualidad julio',
        ]);
        $item->id = 9;
        $invoice->setRelation('items', collect([$item]));

        $player = new Player([
            'names' => 'Luis',
            'last_names' => 'Perez',
            'unique_code' => 'P-2026',
        ]);
        $player->id = 77;
        $inscription = new Inscription();
        $inscription->setRelation('player', $player);
        $invoice->setRelation('inscription', $inscription);

        $payload = (new InvoiceResource($invoice))->resolve(Request::create('/'));

        $this->assertSame(44, $payload['id']);
        $this->assertSame(44, $payload['invoice_id']);
        $this->assertSame('INV-2026-001', $payload['invoice_number']);
        $this->assertSame('PARTIAL', $payload['status']);
        $this->assertInstanceOf(ItemInvoiceCollection::class, $payload['items']);
        $this->assertSame([
            'id' => 77,
            'full_names' => 'Luis Perez',
            'unique_code' => 'P-2026',
        ], $payload['player']);
    }

    public function testInvoiceCollectionMapsInvoicesToResources(): void
    {
        $payload = (new InvoiceCollection(collect([
            new Invoice(['invoice_number' => 'INV-1', 'status' => 'pending']),
        ])))->toArray(Request::create('/'));

        $this->assertCount(1, $payload);
        $this->assertInstanceOf(InvoiceResource::class, $payload[0]);
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

    public function testLoginPlayerResourceCreatesAccessAndRefreshTokens(): void
    {
        $player = Player::factory()->create(['school_id' => $this->school['id']]);
        $player->abilities = ['player'];

        $payload = (new LoginPlayerResource($player))->resolve(Request::create('/'));

        $this->assertSame('Bearer', $payload['token_type']);
        $this->assertNotEmpty($payload['access_token']);
        $this->assertNotEmpty($payload['refresh_token']);
        $this->assertIsInt($payload['expires_at']);
        $this->assertInstanceOf(PlayerResource::class, $payload['player']);
        $this->assertSame($player->id, $payload['player']->resolve(Request::create('/'))['id']);
    }

    public function testTopicNotificationResourceIncludesPivotReadStatusAndPlayer(): void
    {
        $notification = new TopicNotification([
            'title' => 'Entrenamiento',
            'body' => 'Cambio de horario',
            'type' => 'info',
            'priority' => 'high',
        ]);
        $notification->id = 55;
        $notification->created_at = Carbon::parse('2026-07-04 09:00:00');
        $notification->setRelation('pivot', (object) ['is_read' => true]);

        $player = new Player([
            'names' => 'Carlos',
            'last_names' => 'Lopez',
            'unique_code' => 'TN-001',
        ]);
        $player->id = 91;
        $notification->setRelation('notificationPlayer', $player);

        $payload = (new TopicNotificationResource($notification))->resolve(Request::create('/'));

        $this->assertSame(55, $payload['id']);
        $this->assertSame('Entrenamiento', $payload['title']);
        $this->assertTrue($payload['is_read']);
        $this->assertSame('high', $payload['priority']);
        $this->assertSame([
            'id' => 91,
            'full_names' => 'Carlos Lopez',
            'unique_code' => 'TN-001',
        ], $payload['player']);
    }

    public function testTopicNotificationCollectionMapsNotificationsToResources(): void
    {
        $notification = new TopicNotification(['title' => 'Aviso']);
        $notification->setRelation('pivot', (object) ['is_read' => false]);

        $payload = (new TopicNotificationCollection(collect([$notification])))->toArray(Request::create('/'));

        $this->assertCount(1, $payload);
        $this->assertInstanceOf(TopicNotificationResource::class, $payload[0]);
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
