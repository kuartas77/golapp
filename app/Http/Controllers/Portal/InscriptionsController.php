<?php

namespace App\Http\Controllers\Portal;

use App\Models\School;
use App\Service\Portal\GuardianEmailVerificationService;
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
    public function requestGuardianEmailCode(
        Request $request,
        School $school,
        GuardianEmailVerificationService $verificationService
    ): JsonResponse {
        abort_unless($school->is_enable && $school->inscriptions_enabled, 404);

        $validated = $request->validate([
            'tutor_num_doc' => ['required', 'string', 'max:50'],
            'tutor_email' => ['required', 'string', 'email:rfc', 'max:50'],
        ]);

        return response()->json($verificationService->requestCode(
            $school,
            $validated['tutor_num_doc'],
            $validated['tutor_email'],
            (string) $request->ip()
        ));
    }

    public function confirmGuardianEmailCode(
        Request $request,
        School $school,
        GuardianEmailVerificationService $verificationService
    ): JsonResponse {
        abort_unless($school->is_enable && $school->inscriptions_enabled, 404);

        $validated = $request->validate([
            'tutor_num_doc' => ['required', 'string', 'max:50'],
            'tutor_email' => ['required', 'string', 'email:rfc', 'max:50'],
            'verification_code' => ['required', 'digits:6'],
        ]);

        return response()->json($verificationService->confirmCode(
            $school,
            $validated['tutor_num_doc'],
            $validated['tutor_email'],
            $validated['verification_code']
        ));
    }

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

    public function store(
        InscriptionRegisterRequest $request,
        GuardianEmailVerificationService $verificationService
    )
    {
        $response = [];
        $code = 200;
        try {

            DB::beginTransaction();

            InscriptionsPipeline::execute($request->validated());

            DB::commit();

            $verificationService->consume($request->input('guardian_email_verification_token'));

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
