<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Service\Reports\AttendancePaymentReportService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AttendancePaymentReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createReportingViews();
    }

    public function testAttendancePaymentReportServiceFlagsExpectedStatusesOnly(): void
    {
        $this->actingAs($this->user);

        $year = 2026;
        $month = 3;
        $group = $this->createTrainingGroup('Reporte Cruce');

        $expectedFlagged = [
            'APR-DEBT' => 'Asistió con mensualidad en deuda',
            'APR-PARTIAL' => 'Asistió con mensualidad en abono',
            'APR-AGREEMENT' => 'Asistió con acuerdo de pago',
            'APR-NOPAY' => 'Sin registro de mensualidad',
        ];

        foreach ($expectedFlagged as $code => $reason) {
            $inscription = $this->createInscription($this->makeReportPlayer($code), $group, $year);
            $this->createAssist($inscription, $group, $year, $month);

            if ($code !== 'APR-NOPAY') {
                $status = match ($code) {
                    'APR-DEBT' => 2,
                    'APR-PARTIAL' => 3,
                    'APR-AGREEMENT' => 13,
                };

                $this->createPaymentForMonth($inscription, $group, $year, $month, $status);
            } else {
                Payment::query()
                    ->where('inscription_id', $inscription->id)
                    ->where('training_group_id', $group->id)
                    ->where('year', $year)
                    ->delete();
            }
        }

        foreach ([
            ['code' => 'APR-PAID', 'status' => 1],
            ['code' => 'APR-SCHOLAR', 'status' => 8],
            ['code' => 'APR-NA', 'status' => 14],
            ['code' => 'APR-DISABILITY', 'status' => 4],
            ['code' => 'APR-RETREAT', 'status' => 5],
        ] as $case) {
            $inscription = $this->createInscription($this->makeReportPlayer($case['code']), $group, $year);
            $this->createAssist($inscription, $group, $year, $month);
            $this->createPaymentForMonth($inscription, $group, $year, $month, $case['status']);
        }

        $service = app(AttendancePaymentReportService::class);
        $filters = [
            'year' => $year,
            'month' => $month,
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
        ];

        $flaggedRows = $service->monthlyByPlayerQuery($filters)->get()->keyBy('unique_code');

        $this->assertCount(4, $flaggedRows);
        $this->assertEqualsCanonicalizing(array_keys($expectedFlagged), $flaggedRows->keys()->all());

        foreach ($expectedFlagged as $code => $reason) {
            $this->assertSame($reason, $flaggedRows[$code]->flag_reason);
            $this->assertSame(2, (int) $flaggedRows[$code]->total_attendances);
            $this->assertSame(3, (int) $flaggedRows[$code]->total_sessions_registered);
        }

        $summary = $service->monthlyByGroupQuery($filters)->first();

        $this->assertNotNull($summary);
        $this->assertSame('Reporte Cruce', $summary->training_group_name);
        $this->assertSame(9, (int) $summary->players_with_attendance);
        $this->assertSame(4, (int) $summary->flagged_players);
        $this->assertSame(18, (int) $summary->total_attendances);
        $this->assertSame(44.44, (float) $summary->flagged_percentage);
    }

    public function testAttendancePaymentReportApiReturnsMetadataAndDatatablePayloads(): void
    {
        $this->actingAs($this->user);

        $year = 2026;
        $month = 4;
        $group = $this->createTrainingGroup('Cruce API');
        $inscription = $this->createInscription($this->makeReportPlayer('APR-API'), $group, $year);

        $this->createAssist($inscription, $group, $year, $month);
        $this->createPaymentForMonth($inscription, $group, $year, $month, 2);

        $metadata = $this->getJson('/api/v2/reports/attendance-payment?year=2026&month=4')
            ->assertOk();

        $this->assertSame(2026, $metadata->json('defaultYear'));
        $this->assertSame(4, $metadata->json('defaultMonth'));
        $this->assertNotEmpty($metadata->json('years'));
        $this->assertNotEmpty($metadata->json('months'));
        $this->assertNotEmpty($metadata->json('groups'));

        $groupResponse = $this->getJson('/api/v2/reports/attendance-payment/monthly-by-group?draw=1&start=0&length=10&year=2026&month=4')
            ->assertOk();

        $this->assertSame('Cruce API', $groupResponse->json('data.0.training_group_name'));
        $this->assertSame(1, $groupResponse->json('data.0.players_with_attendance'));
        $this->assertSame(1, $groupResponse->json('data.0.flagged_players'));

        $playerResponse = $this->getJson('/api/v2/reports/attendance-payment/monthly-by-player?draw=1&start=0&length=10&year=2026&month=4')
            ->assertOk();

        $this->assertSame('APR-API', $playerResponse->json('data.0.unique_code'));
        $this->assertSame('Debe', $playerResponse->json('data.0.payment_status_label'));
        $this->assertSame('Asistió con mensualidad en deuda', $playerResponse->json('data.0.flag_reason'));
    }

    private function createTrainingGroup(string $name): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'name' => $name,
            'stage' => 'Formativo',
            'year' => (string) now()->year,
            'days' => 'lunes,miercoles',
            'schedules' => '08:00 - 09:00',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
        ]);
    }

    private function makeReportPlayer(string $uniqueCode): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => $uniqueCode,
            'category' => '2010-2011',
        ]);
    }

    private function createInscription(Player $player, TrainingGroup $group, int $year): Inscription
    {
        return Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $year,
            'start_date' => "{$year}-01-15",
            'category' => '2010-2011',
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
    }

    private function createAssist(Inscription $inscription, TrainingGroup $group, int $year, int $month): Assist
    {
        return Assist::query()->create([
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
            'month' => $month,
            'assistance_one' => 1,
            'assistance_two' => 1,
            'assistance_three' => 2,
        ]);
    }

    private function createPaymentForMonth(
        Inscription $inscription,
        TrainingGroup $group,
        int $year,
        int $month,
        int $status
    ): Payment {
        $statusFields = array_fill_keys(array_merge(['enrollment'], Payment::paymentFields()), 0);
        $amountFields = array_fill_keys(array_values(Payment::FIELD_AMOUNT_MAP), 0);
        $monthField = config("variables.KEY_INDEX_MONTHS.{$month}");
        $amountField = Payment::amountFieldFor((string) $monthField);

        $statusFields[$monthField] = $status;
        $amountFields[$amountField] = 50000;

        $payment = Payment::query()->firstOrNew([
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
        ]);

        $payment->fill(array_merge($statusFields, $amountFields, [
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'unique_code' => $inscription->unique_code,
            'year' => $year,
        ]));
        $payment->save();

        return $payment->fresh();
    }

    private function createReportingViews(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS vw_attendance_payment_report_detail');
        DB::statement('DROP VIEW IF EXISTS vw_attendance_monthly_report_detail');

        DB::statement("
            CREATE VIEW vw_attendance_monthly_report_detail AS
            SELECT
                a.school_id,
                a.training_group_id,
                a.inscription_id,
                a.year,
                a.month,
                SUM(
                    CASE WHEN a.assistance_one = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_two = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_three = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_four = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_five = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_six = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_seven = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eight = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_nine = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_ten = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eleven = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twelve = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_thirteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_fourteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_fifteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_sixteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_seventeen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eighteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_nineteen = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_one = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_two = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_three = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_four = 1 THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_five = 1 THEN 1 ELSE 0 END
                ) AS total_attendances,
                SUM(
                    CASE WHEN a.assistance_one IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_two IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_three IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_four IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_five IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_six IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_seven IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eight IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_nine IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_ten IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eleven IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twelve IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_thirteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_fourteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_fifteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_sixteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_seventeen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_eighteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_nineteen IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_one IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_two IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_three IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_four IS NOT NULL THEN 1 ELSE 0 END +
                    CASE WHEN a.assistance_twenty_five IS NOT NULL THEN 1 ELSE 0 END
                ) AS total_sessions_registered
            FROM assists a
            WHERE a.deleted_at IS NULL
            GROUP BY
                a.school_id,
                a.training_group_id,
                a.inscription_id,
                a.year,
                a.month
        ");

        DB::statement("
            CREATE VIEW vw_attendance_payment_report_detail AS
            SELECT
                x.school_id,
                x.training_group_id,
                x.inscription_id,
                x.year,
                x.month,
                x.total_attendances,
                x.total_sessions_registered,
                x.payment_status_code,
                CASE
                    WHEN x.payment_status_code IS NULL THEN 'Sin registro'
                    WHEN x.payment_status_code = 0 THEN 'Pendiente'
                    WHEN x.payment_status_code = 1 THEN 'Pagó'
                    WHEN x.payment_status_code = 9 THEN 'Pagó - Efectivo'
                    WHEN x.payment_status_code = 10 THEN 'Pagó - Consignación'
                    WHEN x.payment_status_code = 11 THEN 'Pago Anualidad Consignación'
                    WHEN x.payment_status_code = 12 THEN 'Pago Anualidad Efectivo'
                    WHEN x.payment_status_code = 13 THEN 'Acuerdo de Pago'
                    WHEN x.payment_status_code = 14 THEN 'No Aplica'
                    WHEN x.payment_status_code = 2 THEN 'Debe'
                    WHEN x.payment_status_code = 3 THEN 'Abonó'
                    WHEN x.payment_status_code = 4 THEN 'Incapacidad'
                    WHEN x.payment_status_code = 5 THEN 'Retiro Temporal'
                    WHEN x.payment_status_code = 6 THEN 'Retiro Definitivo'
                    WHEN x.payment_status_code = 7 THEN 'Otro'
                    WHEN x.payment_status_code = 8 THEN 'Becado'
                    ELSE 'Desconocido'
                END AS payment_status_label,
                CASE
                    WHEN x.total_attendances > 0 THEN 1
                    ELSE 0
                END AS has_attendance,
                CASE
                    WHEN x.total_attendances <= 0 THEN 0
                    WHEN x.payment_status_code IS NULL THEN 1
                    WHEN x.payment_status_code IN (2, 3, 13) THEN 1
                    ELSE 0
                END AS is_flagged,
                CASE
                    WHEN x.total_attendances <= 0 THEN NULL
                    WHEN x.payment_status_code IS NULL THEN 'Sin registro de mensualidad'
                    WHEN x.payment_status_code = 2 THEN 'Asistió con mensualidad en deuda'
                    WHEN x.payment_status_code = 3 THEN 'Asistió con mensualidad en abono'
                    WHEN x.payment_status_code = 13 THEN 'Asistió con acuerdo de pago'
                    ELSE NULL
                END AS flag_reason
            FROM (
                SELECT
                    a.school_id,
                    a.training_group_id,
                    a.inscription_id,
                    a.year,
                    a.month,
                    a.total_attendances,
                    a.total_sessions_registered,
                    CAST(CASE a.month
                        WHEN 1 THEN p.january
                        WHEN 2 THEN p.february
                        WHEN 3 THEN p.march
                        WHEN 4 THEN p.april
                        WHEN 5 THEN p.may
                        WHEN 6 THEN p.june
                        WHEN 7 THEN p.july
                        WHEN 8 THEN p.august
                        WHEN 9 THEN p.september
                        WHEN 10 THEN p.october
                        WHEN 11 THEN p.november
                        WHEN 12 THEN p.december
                    END AS INTEGER) AS payment_status_code
                FROM vw_attendance_monthly_report_detail a
                LEFT JOIN payments p
                    ON p.school_id = a.school_id
                    AND p.training_group_id = a.training_group_id
                    AND p.inscription_id = a.inscription_id
                    AND p.year = a.year
                    AND p.deleted_at IS NULL
            ) x
        ");
    }
}
