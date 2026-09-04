<?php

declare(strict_types=1);

namespace App\Service\Invoice;

use App\Models\Invoice;
use App\Support\Invoice\InvoiceDocumentTerminology;
use App\Traits\PDFTrait;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

final class InvoicePdfService
{
    use PDFTrait;

    /** @return array{content:string, filename:string, mime:string} */
    public function attachment(Invoice $invoice): array
    {
        $this->prepare($invoice);

        return [
            'content' => $this->getMpdf()->Output(null, Destination::STRING_RETURN),
            'filename' => InvoiceDocumentTerminology::filename($invoice),
            'mime' => 'application/pdf',
        ];
    }

    public function stream(Invoice $invoice): Response
    {
        $attachment = $this->attachment($invoice);

        return response($attachment['content'], 200, [
            'Content-Type' => $attachment['mime'],
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $attachment['filename']).'"',
        ]);
    }

    private function prepare(Invoice $invoice): void
    {
        $invoice->loadMissing([
            'school',
            'items',
            'payments.creator',
            'inscription.player.people',
            'trainingGroup',
            'creator',
            'numberRange',
        ]);

        $data = [
            'school' => $invoice->school,
            'invoice' => $invoice,
            'tutor' => $invoice->inscription->player->people->firstWhere('tutor', 1),
            'documentLabel' => InvoiceDocumentTerminology::singular($invoice),
            'isElectronicDocument' => InvoiceDocumentTerminology::isElectronic($invoice),
        ];

        $this->setConfigurationMpdf(['format' => 'A4', 'default_font' => 'dejavusans']);
        $this->createPDF($data, 'invoice.blade.php');
    }
}
