<?php

use App\Models\Payment;
use Carbon\Carbon;
use App\Models\School;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

if (!function_exists('getPay')) {
    /**
     * @param $value
     * @return string
     */
    function getPay($value): string
    {
        $payments = config('variables.KEY_PAYMENTS_SELECT');
        return array_key_exists($value, $payments)? $payments[$value] : number_format((float)$value, 0);
    }
}

if (!function_exists('getAmount')) {
    /**
     * @param $value
     * @return string
     */
    function getAmount($value): string
    {
        return $value; //number_format((float)$value, 0, ',', '.');
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
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
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
    function classDaysMonth(int $year, int $month, array $classDays): Collection
    {
        $date = Carbon::createFromDate($year, $month);

        $periods = CarbonPeriod::create($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
        $dayList = collect();
        $count = 1;
        foreach ($periods as $date) {
            if(in_array($date->isoWeekday(), $classDays)){
                $dayList->push(arrayDay($date, $count));
                $count++;
            }
        }
        return $dayList;
    }
}

if (!function_exists('arrayDay')) {
    function arrayDay(Carbon $date, $count) : array
    {
        return [
            'day' => $date->day,
            'date' => $date->format('Y-m-d'),
            'name' => $date->getTranslatedDayName(),
            'column' => numbersToLetters($count, true)
        ];
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
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return $assist ? "assistance_{$numberFormat}" : "year_{$numberFormat}";
    }
}

if (!function_exists('percent')) {
    function percent($number, $count): float
    {
        $divisor = $count ?: 1;
        return round(($number * 100) / $divisor, 2);
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
    function getSchool($user): School{
        $data = null;
        $school_id = Session::get('selected_school', 1);
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $key = School::KEY_SCHOOL_CACHE. "_admin_{$school_id}";
        $ttl = now()->addMinutes(env('SESSION_LIFETIME', 120));
        $query = School::with(['settingsValues']);

        if(isAdmin() && Cache::has($key)){
            $data = Cache::get($key);
        }elseif(isAdmin() && !Cache::has($key)){
            $data = Cache::remember(School::KEY_SCHOOL_CACHE. "_admin_1", $ttl, fn() => $query->first());
        }else{
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            $data = Cache::remember(School::KEY_SCHOOL_CACHE. "_{$user->school_id}", $ttl, fn()=> $query->firstWhere('id', $user->school_id));
        }
        return $data;
    }
}

if (!function_exists('cleanString')){
    function cleanString($text) {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );
        return strtolower(preg_replace(array_keys($utf8), array_values($utf8), $text));
    }
}

if (!function_exists('randomPassword')){
    function randomPassword(int $length = 10) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890*.';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}

if (!function_exists('checkValuePayment')){
    function checkValuePayment(Payment $payment, string $column, int $defaultValue, int $alternative = 0){
        $attribute = "{$column}_amount";
        $value = 0;
        if(in_array($payment->$column, ['1','9','10'])){
            $value = $payment->$attribute == 0 ? $defaultValue : $payment->$attribute;
        }elseif(in_array($payment->$column, ['11','12','13'])){
            $value = $payment->$attribute == 0 ? $alternative : $payment->$attribute;
        }
        return $value;
    }
}

if (!function_exists('checkValueEnrollment')){
    function checkValueEnrollment(Payment $payment, string $column, int $defaultValue){
        $attribute = "{$column}_amount";
        return $payment->$attribute == 0 && in_array($payment->$column, ['1','9','10']) ? $defaultValue : $payment->$attribute;
    }
}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
