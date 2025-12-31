<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Http\Requests\InvoiceStoreRequest;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Traits\PDFTrait;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use PDFTrait;

    public function __construct(private InvoiceRepository $invoice_repository)
    {
        //
    }

    public function index(Request $request)
    {
        return datatables()->of($this->invoice_repository->query())
        ->filterColumn('created_at', fn($query, $keyword) => $query->whereBetween('created_at', explode(' a ', $keyword)))
        ->toJson();
    }

    public function create($inscriptionId)
    {
        [$inscription, $pendingMonths] = $this->invoice_repository->createInvoice($inscriptionId);

        return response()->json([
            'inscription' => $inscription,
            'pendingMonths' => $pendingMonths
        ]);
    }

    public function store(InvoiceStoreRequest $request)
    {
        $invoiceId = $this->invoice_repository->storeInvoide($request);

        return response()->json(['id' => $invoiceId]);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['items', 'payments', 'inscription.player', 'trainingGroup', 'creator'])
            ->findOrFail($id);

        return response()->json($invoice);
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $this->invoice_repository->addPayment($request, $invoiceId);

        alert()->success(env('APP_NAME'), 'Pago registrado exitosamente.');

        return back();
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        alert()->success(env('APP_NAME'), 'Factura eliminada exitosamente.');

        return redirect()->route('invoices.index');
    }

    public function print($id)
    {
        $invoice = Invoice::with(['school', 'items', 'payments.creator', 'inscription.player.people', 'trainingGroup', 'creator'])
            ->firstWhere('invoice_number', $id);


        abort_if(is_null($invoice), 404, 'not found');

        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['invoice'] = $invoice;
        $data['tutor'] = $invoice->inscription->player->people->firstWhere('tutor', 1);


        // view()->share('school', $data['school']);
        // view()->share('invoice', $data['invoice']);
        // view()->share('tutor', $data['tutor']);
        // return view('templates.pdf.invoice');

        $filename = "Factura #{$invoice->invoice_number}.pdf";
        // $this->setConfigurationMpdf(['format' => [140, 200], 'mode' => 'utf-8', 'default_font' => 'dejavusans',]);
        $this->setConfigurationMpdf(['format' => 'A4','default_font' => 'dejavusans']);
        $this->createPDF($data, 'invoice.blade.php');
        return $this->stream($filename);
    }
}