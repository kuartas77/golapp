<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Models\Payment;

final class PaymentStatusCatalog
{
    public static function paidStatuses(): array
    {
        return [
            Payment::$paid,
            Payment::$paid_cash,
            Payment::$paid_deposit,
            Payment::$annuity_payment_deposit,
            Payment::$annuity_payment_cash,
            Payment::$paid_player_credit,
        ];
    }

    public static function debtStatuses(): array
    {
        return [Payment::$debt];
    }

    public static function noApplicationStatuses(): array
    {
        return [Payment::$no_application];
    }

    public static function annuityStatuses(): array
    {
        return [
            Payment::$annuity_payment_deposit,
            Payment::$annuity_payment_cash,
            Payment::$payment_agreement,
        ];
    }

    public static function playerCreditStatuses(): array
    {
        return [Payment::$paid_player_credit];
    }

    public static function editableStatuses(): array
    {
        return Payment::STATUS_VALUES;
    }

    public static function toArray(bool $includePlayerCredit = true): array
    {
        $labels = config('variables.KEY_PAYMENTS_SELECT', []);
        $statuses = collect($labels)
            ->reject(fn (string $label, int|string $value) => ! $includePlayerCredit && (int) $value === Payment::$paid_player_credit);

        return [
            'statuses' => $statuses->map(fn (string $label, int|string $value) => [
                'value' => (string) $value,
                'label' => $label,
                'paid' => in_array((int) $value, self::paidStatuses(), true),
                'debt' => in_array((int) $value, self::debtStatuses(), true),
                'no_application' => in_array((int) $value, self::noApplicationStatuses(), true),
                'annuity' => in_array((int) $value, self::annuityStatuses(), true),
                'player_credit' => in_array((int) $value, self::playerCreditStatuses(), true),
                'editable' => in_array((int) $value, self::editableStatuses(), true),
                'badge_class' => "payments-c-{$value}",
            ])->values()->all(),
            'groups' => [
                'paid' => $includePlayerCredit
                    ? self::paidStatuses()
                    : array_values(array_diff(self::paidStatuses(), self::playerCreditStatuses())),
                'debt' => self::debtStatuses(),
                'no_application' => self::noApplicationStatuses(),
                'annuity' => self::annuityStatuses(),
                'player_credit' => $includePlayerCredit ? self::playerCreditStatuses() : [],
            ],
            'player_credit_enabled' => $includePlayerCredit,
            'months' => collect(config('variables.KEY_INDEX_MONTHS', []))
                ->map(fn (string $field, int|string $number) => [
                    'value' => $field,
                    'number' => (int) $number,
                    'label' => config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field)),
                ])
                ->values()
                ->all(),
        ];
    }
}
