<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\InscriptionRegisterRequest;
use App\Models\School;
use App\Modules\Inscriptions\Actions\Create\Pipeline as InscriptionsPipeline;
use App\Service\Portal\DataProcessingPolicyService;
use App\Service\Portal\GuardianEmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            $validated['verification_code'],
            (string) $request->ip()
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
            'client_timed_out' => ['nullable', 'boolean'],
            'total_file_bytes' => ['nullable', 'integer', 'min:0'],
            'file_sizes' => ['nullable', 'array'],
            'file_sizes.*' => ['integer', 'min:0'],
            'timeout_ms' => ['nullable', 'integer', 'min:0'],
            'elapsed_ms' => ['nullable', 'integer', 'min:0'],
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
        GuardianEmailVerificationService $verificationService,
        DataProcessingPolicyService $policyService
    ) {
        $response = [];
        $code = 200;
        $verificationConsumed = false;
        try {

            DB::beginTransaction();

            $data = $request->validated();
            $verificationService->consumeVerified(
                $data['school_data'],
                $data['tutor_doc'],
                $data['tutor_email'],
                $data['guardian_email_verification_token'] ?? null,
                (string) $request->ip()
            );
            $verificationConsumed = true;
            $policyEvidence = $policyService->evidenceFor($data['school_data']);
            $data['data_processing_policy_accepted_at'] = now();
            $data['data_processing_policy_version'] = $policyEvidence['version'];
            $data['data_processing_policy_hash'] = $policyEvidence['sha256'];

            if (filled($data['signatureTutor'] ?? null) || filled($data['signatureAlumno'] ?? null)) {
                $data['signature_ip_address'] = $request->ip();
                $data['signature_user_agent'] = mb_substr((string) $request->userAgent(), 0, 500);
            }

            InscriptionsPipeline::execute($data);

            DB::commit();

            $response = ['ok'];
        } catch (ValidationException $th) {
            DB::rollBack();
            Cache::forget('KEY_LAST_UNIQUE_CODE');
            $errors = $th->errors();

            if ($verificationConsumed) {
                $errors['guardian_email_verification_token'] = [
                    'La verificación del correo fue utilizada. Solicita un código nuevo antes de reenviar la inscripción.',
                ];
            }
            Log::warning('Portal inscription rejected by business validation', [
                'school_slug' => $request->route('school'),
                'error_fields' => array_keys($errors),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ]);
            $response = [
                'message' => $th->getMessage(),
                'errors' => $errors,
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
            $response = ['message' => 'No fue posible completar la inscripción. Inténtalo nuevamente o contacta a la escuela.'];
            $code = 500;
        }

        return response()->json($response, $code);
    }
}
