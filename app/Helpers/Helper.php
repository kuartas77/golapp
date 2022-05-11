<?php

use Carbon\Carbon;
use App\Models\School;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getPay')) {
    /**
     * @param $value
     * @return string
     */
    function getPay($value): string
    {
        $payments = config('variables.KEY_PAYMENTS_SELECT');
        return array_key_exists($value, $payments)? $payments[$value] : number_format($value, 0,'','');
    }
}

if (!function_exists('getEloquentSqlWithBindings')) {
    /**
     * recibe un query sin ejecutar la consulta y forma el sql con los parametros
     *
     * @param [type] $query
     * @return String
     */
    function getEloquentSqlWithBindings($query): string
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            $binding = addslashes($binding);
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }
}

if (!function_exists('checkAssists')) {
    function checkAssists($value): string
    {
        $assist = config('variables.KEY_ASSIST_LETTER');
        return array_key_exists($value, $assist) ? $assist[$value] : '-';
    }
}

if (!function_exists('countAssists')) {
    function countAssists($assist, $dayClass): float
    {
        $i = 0;
        $assist->assistance_one !== 'as' ?: $i++;
        $assist->assistance_two !== 'as' ?: $i++;
        $assist->assistance_three !== 'as' ?: $i++;
        $assist->assistance_four !== 'as' ?: $i++;
        $assist->assistance_five !== 'as' ?: $i++;
        $assist->assistance_six !== 'as' ?: $i++;
        $assist->assistance_seven !== 'as' ?: $i++;
        $assist->assistance_eight !== 'as' ?: $i++;
        $assist->assistance_nine !== 'as' ?: $i++;
        $assist->assistance_ten !== 'as' ?: $i++;
        $assist->assistance_eleven !== 'as' ?: $i++;
        $assist->assistance_twelve !== 'as' ?: $i++;
        $assist->assistance_thirteen !== 'as' ?: $i++;
        $assist->assistance_fourteen !== 'as' ?: $i++;
        $assist->assistance_fifteen !== 'as' ?: $i++;
        return round(($i * 100) / $dayClass);
    }
}

if (!function_exists('getMonth')) {
    function getMonth($month): string
    {
        return config('variables.KEY_MONTHS_INDEX')[$month];
    }
}

if (!function_exists('getMonths')) {
    /**
     * @param int $months
     * @return Collection
     */
    function getMonths(int $months = 12): Collection
    {
        $response = collect();
        for ($i = 1; $i <= $months; $i++) {
            $response->put(strtolower(getMonth($i)), 1);
        }
        return $response;
    }
}

if (!function_exists('classDaysMonth')) {
    function classDaysMonth($year, int $month, array $classDays): Collection
    {
        $date = Carbon::createFromDate($year, $month);

        $periods = CarbonPeriod::create($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
        $dayList = collect();

        foreach ($periods as $date) {
            if(in_array($date->isoWeekday(), $classDays)){
                $dayList->push(arrayDay($date));
            }
        }
        return $dayList;
    }
}

if (!function_exists('arrayDay')) {
    function arrayDay(Carbon $date) : array{
        return ['day' => $date->day, 'date' => $date->format('Y-m-d'), 'name' => $date->getTranslatedDayName()];
    }
}

if (!function_exists('classDays')) {
    function classDays(int $year, int $month, array $days): Collection
    {
        return classDaysMonth($year, $month, $days);
    }
}

if (!function_exists('numbersToLetters')) {
    function numbersToLetters($number, $assist = true): string
    {
        $formatter = NumberFormatter::create("en_CA", NumberFormatter::SPELLOUT);
        $numberFormat = str_replace('-', '_', $formatter->format(intval($number)));
        return $assist ? "assistance_{$numberFormat}" : "year_{$numberFormat}";
    }
}

if (!function_exists('percent')) {
    function percent($number, $count): float
    {
        return round(($number * 100) / $count, 2);
    }
}

if (!function_exists('categoriesName')) {
    function categoriesName($value): string
    {
        return "SUB-". abs((int)$value - now()->year);
    }
}

if (!function_exists('dayToNumber')) {
    function dayToNumber(string $day): int
    {
        return array_search(Str::title($day), config('variables.KEY_WEEKS_INDEX'));
    }
}


if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        return auth()->user()->hasAnyRole(['super-admin']);
    }
}

if (!function_exists('isSchool')) {
    function isSchool(): bool
    {
        return auth()->user()->hasAnyRole(['school']);
    }
}

if (!function_exists('isInstructor')) {
    function isInstructor(): bool
    {
        return auth()->user()->hasAnyRole(['instructor']);
    }
}

if (!function_exists('getSchool')){
    function getSchool($user){
        return Cache::remember(School::KEY_SCHOOL_CACHE. "_{$user->school_id}", now()->addMinutes(env('SESSION_LIFETIME', 120)), fn()=> $user->school);
    }
}



//
//if (!function_exists('')){}
//
//if (!function_exists('')){}

