<?php

namespace App\Service\Payment;

use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use App\Traits\ErrorTrait;
use stdClass;

class PaymentExportService
{
    use PDFTrait;
    use ErrorTrait;
    
    public function paymentsPdfByGroup($payments, $request, bool $stream)
    {
        if($request->training_group_id != 0){
            $group = TrainingGroup::query()->find($request->training_group_id);
        }else{
            $group = new stdClass();
            $group->name = 'Todos los grupos';
        }

        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['payments'] = $payments;
        $data['group'] = $group;
        
        $filename = "Pagos.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'payments/payments.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }
}
