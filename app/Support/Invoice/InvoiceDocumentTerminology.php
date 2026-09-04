<?php

declare(strict_types=1);

namespace App\Support\Invoice;

use App\Models\Invoice;

final class InvoiceDocumentTerminology
{
    public static function isElectronic(Invoice|string|null $invoice): bool
    {
        $numberingType = $invoice instanceof Invoice ? $invoice->numbering_type : $invoice;

        return $numberingType === 'electronic';
    }

    public static function singular(Invoice|string|null $invoice): string
    {
        return self::isElectronic($invoice) ? 'Factura' : 'Recibo de caja';
    }

    public static function plural(bool $electronicInvoicingEnabled): string
    {
        return $electronicInvoicingEnabled ? 'Facturas' : 'Recibos de caja';
    }

    public static function filename(Invoice $invoice): string
    {
        return sprintf('%s #%s.pdf', self::singular($invoice), $invoice->invoice_number);
    }
}
