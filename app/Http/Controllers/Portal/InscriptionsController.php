<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\Inscriptions\Actions\Create\Pipeline as InscriptionsPipeline;
use App\Http\Requests\Portal\InscriptionRegisterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InscriptionsController extends Controller
{
    public function clientError(Request $request): JsonResponse
    {
        $context = $request->validate([
            'school_slug' => ['required', 'string', 'max:255'],
            'endpoint' => ['nullable', 'string', 'max:500'],
            'error_code' => ['nullable', 'string', 'max:100'],
            'error_message' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'integer', 'between:100,599'],
            'online' => ['nullable', 'boolean'],
            'total_file_bytes' => ['nullable', 'integer', 'min:0'],
            'file_sizes' => ['nullable', 'array'],
            'file_sizes.*' => ['integer', 'min:0'],
        ]);

        Log::warning('Portal inscription failed in browser', [
            ...$context,
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json(['reported' => true]);
    }

    public function store(InscriptionRegisterRequest $request)
    {
        $response = [];
        $code = 200;
        try {

            DB::beginTransaction();

            InscriptionsPipeline::execute($request->validated());

            DB::commit();

            $response = ['ok'];
        } catch (ValidationException $th) {
            DB::rollBack();
            Cache::forget('KEY_LAST_UNIQUE_CODE');
            Log::warning('Portal inscription rejected by business validation', [
                'school_slug' => $request->route('school'),
                'error_fields' => array_keys($th->errors()),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ]);
            $response = [
                'message' => $th->getMessage(),
                'errors' => $th->errors(),
            ];
            $code = 422;
        } catch (\Throwable $th) {
            DB::rollBack();
            Cache::forget('KEY_LAST_UNIQUE_CODE');
            Log::error('Portal inscription failed in backend', [
                'school_slug' => $request->route('school'),
                'exception' => $th::class,
                'message' => $th->getMessage(),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ]);
            report($th);
            $response = ['message' => $th->getMessage()];
            $code = 500;
        }

        return response()->json($response, $code);
    }
}
