<?php

namespace App\Service\Payment;

use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;

class PaymentExportService
{
    use PDFTrait;
    use ErrorTrait;
    
    public function paymentsPdfByGroup($payments, $request, bool $stream)
    {
        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['payments'] = $payments;
        $data['group'] = TrainingGroup::query()->find($request->training_group_id);
        
        $filename = "Pagos {$data['group']->name}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'payments/payments.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }
}
