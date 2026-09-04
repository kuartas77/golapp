<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Http\Requests\InvoiceStoreRequest;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Service\Invoice\InvoicePdfService;
use App\Support\Invoice\InvoiceDocumentTerminology;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoice_repository,
        private InvoicePdfService $invoicePdfService,
    ) {
        //
    }

    public function index(Request $request)
    {
        return datatables()->of($this->invoice_repository->query())
            ->filterColumn('training_group_id', fn ($query, $keyword) => $query->where('training_group_id', $keyword))
            ->filterColumn('created_at', fn ($query, $keyword) => $this->filterCreatedAtColumn($query, $keyword))
            ->toJson();
    }

    public function creationInscriptions()
    {
        $school = getSchool(auth()->user());

        $inscriptions = DB::table('inscriptions')
            ->select([
                'inscriptions.id',
                'inscriptions.unique_code',
                'players.names as player_names',
                'players.last_names as player_last_names',
                'training_groups.name as training_group_name',
            ])
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'inscriptions.training_group_id')
            ->where('inscriptions.school_id', $school->id)
            ->where('inscriptions.year', now()->year)
            ->whereNull('inscriptions.deleted_at')
            ->whereNull('players.deleted_at')
            ->orderBy('players.last_names')
            ->orderBy('players.names')
            ->get()
            ->map(fn ($inscription) => [
                'id' => $inscription->id,
                'unique_code' => $inscription->unique_code,
                'player_name' => trim("{$inscription->player_names} {$inscription->player_last_names}"),
                'training_group_name' => $inscription->training_group_name,
            ])
            ->values();

        return response()->json(['data' => $inscriptions]);
    }

    private function filterCreatedAtColumn($query, string $keyword)
    {
        $dates = preg_split('/\s+a\s+/', trim($keyword));

        if (! $dates || count($dates) > 2) {
            return $query;
        }

        foreach ($dates as $date) {
            if (! preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date)) {
                return $query;
            }
        }

        try {
            $startDate = CarbonImmutable::parse($dates[0])->startOfDay();
            $endDate = CarbonImmutable::parse($dates[1] ?? $dates[0])->endOfDay();
        } catch (\Throwable) {
            return $query;
        }

        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function create($inscriptionId, Request $request)
    {

        $settings = getSchool(auth()->user())->settings;

        [$inscription, $pendingMonths, $pendingUniformRequests, $customCharges] = $this->invoice_repository->createInvoice($inscriptionId);

        return response()->json([
            'inscription' => $inscription,
            'pendingMonths' => $pendingMonths,
            'pendingUniformRequests' => $pendingUniformRequests,
            'customCharges' => $customCharges,
        ]);
    }

    public function store(InvoiceStoreRequest $request)
    {
        $result = $this->invoice_repository->storeInvoice($request->validated());

        if (is_null($result['id'])) {
            $documentLabel = mb_strtolower(InvoiceDocumentTerminology::singular(
                getSchool(auth()->user())->electronic_invoicing_enabled ? 'electronic' : 'internal'
            ));
            Alert::error(env('APP_NAME'), "No fue posible crear {$documentLabel}.");

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "No fue posible crear {$documentLabel}. Verifica que los conceptos sigan disponibles e intenta nuevamente.",
                ], 422);
            }

            return redirect()->route('invoices.index');
        }

        return response()->json(['id' => $result['id']]);
    }

    public function show($id)
    {
        $relations = [
            'items',
            'payments.creator',
            'inscription.player',
            'trainingGroup',
            'creator',
            'numberRange',
        ];

        if (! isViewer()) {
            $relations['paymentRequests'] = fn ($query) => $query->latest();
        }

        $invoice = Invoice::with($relations)
            ->schoolId()
            ->findOrFail($id);

        return response()->json($invoice);
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $result = $this->invoice_repository->addPayment($request, $invoiceId);

        if ($result['created']) {
            Alert::success(env('APP_NAME'), 'Pago registrado exitosamente.');
        }

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back();
    }

    public function update($invoiceId, $paymentRequestId)
    {
        $this->invoice_repository->addPaymentButton($invoiceId, $paymentRequestId);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $invoice = Invoice::query()
            ->schoolId()
            ->findOrFail($id);

        $documentLabel = mb_strtolower(InvoiceDocumentTerminology::singular($invoice));
        abort_unless(
            in_array($invoice->status, ['pending', 'partial'], true),
            422,
            "Solo se puede eliminar {$documentLabel} cuando está pendiente o parcial."
        );

        $invoice->delete();

        Alert::success(env('APP_NAME'), 'Documento eliminado exitosamente.');

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('invoices.index');
    }

    public function print($id)
    {
        $invoice = Invoice::with(['school', 'items', 'payments.creator', 'inscription.player.people', 'trainingGroup', 'creator', 'numberRange'])
            ->schoolId()
            ->firstWhere('invoice_number', $id);

        abort_if(is_null($invoice), 404, 'not found');

        return $this->invoicePdfService->stream($invoice);
    }
}
