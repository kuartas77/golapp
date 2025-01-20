<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ErrorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class Master extends Model
{
    use HasFactory;
    use ErrorTrait;

    protected $table = "master";

    protected $fillable = [
        'field',
        'autocomplete'
    ];

    protected $appends = [
        'autocomplete_explode'
    ];

    public static function getAutocomplete($request): Collection
    {
        $response = collect();

        $masters = Master::whereIn('field', $request->input('fields', []))->get();
        foreach ($masters as $master) {
            $response->put($master->field, $master->autocomplete_explode);
        }

        return $response;
    }

    public static function saveAutoComplete(array $data): void
    {
        $keys = ['school', 'place_birth', 'neighborhood', 'eps', 'place', 'rival_name', 'zone', 'commune', 'degree'];

        try {
            DB::beginTransaction();
            $counter = count($keys);
            for ($i = 0; $i < $counter; ++$i) {
                $key = $keys[$i];
                if (array_key_exists($key, $data)) {

                    $fieldRequest = Str::upper(trim($data[$key]));

                    $master = static::firstOrCreate(
                        ['field' => $key],
                    );

                    $autocomplete = array_unique(
                        array_merge(
                            explode(',', $master->autocomplete),
                            explode(',', $fieldRequest)
                        )
                        , SORT_STRING);
                    $master->update(['autocomplete' => $autocomplete]);
                }
            }

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();
            (new Master())->logError(__METHOD__, $throwable);
        }
    }

    public function getAutoCompleteExplodeAttribute()
    {
        return $this->attributes['autocomplete'] ? explode(',', $this->attributes['autocomplete']) : '';
    }

    public function setAutoCompleteAttribute($value): void
    {
        $this->attributes['autocomplete'] = implode(',', array_values(array_diff($value, array(''))));
    }
}
