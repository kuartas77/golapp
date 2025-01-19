<?php

namespace App\Http\ViewComposers\Public;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\View;
use App\Models\School;

class PortalComposer
{
    public function compose(View $view): void
    {
        $schools = Cache::remember('SCHOOLS_ENABLED', now()->addMinutes(10), fn() => School::query()->where('id', '<>', 1)->where('is_enable', true)->orderBy('name')->pluck('name', 'slug'));

        $year = date('Y');
        if (in_array(now()->month, [11, 12])) {
            $year = now()->addYear()->format('Y');
        }

        $genders = Cache::remember('KEY_GENDERS', now()->addYear(), fn() => config('variables.KEY_GENDERS'));

        $relationships = Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), fn() => config('variables.KEY_RELATIONSHIPS_SELECT'));

        $blood_types = Cache::remember('KEY_BLOOD_TYPES', now()->addYear(), fn() => config('variables.KEY_BLOOD_TYPES'));

        $document_types = Cache::remember('KEY_DOCUMENT_TYPES', now()->addYear(), fn() => config('variables.KEY_DOCUMENT_TYPES'));

        $jornada = Cache::remember('KEY_JORNADA_TYPES', now()->addYear(), fn() => config('variables.KEY_JORNADA'));

        $view->with('year', $year);
        $view->with('genders', $genders);
        $view->with('jornada', $jornada);
        $view->with('public_schools', $schools);
        $view->with('blood_types', $blood_types);
        $view->with('relationships', $relationships);
        $view->with('document_types', $document_types);
    }

}
