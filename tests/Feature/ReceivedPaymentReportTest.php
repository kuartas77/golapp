<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateReceivedPaymentReport;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Notifications\ReceivedPaymentReportNotification;
use App\Service\Reports\ReceivedPaymentReportService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class ReceivedPaymentReportTest extends TestCase
{
    public function test_queue_reservation_outlives_the_large_report_job_timeout(): void
    {
        $job = new GenerateReceivedPaymentReport(1, 1, ['year' => 2026]);

        $this->assertGreaterThan(
            $job->timeout,
            config('queue.connections.database.retry_after'),
        );
    }

    public function test_queued_export_emails_the_generated_pdf_to_the_requesting_user(): void
    {
        Notification::fake();
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, 'PAY-QUEUE', 'Sofía', 'López');
        $this->resetPayment($this->paymentForInscription($inscription))->update([
            'january' => Payment::$paid_cash,
            'january_amount' => 58000,
        ]);
        $job = new GenerateReceivedPaymentReport(
            (int) $this->school['id'],
            (int) $this->user->id,
            [
                'year' => 2026,
                'training_group_id' => 0,
                'show_item_amounts' => true,
                'show_total_paid' => true,
            ],
        );

        $job->handle(app(ReceivedPaymentReportService::class));

        Notification::assertSentTo(
            $this->user,
            ReceivedPaymentReportNotification::class,
            function (ReceivedPaymentReportNotification $notification): bool {
                $message = $notification->toMail($this->user);
                $renderedMessage = $message->render()->toHtml();

                $this->assertSame('Informe de pagos listo', $message->subject);
                $this->assertStringContainsString('Saludos,', $renderedMessage);
                $this->assertStringNotContainsString('Regards,', $renderedMessage);
                $this->assertCount(1, $message->rawAttachments);
                $this->assertSame('application/pdf', $message->rawAttachments[0]['options']['mime']);
                $this->assertStringStartsWith('%PDF', $message->rawAttachments[0]['data']);
                $this->assertStringContainsString('Pagos 2026', $message->rawAttachments[0]['name']);

                return true;
            },
        );
    }

    public function test_all_groups_export_is_queued_for_email_delivery(): void
    {
        Queue::fake();
        $this->actingAs($this->user);

        $this->postJson('/api/v2/reports/received-payments', [
            'year' => 2026,
            'player_search' => 'Sofía López',
            'show_item_amounts' => true,
            'show_total_paid' => true,
        ])
            ->assertAccepted()
            ->assertJsonPath('message', 'El informe será enviado al correo electrónico registrado.');

        Queue::assertPushed(GenerateReceivedPaymentReport::class, function (GenerateReceivedPaymentReport $job): bool {
            return $job->schoolId === (int) $this->school['id']
                && $job->userId === (int) $this->user->id
                && $job->filters === [
                    'year' => 2026,
                    'training_group_id' => 0,
                    'player_search' => 'Sofía López',
                    'show_item_amounts' => true,
                    'show_total_paid' => true,
                ]
                && $job->queue === 'golapp_default';
        });
    }

    public function test_authenticated_school_can_export_the_received_payment_pdf(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, 'PAY-PDF', 'Laura', 'Díaz');
        $this->resetPayment($this->paymentForInscription($inscription))->update([
            'january' => Payment::$paid_deposit,
            'january_amount' => 52000,
        ]);

        $this->get('/api/v2/reports/received-payments/pdf?'.http_build_query([
            'year' => 2026,
            'training_group_id' => $group->id,
            'show_item_amounts' => 1,
            'show_total_paid' => 1,
        ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_controls_item_amounts_and_paid_totals_independently(): void
    {
        $data = [
            'school' => (object) ['logo_local' => '', 'name' => 'Escuela test'],
            'rows' => collect([[
                'unique_code' => 'PAY-1001',
                'student_name' => 'Ana Torres',
                'category' => 'Sub 10',
                'training_group' => 'Grupo 1',
                'payment_items' => [
                    ['label' => 'Enero', 'amount' => 50000],
                    ['label' => 'Uniforme', 'amount' => 25000],
                ],
                'total_paid' => 75000,
            ]]),
            'date' => '19-08-2026 10:00:00',
            'year' => 2026,
            'group' => 'Todos los grupos',
        ];

        $itemAmountsOnly = view('templates.pdf.received-payments', $data + [
            'showItemAmounts' => true,
            'showTotalPaid' => false,
        ])->render();
        $totalsOnly = view('templates.pdf.received-payments', $data + [
            'showItemAmounts' => false,
            'showTotalPaid' => true,
        ])->render();
        $itemAmountsOnlyText = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($itemAmountsOnly)));

        $this->assertStringContainsString('Enero (50.000), Uniforme (25.000)', $itemAmountsOnlyText);
        $this->assertStringNotContainsString('Total Pagado', $itemAmountsOnly);
        $this->assertStringNotContainsString('(50.000)', $totalsOnly);
        $this->assertStringContainsString('Total Pagado', $totalsOnly);
        $this->assertStringContainsString('75.000', $totalsOnly);
    }

    public function test_metadata_returns_years_and_groups_with_applied_payments(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, 'PAY-META', 'Mario', 'Ruiz');
        $this->resetPayment($this->paymentForInscription($inscription))->update([
            'january' => Payment::$paid_cash,
            'january_amount' => 55000,
        ]);

        $response = $this->getJson('/api/v2/reports/received-payments?year=2026');

        $response
            ->assertOk()
            ->assertJsonPath('defaultYear', 2026)
            ->assertJsonPath('years.0.value', 2026)
            ->assertJsonPath('groups.0.value', $group->id);
    }

    public function test_report_consolidates_applied_payments_without_duplicating_invoiced_monthly_payments(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, 'PAY-1001', 'Ana', 'Torres');
        $payment = $this->resetPayment($this->paymentForInscription($inscription));
        $payment->update([
            'january' => Payment::$paid,
            'january_amount' => 60000,
            'february' => Payment::$paid_,
            'february_amount' => 20000,
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-PAY-001',
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'student_name' => 'Ana Torres',
            'total_amount' => 95000,
            'paid_amount' => 95000,
            'status' => 'paid',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-31',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);

        $invoice->items()->create([
            'type' => 'monthly',
            'description' => 'Mensualidad enero',
            'quantity' => 1,
            'unit_price' => 60000,
            'month' => 'january',
            'payment_id' => $payment->id,
            'is_paid' => true,
        ]);
        $uniformItem = $invoice->items()->create([
            'type' => 'additional',
            'description' => 'Uniforme',
            'quantity' => 1,
            'unit_price' => 35000,
            'is_paid' => true,
        ]);

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'invoice_item_id' => $uniformItem->id,
            'name' => 'Uniforme',
            'value' => 35000,
            'status' => InscriptionCustomCharge::STATUS_PAID,
            'due_date' => '2026-02-15',
        ]);

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'name' => 'Carné',
            'value' => 15000,
            'status' => InscriptionCustomCharge::STATUS_PAID,
            'due_date' => '2026-03-15',
        ]);

        $rows = app(ReceivedPaymentReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(130000.0, $rows->first()['total_paid']);
        $this->assertSame(
            ['Febrero (Abonó)', 'Mensualidad enero', 'Uniforme', 'Carné'],
            collect($rows->first()['payment_items'])->pluck('label')->all()
        );
        $this->assertSame(
            [20000.0, 60000.0, 35000.0, 15000.0],
            collect($rows->first()['payment_items'])->pluck('amount')->all()
        );
    }

    public function test_report_includes_applied_statuses_and_excludes_non_monetary_statuses(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, 'PAY-STATUS', 'Tomás', 'León');
        $this->resetPayment($this->paymentForInscription($inscription))->update([
            'enrollment' => Payment::$paid,
            'enrollment_amount' => 10000,
            'january' => Payment::$paid_cash,
            'january_amount' => 20000,
            'february' => Payment::$paid_deposit,
            'february_amount' => 30000,
            'march' => Payment::$annuity_payment_deposit,
            'march_amount' => 40000,
            'april' => Payment::$annuity_payment_cash,
            'april_amount' => 50000,
            'may' => Payment::$paid_player_credit,
            'may_amount' => 60000,
            'june' => Payment::$paid_,
            'june_amount' => 70000,
            'july' => Payment::$debt,
            'july_amount' => 80000,
            'august' => Payment::$payment_agreement,
            'august_amount' => 90000,
            'september' => Payment::$scholarship_recipient,
            'september_amount' => 100000,
        ]);

        $row = app(ReceivedPaymentReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ])->sole();

        $this->assertSame(280000.0, $row['total_paid']);
        $this->assertSame(
            ['Matrícula', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio (Abonó)'],
            collect($row['payment_items'])->pluck('label')->all()
        );
    }

    public function test_report_filters_players_by_full_name_or_unique_code(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $ana = $this->createInscriptionForReport($group, 'PAY-ANA-100', 'Ana María', 'Torres Díaz');
        $luis = $this->createInscriptionForReport($group, 'PAY-LUIS-200', 'Luis', 'Mora');

        foreach ([$ana, $luis] as $inscription) {
            $this->resetPayment($this->paymentForInscription($inscription))->update([
                'january' => Payment::$paid_cash,
                'january_amount' => 50000,
            ]);
        }

        $service = app(ReceivedPaymentReportService::class);
        $filters = [
            'school_id' => $this->school['id'],
            'year' => 2026,
        ];

        $this->assertSame(
            ['PAY-ANA-100'],
            $service->rows($filters + ['player_search' => 'Ana Torres'])->pluck('unique_code')->all(),
        );
        $this->assertSame(
            ['PAY-LUIS-200'],
            $service->rows($filters + ['player_search' => 'LUIS-200'])->pluck('unique_code')->all(),
        );
    }

    public function test_report_does_not_include_payments_from_another_school(): void
    {
        $this->actingAs($this->user);
        $otherSchool = $this->createSchool();
        $otherGroup = TrainingGroup::query()
            ->where('school_id', $otherSchool['id'])
            ->firstOrFail();
        $otherInscription = $this->createInscriptionForReport(
            $otherGroup,
            'PAY-OTHER-SCHOOL',
            'Elena',
            'Mora',
            (int) $otherSchool['id'],
        );
        $this->resetPayment($this->paymentForInscription($otherInscription))->update([
            'january' => Payment::$paid,
            'january_amount' => 50000,
        ]);

        $rows = app(ReceivedPaymentReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertTrue($rows->isEmpty());
    }

    private function defaultTrainingGroup(): TrainingGroup
    {
        return TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
    }

    private function createInscriptionForReport(
        TrainingGroup $group,
        string $uniqueCode,
        string $names,
        string $lastNames,
        ?int $schoolId = null,
    ): Inscription {
        $schoolId ??= (int) $this->school['id'];
        $player = Player::factory()->create([
            'school_id' => $schoolId,
            'unique_code' => $uniqueCode,
            'names' => $names,
            'last_names' => $lastNames,
        ]);
        $tournament = Tournament::query()->create([
            'school_id' => $schoolId,
            'name' => 'Torneo informe de pagos',
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'school_id' => $schoolId,
            'name' => 'Competencia informe de pagos',
            'year' => '2026',
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => 'Sub 10',
        ]);

        return Inscription::factory()->create([
            'school_id' => $schoolId,
            'player_id' => $player->id,
            'unique_code' => $uniqueCode,
            'year' => 2026,
            'training_group_id' => $group->id,
            'competition_group_id' => $competitionGroup->id,
            'category' => 'Sub 10',
        ]);
    }

    private function paymentForInscription(Inscription $inscription): Payment
    {
        return Payment::query()
            ->where('year', 2026)
            ->where('training_group_id', $inscription->training_group_id)
            ->where('inscription_id', $inscription->id)
            ->firstOrFail();
    }

    private function resetPayment(Payment $payment): Payment
    {
        $values = [];

        foreach (Payment::paymentFields() as $field) {
            $values[$field] = Payment::$pending;
            $values[Payment::amountFieldFor($field)] = 0;
        }

        $payment->update($values);

        return $payment->fresh();
    }
}
