<?php

namespace App\Http\Controllers\API\Notifications;

use App\Custom\FakerTester;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\Notification\Invoices\InvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\InvoiceResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Models\Invoice;
use App\Models\School;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceRepository $invoice_repository)
    {
        //
    }

    public function index(): InvoiceCollection
    {
        return new InvoiceCollection($this->invoice_repository->invoicesPlayer());
    }

    public function statistics(): InvoiceStatistcsResource|JsonResponse
    {
        return new InvoiceStatistcsResource($this->invoice_repository->statisticsPlayer());
    }

    public function payment(Request $request): JsonResponse
    {
        $tester = new FakerTester;
        $user = $request->user();

        $school = School::find(1);
        $image = $tester->uploadFile($request->image, $school->slug, 'invoice_receipts');
        logger('payment', [$request->all(), $image]);

        $response = [
            'id' => Str::uuid(),
            'invoice_id' => '1',
            'invoice_number' => 'FAC-1010101',
            'amount' => 50000,
            'description' => 'aquí irian todos los items y valor',
            'reference_number' => '40303030303',
            'payment_method' => 'TRANSFER', // 'CASH','CARD','TRANSFER','CHECK','OTHER'
            'status' => 'PENDING', //PENDING, PARTIAL, PAID, CANCELLED
            'image_url' => null,
            'due_date' => now()->addDays(15)->timestamp,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
            'items' => []
        ];

        return response()->json($response, 200);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load('items'));
    }
}
