<?php

namespace App\Http\Controllers\API\Notifications;

use App\Custom\FakerTester;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\Invoices\InvoiceCollection;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request): InvoiceCollection
    {
        $player = $request->user();
        $player->load(['inscription.invoices.items']);

        return new InvoiceCollection($player->inscription->invoices);
    }

    public function statistics(Request $request): JsonResponse
    {
        $response = [];

        $player = $request->user();
        $player->load(['inscription.invoices.items']);
        $invoices = data_get($player->inscription, 'invoices', collect());

        if($invoices->isNotEmpty()) {
            $response = [
                'total' => $invoices->count(),
                'pending' => $invoices->where('status', 'pending')->count(),
                'paid' => $invoices->where('status', 'paid')->count(),
                'partial' => $invoices->where('status', 'partial')->count(),
                'cancelled' => $invoices->where('status', 'cancelled')->count(),
                'total_amount' => $invoices->where('status', 'paid')->sum('total_amount'),
            ];
        }

        return response()->json($response);
    }

    public function store(Request $request): JsonResponse
    {
        $tester = new FakerTester;
        $user = $request->user();

            $school = School::find(1);
            $image = $tester->uploadFile($request->image, $school->slug);
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
}
