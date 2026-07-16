<?php

declare(strict_types=1);

namespace App\Service\Kpi;

use App\Models\Assist;
use App\Models\Payment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class KpiDashboardService
{
    private const ATTENDANCE_FIELDS = [
        'assistance_one',
        'assistance_two',
        'assistance_three',
        'assistance_four',
        'assistance_five',
        'assistance_six',
        'assistance_seven',
        'assistance_eight',
        'assistance_nine',
        'assistance_ten',
        'assistance_eleven',
        'assistance_twelve',
        'assistance_thirteen',
        'assistance_fourteen',
        'assistance_fifteen',
        'assistance_sixteen',
        'assistance_seventeen',
        'assistance_eighteen',
        'assistance_nineteen',
        'assistance_twenty',
        'assistance_twenty_one',
        'assistance_twenty_two',
        'assistance_twenty_three',
        'assistance_twenty_four',
        'assistance_twenty_five',
    ];

    public function __construct(private KpiCacheService $cacheService) {}

    public function resolve(User $user, array $filters = []): array
    {
        $schoolId = (int) getSchool($user)->id;
        $filterMetadata = $this->cacheService->rememberFilters(
            $schoolId,
            fn () => $this->buildFilterMetadata($schoolId)
        );

        $year = (int) ($filters['year'] ?? $filterMetadata['defaultYear']);
        $month = (int) ($filters['month'] ?? $filterMetadata['defaultMonth']);
        $groupOptions = $this->groupOptions($user, $schoolId, $year);
        $selectedGroupId = $this->resolveSelectedGroupId($filters['training_group_id'] ?? null, $groupOptions);

        $payload = $this->cacheService->rememberPayload(
            $schoolId,
            $this->scopeKey($user),
            $year,
            $month,
            $selectedGroupId,
            fn () => $this->buildPayload($user, $schoolId, $year, $month, $selectedGroupId, $groupOptions)
        );

        return array_merge($payload, [
            'filters' => array_merge($filterMetadata, [
                'selectedYear' => $year,
                'selectedMonth' => $month,
                'selectedGroupId' => $selectedGroupId,
            ]),
            'group_options' => $groupOptions->values()->all(),
        ]);
    }

    private function buildFilterMetadata(int $schoolId): array
    {
        $paymentYears = Payment::query()
            ->where('school_id', $schoolId)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->distinct()
            ->pluck('year');

        $assistYears = Assist::query()
            ->where('school_id', $schoolId)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->distinct()
            ->pluck('year');

        $years = $paymentYears
            ->merge($assistYears)
            ->push(now()->year)
            ->map(fn ($year) => (int) $year)
            ->filter(fn (int $year) => $year >= 2000 && $year <= 2100)
            ->unique()
            ->sort()
            ->values()
            ->map(fn (int $year) => [
                'value' => $year,
                'label' => (string) $year,
            ]);

        $months = collect(config('variables.KEY_MONTHS_INDEX', []))
            ->map(fn ($label, $value) => [
                'value' => (int) $value,
                'label' => (string) $label,
            ])
            ->values();

        return [
            'years' => $years->all(),
            'months' => $months->all(),
            'defaultYear' => (int) now()->year,
            'defaultMonth' => (int) now()->month,
        ];
    }

    private function buildPayload(
        User $user,
        int $schoolId,
        int $year,
        int $month,
        ?int $selectedGroupId,
        Collection $groupOptions
    ): array {
        $canViewMonetaryValues = ! $this->isInstructor($user);
        $groupCatalog = $groupOptions->keyBy('value');
        $paymentMetrics = $this->buildPaymentMetrics($schoolId, $year, $selectedGroupId, $groupCatalog);
        $attendanceMetrics = $this->buildAttendanceMetrics($schoolId, $year, $month, $selectedGroupId, $groupCatalog);
        $flaggedMetrics = $this->buildFlaggedMetrics($schoolId, $year, $month, $selectedGroupId, $groupCatalog);

        $summaryCards = collect([
            [
                'key' => 'monthly_revenue',
                'label' => 'Recaudo mensualidades',
                'value' => $paymentMetrics['summary']['total_raised'],
                'format' => 'currency',
                'helper' => 'Acumulado del año',
            ],
            [
                'key' => 'enrollment_revenue',
                'label' => 'Recaudo inscripciones',
                'value' => $paymentMetrics['summary']['total_enrollment'],
                'format' => 'currency',
                'helper' => 'Acumulado del año',
            ],
            [
                'key' => 'payment_compliance',
                'label' => '% cumplimiento global',
                'value' => $paymentMetrics['summary']['total_compliance_percentage'],
                'format' => 'percentage',
                'helper' => 'Acumulado del año',
            ],
            [
                'key' => 'payments_debt',
                'label' => 'Mensualidades con deuda',
                'value' => $paymentMetrics['summary']['monthly_payments_debt'],
                'format' => 'number',
                'helper' => 'Registros del año',
            ],
            [
                'key' => 'attendance_percentage',
                'label' => '% asistencia del mes',
                'value' => $attendanceMetrics['summary']['percentage_attendance'],
                'format' => 'percentage',
                'helper' => 'Mes seleccionado',
            ],
            [
                'key' => 'flagged_players',
                'label' => 'Jugadores observados pago vs asistencia',
                'value' => $flaggedMetrics['summary']['flagged_players'],
                'format' => 'number',
                'helper' => 'Mes seleccionado',
            ],
        ])
            ->when(
                ! $canViewMonetaryValues,
                fn (Collection $cards) => $cards->reject(
                    fn (array $card) => in_array($card['key'], ['monthly_revenue', 'enrollment_revenue'], true)
                )
            )
            ->values()
            ->all();

        $amountPaymentGroupReport = $canViewMonetaryValues
            ? array_merge($paymentMetrics['amount_payment_group_report'], ['mode' => 'default'])
            : [
                'categories' => $paymentMetrics['group_rows']->pluck('label')->all(),
                'data' => [
                    [
                        'type' => 'bar',
                        'name' => '% de cumplimiento',
                        'data' => $paymentMetrics['group_rows']->pluck('percentage_compliance')->all(),
                    ],
                ],
                'mode' => 'compliance_only',
            ];

        $monthlyTrendReport = $canViewMonetaryValues
            ? array_merge($paymentMetrics['monthly_trend_report'], ['mode' => 'default'])
            : [
                'categories' => $paymentMetrics['monthly_trend_report']['categories'],
                'data' => [
                    [
                        'type' => 'line',
                        'name' => 'Pagos',
                        'data' => $paymentMetrics['monthly_trend_report']['data'][1]['data'] ?? [],
                    ],
                ],
                'mode' => 'payments_only',
            ];

        return [
            'summary_cards' => $summaryCards,
            'payment_group_report' => $paymentMetrics['payment_group_report'],
            'amount_payment_group_report' => $amountPaymentGroupReport,
            'monthly_trend_report' => $monthlyTrendReport,
            'attendance_mix_report' => $attendanceMetrics['attendance_mix_report'],
            'rankings' => [
                'compliance' => $this->formatRanking(
                    $paymentMetrics['group_rows']->sortByDesc('percentage_compliance')->take(5),
                    'percentage_compliance',
                    'percentage'
                ),
                'low_attendance' => $this->formatRanking(
                    $attendanceMetrics['group_rows']->sortBy('percentage_attendance')->take(5),
                    'percentage_attendance',
                    'percentage'
                ),
                'debt' => $this->formatRanking(
                    $paymentMetrics['group_rows']->sortByDesc('monthly_payments_debt')->take(5),
                    'monthly_payments_debt',
                    'number'
                ),
                'flagged' => $this->formatRanking(
                    $flaggedMetrics['group_rows']->sortByDesc('flagged_players')->take(5),
                    'flagged_players',
                    'number'
                ),
            ],
            'report_links' => $this->buildReportLinks($year, $month, $selectedGroupId, $canViewMonetaryValues),
            'permissions' => [
                'can_view_monetary_values' => $canViewMonetaryValues,
            ],
            'assist_report' => $attendanceMetrics['attendance_mix_report'],
            'monthly_report' => $monthlyTrendReport,
        ];
    }

    private function buildPaymentMetrics(
        int $schoolId,
        int $year,
        ?int $selectedGroupId,
        Collection $groupCatalog
    ): array {
        $payments = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->when(
                $selectedGroupId,
                fn ($query, $groupId) => $query->where('training_group_id', $groupId),
                fn ($query) => $groupCatalog->isNotEmpty()
                    ? $query->whereIn('training_group_id', $groupCatalog->keys()->all())
                    : $query->whereRaw('1 = 0')
            )
            ->get();

        $monthlyFieldMap = config('variables.KEY_INDEX_MONTHS', []);
        $monthlyLabels = config('variables.KEY_INDEX_MONTHS_LABEL', []);
        $groupRows = [];
        $summary = [
            'total_raised' => 0.0,
            'total_enrollment' => 0.0,
            'monthly_payments_debt' => 0,
            'monthly_payments_paid' => 0,
            'compliance_denominator' => 0,
            'total_compliance_percentage' => 0.0,
        ];
        $monthlyTrend = [];

        foreach ($monthlyFieldMap as $monthNumber => $field) {
            $monthlyTrend[$field] = [
                'amount' => 0.0,
                'payments' => 0,
            ];
        }

        foreach ($payments as $payment) {
            $groupId = (int) $payment->training_group_id;
            $groupConfig = $groupCatalog->get($groupId);

            if (! $groupConfig) {
                continue;
            }

            if (! isset($groupRows[$groupId])) {
                $groupRows[$groupId] = [
                    'training_group_id' => $groupId,
                    'label' => $groupConfig['label'],
                    'total_inscriptions' => 0,
                    'inscription_ids' => [],
                    'total_raised' => 0.0,
                    'total_enrollment' => 0.0,
                    'monthly_payments_paid' => 0,
                    'monthly_payments_debt' => 0,
                    'monthly_payments_scholarship' => 0,
                    'monthly_payments_others' => 0,
                    'compliance_denominator' => 0,
                    'percentage_compliance' => 0.0,
                ];
            }

            $groupRows[$groupId]['inscription_ids'][$payment->inscription_id] = true;
            $enrollmentStatus = $payment->enrollment === null ? null : (int) $payment->enrollment;
            $enrollmentAmount = (float) ($payment->enrollment_amount ?? 0);
            $enrollmentReportAmount = $this->reportAmount($enrollmentStatus, $enrollmentAmount);

            $groupRows[$groupId]['total_enrollment'] += $enrollmentReportAmount;
            $summary['total_enrollment'] += $enrollmentReportAmount;

            foreach ($monthlyFieldMap as $field) {
                $status = $payment->{$field} === null ? null : (int) $payment->{$field};
                $amountField = Payment::amountFieldFor((string) $field);
                $amount = (float) ($amountField ? ($payment->{$amountField} ?? 0) : 0);
                $reportAmount = $this->reportAmount($status, $amount);

                $groupRows[$groupId]['total_raised'] += $reportAmount;
                $summary['total_raised'] += $reportAmount;
                $monthlyTrend[$field]['amount'] += $reportAmount;

                if ($this->statusSumsInReports($status)) {
                    $groupRows[$groupId]['monthly_payments_paid']++;
                    $summary['monthly_payments_paid']++;
                    $monthlyTrend[$field]['payments']++;
                }

                if ($status === Payment::$debt) {
                    $groupRows[$groupId]['monthly_payments_debt']++;
                    $summary['monthly_payments_debt']++;
                }

                if ($status === Payment::$scholarship_recipient) {
                    $groupRows[$groupId]['monthly_payments_scholarship']++;
                }

                if (in_array($status, [
                    Payment::$disability,
                    Payment::$no_application,
                    Payment::$temporary_retirement,
                    Payment::$permanent_retirement,
                ], true)) {
                    $groupRows[$groupId]['monthly_payments_others']++;
                }

                if (! in_array($status, [
                    Payment::$disability,
                    Payment::$no_application,
                    Payment::$scholarship_recipient,
                    Payment::$temporary_retirement,
                    Payment::$permanent_retirement,
                ], true)) {
                    $groupRows[$groupId]['compliance_denominator']++;
                    $summary['compliance_denominator']++;
                }
            }
        }

        $groupRowsCollection = collect($groupRows)
            ->map(function (array $row) {
                $row['total_inscriptions'] = count($row['inscription_ids']);
                unset($row['inscription_ids']);

                $row['total_raised'] = round($row['total_raised'], 2);
                $row['total_enrollment'] = round($row['total_enrollment'], 2);
                $row['percentage_compliance'] = $this->percentage(
                    $row['monthly_payments_paid'],
                    $row['compliance_denominator']
                );

                return $row;
            })
            ->sortBy('label')
            ->values();

        $summary['total_raised'] = round($summary['total_raised'], 2);
        $summary['total_enrollment'] = round($summary['total_enrollment'], 2);
        $summary['total_compliance_percentage'] = $this->percentage(
            $summary['monthly_payments_paid'],
            $summary['compliance_denominator']
        );

        return [
            'summary' => $summary,
            'group_rows' => $groupRowsCollection,
            'payment_group_report' => [
                'categories' => $groupRowsCollection->pluck('label')->all(),
                'data' => [
                    ['name' => 'Pagas', 'data' => $groupRowsCollection->pluck('monthly_payments_paid')->all()],
                    ['name' => 'Con Deuda', 'data' => $groupRowsCollection->pluck('monthly_payments_debt')->all()],
                    ['name' => 'Becados', 'data' => $groupRowsCollection->pluck('monthly_payments_scholarship')->all()],
                    ['name' => 'Otros', 'data' => $groupRowsCollection->pluck('monthly_payments_others')->all()],
                ],
            ],
            'amount_payment_group_report' => [
                'categories' => $groupRowsCollection->pluck('label')->all(),
                'data' => [
                    ['type' => 'column', 'name' => 'Mensualidades', 'data' => $groupRowsCollection->pluck('total_raised')->all()],
                    ['type' => 'column', 'name' => 'Inscripciones', 'data' => $groupRowsCollection->pluck('total_enrollment')->all()],
                    ['type' => 'line', 'name' => '% de cumplimiento', 'data' => $groupRowsCollection->pluck('percentage_compliance')->all()],
                ],
            ],
            'monthly_trend_report' => [
                'categories' => array_values($monthlyLabels),
                'data' => [
                    [
                        'type' => 'column',
                        'name' => 'Valor',
                        'data' => collect(array_keys($monthlyLabels))
                            ->map(fn ($field) => round($monthlyTrend[$field]['amount'] ?? 0, 2))
                            ->all(),
                    ],
                    [
                        'type' => 'line',
                        'name' => 'Pagos',
                        'data' => collect(array_keys($monthlyLabels))
                            ->map(fn ($field) => (int) ($monthlyTrend[$field]['payments'] ?? 0))
                            ->all(),
                    ],
                ],
            ],
        ];
    }

    private function buildAttendanceMetrics(
        int $schoolId,
        int $year,
        int $month,
        ?int $selectedGroupId,
        Collection $groupCatalog
    ): array {
        $groupRows = $this->aggregateAttendanceRows(
            $schoolId,
            $year,
            $month,
            $selectedGroupId,
            $groupCatalog
        );

        $summary = [
            'total_attendances' => (int) $groupRows->sum('total_attendances'),
            'total_absences' => (int) $groupRows->sum('total_absences'),
            'total_excuses' => (int) $groupRows->sum('total_excuses'),
            'total_retreat' => (int) $groupRows->sum('total_retreat'),
            'total_disabilities' => (int) $groupRows->sum('total_disabilities'),
            'total_sessions_registered' => (int) $groupRows->sum('total_sessions_registered'),
            'percentage_attendance' => $this->percentage(
                (int) $groupRows->sum('total_attendances'),
                (int) $groupRows->sum('total_sessions_registered')
            ),
        ];

        return [
            'summary' => $summary,
            'group_rows' => $groupRows,
            'attendance_mix_report' => [
                'categories' => ['Asistencias', 'Excusas', 'Ausencias', 'Retiros', 'Incapacidades'],
                'data' => [
                    $summary['total_attendances'],
                    $summary['total_excuses'],
                    $summary['total_absences'],
                    $summary['total_retreat'],
                    $summary['total_disabilities'],
                ],
            ],
        ];
    }

    private function buildFlaggedMetrics(
        int $schoolId,
        int $year,
        int $month,
        ?int $selectedGroupId,
        Collection $groupCatalog
    ): array {
        $assistRows = Assist::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->where('month', $month)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->when(
                $selectedGroupId,
                fn ($query, $groupId) => $query->where('training_group_id', $groupId),
                fn ($query) => $groupCatalog->isNotEmpty()
                    ? $query->whereIn('training_group_id', $groupCatalog->keys()->all())
                    : $query->whereRaw('1 = 0')
            )
            ->get();

        $payments = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->when(
                $selectedGroupId,
                fn ($query, $groupId) => $query->where('training_group_id', $groupId),
                fn ($query) => $groupCatalog->isNotEmpty()
                    ? $query->whereIn('training_group_id', $groupCatalog->keys()->all())
                    : $query->whereRaw('1 = 0')
            )
            ->get()
            ->keyBy(fn (Payment $payment) => $this->paymentLookupKey(
                (int) $payment->training_group_id,
                (int) $payment->inscription_id,
                (int) $payment->year
            ));

        $groupRows = [];

        foreach ($assistRows as $assist) {
            $groupId = (int) $assist->training_group_id;
            $groupConfig = $groupCatalog->get($groupId);

            if (! $groupConfig) {
                continue;
            }

            $attendance = $this->extractAssistCounts($assist);
            if ($attendance['total_attendances'] <= 0) {
                continue;
            }

            if (! isset($groupRows[$groupId])) {
                $groupRows[$groupId] = [
                    'training_group_id' => $groupId,
                    'label' => $groupConfig['label'],
                    'players_with_attendance' => 0,
                    'flagged_players' => 0,
                    'total_attendances' => 0,
                    'flagged_percentage' => 0.0,
                ];
            }

            $groupRows[$groupId]['players_with_attendance']++;
            $groupRows[$groupId]['total_attendances'] += $attendance['total_attendances'];

            $payment = $payments->get(
                $this->paymentLookupKey($groupId, (int) $assist->inscription_id, $year)
            );
            $statusField = config("variables.KEY_INDEX_MONTHS.{$month}");
            $status = $payment && $statusField ? $payment->{$statusField} : null;

            if ($status === null || in_array((int) $status, [Payment::$debt, Payment::$paid_, Payment::$payment_agreement], true)) {
                $groupRows[$groupId]['flagged_players']++;
            }
        }

        $groupRowsCollection = collect($groupRows)
            ->map(function (array $row) {
                $row['flagged_percentage'] = $this->percentage(
                    $row['flagged_players'],
                    $row['players_with_attendance']
                );

                return $row;
            })
            ->sortBy('label')
            ->values();

        return [
            'summary' => [
                'players_with_attendance' => (int) $groupRowsCollection->sum('players_with_attendance'),
                'flagged_players' => (int) $groupRowsCollection->sum('flagged_players'),
                'total_attendances' => (int) $groupRowsCollection->sum('total_attendances'),
            ],
            'group_rows' => $groupRowsCollection,
        ];
    }

    private function aggregateAttendanceRows(
        int $schoolId,
        int $year,
        int $month,
        ?int $selectedGroupId,
        Collection $groupCatalog
    ): Collection {
        $assists = Assist::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->where('month', $month)
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at'))
            ->when(
                $selectedGroupId,
                fn ($query, $groupId) => $query->where('training_group_id', $groupId),
                fn ($query) => $groupCatalog->isNotEmpty()
                    ? $query->whereIn('training_group_id', $groupCatalog->keys()->all())
                    : $query->whereRaw('1 = 0')
            )
            ->get();

        $groupRows = [];

        foreach ($assists as $assist) {
            $groupId = (int) $assist->training_group_id;
            $groupConfig = $groupCatalog->get($groupId);

            if (! $groupConfig) {
                continue;
            }

            if (! isset($groupRows[$groupId])) {
                $groupRows[$groupId] = [
                    'training_group_id' => $groupId,
                    'label' => $groupConfig['label'],
                    'players' => [],
                    'total_attendances' => 0,
                    'total_absences' => 0,
                    'total_excuses' => 0,
                    'total_retreat' => 0,
                    'total_disabilities' => 0,
                    'total_sessions_registered' => 0,
                    'percentage_attendance' => 0.0,
                ];
            }

            $groupRows[$groupId]['players'][$assist->inscription_id] = true;
            $counts = $this->extractAssistCounts($assist);

            $groupRows[$groupId]['total_attendances'] += $counts['total_attendances'];
            $groupRows[$groupId]['total_absences'] += $counts['total_absences'];
            $groupRows[$groupId]['total_excuses'] += $counts['total_excuses'];
            $groupRows[$groupId]['total_retreat'] += $counts['total_retreat'];
            $groupRows[$groupId]['total_disabilities'] += $counts['total_disabilities'];
            $groupRows[$groupId]['total_sessions_registered'] += $counts['total_sessions_registered'];
        }

        return collect($groupRows)
            ->map(function (array $row) {
                $row['total_jugadores'] = count($row['players']);
                unset($row['players']);

                $row['percentage_attendance'] = $this->percentage(
                    $row['total_attendances'],
                    $row['total_sessions_registered']
                );

                return $row;
            })
            ->sortBy('label')
            ->values();
    }

    private function extractAssistCounts(Assist $assist): array
    {
        $counts = [
            'total_attendances' => 0,
            'total_absences' => 0,
            'total_excuses' => 0,
            'total_retreat' => 0,
            'total_disabilities' => 0,
            'total_sessions_registered' => 0,
        ];

        foreach (self::ATTENDANCE_FIELDS as $field) {
            $status = $assist->{$field};

            if ($status === null) {
                continue;
            }

            $counts['total_sessions_registered']++;

            switch ((int) $status) {
                case 1:
                    $counts['total_attendances']++;
                    break;
                case 2:
                    $counts['total_absences']++;
                    break;
                case 3:
                    $counts['total_excuses']++;
                    break;
                case 4:
                    $counts['total_retreat']++;
                    break;
                case 5:
                    $counts['total_disabilities']++;
                    break;
            }
        }

        return $counts;
    }

    private function groupOptions(User $user, int $schoolId, int $year): Collection
    {
        return TrainingGroup::withTrashed()
            ->where('school_id', $schoolId)
            ->where('name', '!=', 'Provisional')
            ->where('is_complementary', false)
            ->when(
                $this->isInstructor($user),
                fn ($query) => $query->whereHas('instructors', function ($instructorQuery) use ($user, $year) {
                    $instructorQuery
                        ->where('users.id', $user->id)
                        ->where('training_group_user.assigned_year', $year);
                })
            )
            ->orderBy('name')
            ->get()
            ->map(fn (TrainingGroup $group) => [
                'value' => (int) $group->id,
                'label' => $group->name,
            ])
            ->values();
    }

    private function resolveSelectedGroupId(mixed $requestedGroupId, Collection $groupOptions): ?int
    {
        if ($requestedGroupId === null || $requestedGroupId === '') {
            return null;
        }

        $groupId = (int) $requestedGroupId;
        $isValid = $groupOptions->contains(fn (array $group) => (int) $group['value'] === $groupId);

        if (! $isValid) {
            throw ValidationException::withMessages([
                'training_group_id' => 'El grupo seleccionado no está disponible para este panel.',
            ]);
        }

        return $groupId;
    }

    private function formatRanking(Collection $rows, string $valueKey, string $format): array
    {
        return $rows->map(fn (array $row) => [
            'training_group_id' => $row['training_group_id'],
            'label' => $row['label'],
            'value' => $row[$valueKey],
            'format' => $format,
        ])->values()->all();
    }

    private function buildReportLinks(int $year, int $month, ?int $groupId, bool $canViewMonetaryValues): array
    {
        $assistParams = ['year' => $year, 'month' => $month];
        $paymentParams = ['year' => $year];
        $attendancePaymentParams = ['year' => $year, 'month' => $month];

        if ($groupId) {
            $assistParams['training_group_id'] = $groupId;
            $paymentParams['training_group_id'] = $groupId;
            $attendancePaymentParams['training_group_id'] = $groupId;
        }

        return [
            'assists' => '/informes/asistencias?'.http_build_query($assistParams),
            'payments' => $canViewMonetaryValues
                ? '/informes/pagos?'.http_build_query($paymentParams)
                : null,
            'attendance_payment' => '/informes/mensualidades-asistencias?'.http_build_query($attendancePaymentParams),
        ];
    }

    private function scopeKey(User $user): string
    {
        return $this->isInstructor($user) ? "user-{$user->id}" : 'admin';
    }

    private function isInstructor(User $user): bool
    {
        return $user->hasRole('instructor');
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator * 100) / $denominator, 2);
    }

    private function statusSumsInReports(?int $status): bool
    {
        return in_array($status, [
            Payment::$paid,
            Payment::$paid_cash,
            Payment::$paid_deposit,
            Payment::$annuity_payment_deposit,
            Payment::$annuity_payment_cash,
            Payment::$paid_player_credit,
        ], true);
    }

    private function reportAmount(?int $status, float $amount): float
    {
        return $this->statusSumsInReports($status) ? $amount : 0.0;
    }

    private function paymentLookupKey(int $groupId, int $inscriptionId, int $year): string
    {
        return "{$groupId}:{$inscriptionId}:{$year}";
    }
}
