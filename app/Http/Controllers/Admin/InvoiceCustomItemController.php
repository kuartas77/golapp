<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceCustomItemRequest;
use App\Models\InvoiceCustomItem;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

use function Laravel\Prompts\alert;

class InvoiceCustomItemController extends Controller
{
    public function index(Request $request)
    {
        return datatables()->of(InvoiceCustomItem::query()->schoolId())->toJson();
    }

    public function store(InvoiceCustomItemRequest $request)
    {

        $response = [];
        try {
            $invoiceCustomItem = new InvoiceCustomItem();
            $invoiceCustomItem->type = $request->type;
            $invoiceCustomItem->name = $request->name;
            $invoiceCustomItem->unit_price = $request->unit_price;
            $invoiceCustomItem->school_id = getSchool(auth()->user())->id;
            $invoiceCustomItem->save();
            Alert::success(env('APP_NAME'), 'Creado correctamente');
        } catch (\Throwable $th) {
            //throw $th;
            logger('guardando itemcustom', [
                "error" => $th->getMessage(),
                "line" => $th->getLine(),
                "file" => $th->getFile(),
                "code" => $th->getCode(),
            ]);

            if($th->getCode() == '23000') {
                $error = 'Un item de este tipo ya se encuentra registrado';
            }else {
                $error = $th->getMessage();
            }

            Alert::error(env('APP_NAME'), $error);
        }

        return back();
    }

    public function show($id)
    {
        $invoiceCustomItem = InvoiceCustomItem::query()->schoolId()->firstWhere('id', $id);
        return response()->json($invoiceCustomItem);
    }

    public function update($id, InvoiceCustomItemRequest $request)
    {
        $response = [];
        try {
            $invoiceCustomItem = InvoiceCustomItem::query()->schoolId()->firstWhere('id', $id);
            $invoiceCustomItem->type = $request->type;
            $invoiceCustomItem->name = $request->name;
            $invoiceCustomItem->unit_price = $request->unit_price;
            $invoiceCustomItem->school_id = getSchool(auth()->user())->id;
            $invoiceCustomItem->save();
            Alert::success(env('APP_NAME'), 'Modificado correctamente');
        } catch (\Throwable $th) {
            //throw $th;
            logger('ACTUALIZANDO itemcustom', [
                "error" => $th->getMessage(),
                "line" => $th->getLine(),
                "file" => $th->getFile(),
                "code" => $th->getCode(),
            ]);

                        if($th->getCode() == '23000') {
                $error = 'Un item de este tipo ya se encuentra registrado';
            }else {
                $error = $th->getMessage();
            }

            Alert::error(env('APP_NAME'), $error);
        }

        return back();
    }

    public function destroy($id)
    {
        $invoiceCustomItem = InvoiceCustomItem::query()->schoolId()->firstWhere('id', $id);
        $invoiceCustomItem->forceDelete();
        Alert::success(env('APP_NAME'), 'eliminado correctamente');
        return back();
    }
}
