<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Service\StopWatch;
use App\Models\School;
use App\Models\Payment;

if (!function_exists('getPay')) {
    /**
     * @param $value
     */
    function getPay($value): string
    {
        $payments = config('variables.KEY_PAYMENTS_SELECT');
        return array_key_exists($value, $payments) ? $payments[$value] : number_format((float)$value);
    }
}

if (!function_exists('getAmount')) {
    /**
     * @param $value
     */
    function getAmount($value): string
    {
        return (string) $value; //number_format((float)$value, 0, ',', '.');
    }
}

if (!function_exists('getEloquentSqlWithBindings')) {
    /**
     * get query with binding
     *
     * @param Builder $query
     */
    function getEloquentSqlWithBindings($query): string
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            $binding = addslashes($binding);

            return is_numeric($binding) ? $binding : sprintf("'%s'", $binding);
        })->toArray());
    }
}

if (!function_exists('checkAssists')) {
    function checkAssists($value): string
    {
        $assist = config('variables.KEY_ASSIST_LETTER');
        $key = array_search($value, array_keys($assist), true);
        return is_numeric($key) ? $assist[$value] : '-';
    }
}

if (!function_exists('getMonthNumber')) {
    function getMonthNumber($value)
    {
        if(!is_numeric($value)){
            $months = config('variables.KEY_MONTHS_INDEX');
            foreach ($months as $key => $month) {
                if($month === $value){
                    $value = $key;
                    break;
                }
            }
        }

        return $value;
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
    function getMonths(int $months = 12): Collection
    {
        $response = collect();
        for ($i = 1; $i <= $months; ++$i) {
            $response->put(strtolower(getMonth($i)), 1);
        }

        return $response;
    }
}

if (!function_exists('classDaysMonth')) {
    function classDaysMonth(int $year, int $month, array $classDays): Collection
    {
        $date = Carbon::createFromDate($year, $month);

        $carbonPeriod = CarbonPeriod::create($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
        $dayList = collect();
        $count = 1;
        foreach ($carbonPeriod as $date) {
            if (in_array($date->isoWeekday(), $classDays)) {
                $dayList->push(arrayDay($date, $count));
                ++$count;
            }
        }

        return $dayList;
    }
}

if (!function_exists('arrayDay')) {
    function arrayDay(Carbon $date, $count): array
    {
        return [
            'day' => $date->day,
            'date' => $date->format('Y-m-d'),
            'name' => $date->getTranslatedDayName(),
            'column' => numbersToLetters($count),
            'number_class' => $count
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
        $numberFormatter = NumberFormatter::create("en_CA", NumberFormatter::SPELLOUT);
        $numberFormat = str_replace('-', '_', $numberFormatter->format(intval($number)));


        return $assist ? 'assistance_' . $numberFormat : 'year_' . $numberFormat;
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
        return "SUB-" . abs((int)$value - now()->year);
    }
}

if (!function_exists('dayToNumber')) {
    function dayToNumber(string $day): int
    {
        $result = array_search(Str::title($day), config('variables.KEY_WEEKS_INDEX'), true);
        return $result ?: 0;
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

if (!function_exists('getSchool')) {
    function getSchool($user = null): School
    {
        $user = isset($user) ? $user : auth()->user();
        $prefixKey = isAdmin() ? 'admin.' : (isSchool() ? 'school.': '');

        $school_id = Session::get($prefixKey . 'selected_school', 1);

        $key = School::KEY_SCHOOL_CACHE . sprintf('_%s_%s', $prefixKey, $school_id);
        $ttl = now()->addMinutes(env('SESSION_LIFETIME', 120));
        $builder = School::with(['settingsValues']);

        if ((isAdmin() || isSchool()) && Cache::has($key)) {
            $data = Cache::get($key);
        } elseif (isAdmin() && !Cache::has($key)) {
            $data = Cache::remember(School::KEY_SCHOOL_CACHE . "_admin_1", $ttl, fn() => $builder->first());
        } elseif (isSchool() && !Cache::has($key)) {
            $school_id = $user->school_id;
            $data = Cache::remember(School::KEY_SCHOOL_CACHE . ('_' . $school_id), $ttl, fn() => $builder->firstWhere('id', $school_id));
        } else {
            $school_id = $user->school_id;
            $data = Cache::remember(School::KEY_SCHOOL_CACHE . ('_' . $school_id), $ttl, fn() => $builder->firstWhere('id', $school_id));
        }

        return $data;
    }
}

if (!function_exists('cleanString')) {
    function cleanString($text): string
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // non breaking space (equiv. to 0x160)
        );
        return strtolower(preg_replace(array_keys($utf8), array_values($utf8), $text));
    }
}

if (!function_exists('randomPassword')) {
    function randomPassword(int $length = 10): string
    {
        $alphabet = '#abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890*.';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; ++$i) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode('', $pass); //turn the array into a string
    }
}

if (!function_exists('checkValuePayment')) {
    function checkValuePayment(Payment $payment, string $column, int $defaultValue, int $alternative = 0)
    {
        $attribute = $column . '_amount';
        $value = 0;
        if (in_array($payment->$column, ['1', '9', '10'])) {
            $value = $payment->$attribute == 0 ? $defaultValue : $payment->$attribute;
        } elseif (in_array($payment->$column, ['11', '12', '13'])) {
            $value = $payment->$attribute == 0 ? $alternative : $payment->$attribute;
        }

        return $value;
    }
}

if (!function_exists('checkValueEnrollment')) {
    function checkValueEnrollment(Payment $payment, string $column, int $defaultValue)
    {
        $attribute = $column . '_amount';
        return $payment->$attribute == 0 && in_array($payment->$column, ['1', '9', '10']) ? $defaultValue : $payment->$attribute;
    }
}

if (!function_exists('checkEmail')){
    function checkEmail($email): bool
    {
        return isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('getIpToLog')){
    function getIpToLog(): ?string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER)){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }

        return request()->ip(); // it will return the server IP if the client IP is not found using this method.
    }
}

if (!function_exists('loggerTimeRequest')){
    function loggerTimeRequest(StopWatch $stopWatch): void
    {
        if (env('APP_ENV') == 'local') {
            logger()->info(
                "req",
                [
                    'elapsed'=> $stopWatch->getTimeElapsed(),
                    'url' => request()->fullUrl(),
                    'ip_address' => getIpToLog(),
                    'user_id' => (auth()->user() ? auth()->id() : 0),
                ]
            );
        }
    }
}

if (!function_exists('createUniqueCode')){
    function createUniqueCode(string $school_id, ?string $year): mixed
    {
        $campusIds = [];
        $newUniqueCode = '';
        $keyCache = "KEY_LAST_UNIQUE_CODE_".$school_id;
        $year = isset($year) ? $year: now()->year;

        $school = School::with(['settingsValues'])->find($school_id);
        if ($multiple = $school->settings->get('MULTIPLE_SCHOOLS')){
            $campusIds = json_decode($multiple);
        }

        $lastUniqueCode = Cache::remember($keyCache, now()->addMinute(), function() use($year, $school_id, $campusIds){
            $result = DB::table('players')->select(['unique_code'])
                ->where('unique_code', 'like', $year . '%')
                ->when(!empty($campusIds),
                    fn($q) => $q->whereIn('school_id', $campusIds),
                    fn($q) => $q->where('school_id', $school_id)
                )
                ->orderBy('unique_code', 'desc')
                ->limit(1)
                ->first();
            return isset($result) ? $result->unique_code : null;
        });

        if(isset($lastUniqueCode)){
            $newUniqueCode = intval($lastUniqueCode) + 1;
        }else{
            $count = 1;
            $newUniqueCode = $year . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
        }

        $newUniqueCode = generateCode($school_id, $newUniqueCode, $campusIds);

        Cache::put($keyCache, $newUniqueCode, now()->addMinute());

        return $newUniqueCode;
    }
}

if(!function_exists('generateCode')) {
    function generateCode($school_id, $lastUniqueCode, $campusIds = [])
    {
        $next = true;
        while ($next){
            $exits = DB::table('players')->select(['unique_code'])
                    ->where('unique_code', $lastUniqueCode)
                    ->when(!empty($campusIds),
                        fn($q) => $q->whereIn('school_id', $campusIds),
                        fn($q) => $q->where('school_id', $school_id)
                    )
                    ->exists();
            if(!$exits){
                $next = false;
            }else{
                $lastUniqueCode = intval($lastUniqueCode) + 1;
            }
        }

        return $lastUniqueCode;
    }
}

if(!function_exists('getYearInscription')) {
    function getYearInscription() {
        $year = in_array(now()->month, [11, 12]) ? now()->addYear()->year : now()->year;

        return $year;
    }
}

//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
//if (!function_exists('')){}
