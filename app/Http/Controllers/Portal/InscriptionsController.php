<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\Inscriptions\Actions\Create\Pipeline as InscriptionsPipeline;
use App\Http\Requests\Portal\InscriptionRegisterRequest;
use App\Http\Controllers\Controller;

class InscriptionsController extends Controller
{
    public function store(InscriptionRegisterRequest $request)
    {
        $response = [];
        $code = 200;
        try {

            DB::beginTransaction();

            InscriptionsPipeline::execute($request->validated());

            DB::commit();

            $response = ['ok'];
        } catch (\Throwable $th) {
            DB::rollBack();
            Cache::forget('KEY_LAST_UNIQUE_CODE');
            report($th);
            $response = ['message' => $th->getMessage()];
            $code = 500;
        }

        return response()->json($response, $code);
    }
}
