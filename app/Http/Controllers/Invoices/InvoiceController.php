<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Http\Requests\InvoiceStoreRequest;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Traits\PDFTrait;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class InvoiceController extends Controller
{
    use PDFTrait;

    public function __construct(private InvoiceRepository $invoice_repository)
    {
        //
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->of($this->invoice_repository->query())->toJson();
        }

        return view('invoices.index');
    }

    public function create($inscriptionId, Request $request)
    {

        $settings = getSchool(auth()->user())->settings;

        [$inscription, $pendingMonths, $pendingUniformRequests, $customItems] = $this->invoice_repository->createInvoice($inscriptionId);

        $data = compact('inscription', 'pendingMonths', 'pendingUniformRequests', 'customItems', 'settings');

        return view('invoices.create', $data);
    }

    public function store(InvoiceStoreRequest $request)
    {
        $invoiceId = $this->invoice_repository->storeInvoice($request);

        Alert::success(env('APP_NAME'), 'Factura creada exitosamente.');

        return redirect()->route('invoices.show', $invoiceId);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['items', 'payments', 'inscription.player', 'trainingGroup', 'paymentRequests'])
            ->schoolId()
            ->findOrFail($id);


        return view('invoices.show', compact('invoice'));
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $this->invoice_repository->addPayment($request, $invoiceId);

        Alert::success(env('APP_NAME'), 'Pago registrado exitosamente.');

        return back();
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        Alert::success(env('APP_NAME'), 'Factura eliminada exitosamente.');

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