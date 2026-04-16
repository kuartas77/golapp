<?php

namespace App\Http\Controllers\Portal;

use App\Models\School;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SchoolsController extends Controller
{
    public function index()
    {
        return view('portal.schools.index');
    }

    public function show($slug)
    {
        $school = School::query()->where('is_enable', true)->firstWhere('slug', $slug);
        if(!$school) {
            return response('La escuela está deshabilitada', 404);
        }
        return view('portal.schools.show', compact('school'));
    }

    public function indexData(): JsonResponse
    {
        $schools = School::query()
            ->where('id', '<>', 1)
            ->where('is_enable', true)
            ->orderBy('name')
            ->get()
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'address' => $school->address,
                'phone' => $school->phone,
                'email_info' => $school->email_info,
                'logo_file' => $school->logo_file,
                'url' => url('portal.school.show', [$school->slug]),
            ])
            ->values();

        return $this->responseJson([
            'schools' => $schools,
        ]);
    }

    public function showData(School $school): JsonResponse
    {
        abort_unless($school->is_enable, 404, 'La escuela está deshabilitada');

        return $this->responseJson([
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'address' => $school->address,
                'phone' => $school->phone,
                'email_info' => $school->email_info,
                'logo_file' => $school->logo_file,
                'tutor_platform' => $school->tutor_platform,
                'create_contract' => $school->create_contract,
                'send_documents' => $school->send_documents,
                'sign_player' => $school->sign_player,
                'inscriptions_enabled' => $school->inscriptions_enabled,
            ],
            'year' => $this->getPortalYear(),
            'fileSizeMb' => 3,
            'storageKey' => "portal-inscription-form-{$school->slug}",
            'links' => [
                'schoolIndex' => '',//route('portal.school.index.data'),
                'guardianLogin' => url('/portal/acudientes/login'),
                'schoolLogin' => url('/login'),
            ],
            'endpoints' => [
                'store' => route('portal.school.inscription.store', [$school->slug]),
                'autocomplete' => route('portal.autocomplete.fields'),
                'searchDoc' => route('portal.autocomplete.search_doc'),
            ],
            'assets' => [
                'defaultUserPhoto' => asset('img/user.png'),
            ],
            'contracts' => [
                'affiliate' => asset('contracts/' . $school->slug . '/CAFICODEPOR.pdf'),
                'inscription' => asset('contracts/' . $school->slug . '/COINSCRIP.pdf'),
            ],
            'options' => [
                'genders' => Cache::remember('KEY_GENDERS', now()->addYear(), fn() => config('variables.KEY_GENDERS')),
                'relationships' => Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), fn() => config('variables.KEY_RELATIONSHIPS_SELECT')),
                'bloodTypes' => Cache::remember('KEY_BLOOD_TYPES', now()->addYear(), fn() => config('variables.KEY_BLOOD_TYPES')),
                'documentTypes' => Cache::remember('KEY_DOCUMENT_TYPES', now()->addYear(), fn() => config('variables.KEY_DOCUMENT_TYPES')),
                'jornada' => Cache::remember('KEY_JORNADA_TYPES', now()->addYear(), fn() => config('variables.KEY_JORNADA')),
            ],
            'recaptcha' => [
                'enabled' => !app()->environment('local') && filled(config('recaptchav3.sitekey')),
                'action' => 'inscriptions',
            ],
        ]);
    }

    private function getPortalYear(): string
    {
        return in_array(now()->month, [11, 12], true)
            ? now()->copy()->addYear()->format('Y')
            : now()->format('Y');
    }
}
