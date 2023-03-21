<?php

namespace App\Models;

use App\Traits\ErrorTrait;
use App\Traits\Fields;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Master extends Model
{
    use Fields;
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
        foreach ($masters as $key => $master) {
            $response->put($master->field, $master->autocomplete_explode);
        }
        return $response;
    }

    public static function saveAutoComplete(array $data)
    {
        $keys = ['school', 'place_birth', 'neighborhood', 'eps', 'place', 'rival_name', 'zone', 'commune', 'degree'];
        
        try {
            DB::beginTransaction();
            for ($i = 0; $i < count($keys); ++$i) {
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
        } catch (\Throwable $th) {
            DB::rollBack();
            (new Master())->logError(__METHOD__, $th);
        }   
    }

    // public static function saveAutoComplete($request)
    // {
    //     $keys = ['school', 'place_birth', 'neighborhood', 'eps', 'place', 'rival_name', 'zone', 'commune', 'degree'];
        
    //     try {
    //         DB::beginTransaction();
    //         for ($i = 0; $i < count($keys); ++$i) {
    //             $key = $keys[$i];
    //             if (array_key_exists($key, $request->all())) {

    //                 $fieldRequest = Str::upper(trim($request[$key]));

    //                 $master = static::firstOrCreate(
    //                     ['field' => $key],
    //                 );

    //                 $autocomplete = array_unique(
    //                     array_merge(
    //                         explode(',', $master->autocomplete),
    //                         explode(',', $fieldRequest)
    //                     )
    //                 , SORT_STRING);
    //                 $master->update(['autocomplete' => $autocomplete]);
    //             }
    //         }
    //         DB::commit();
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         (new Master())->logError(__METHOD__, $th);
    //     }   
    // }

    public function getAutoCompleteExplodeAttribute()
    {
        return $this->attributes['autocomplete'] ? explode(',', $this->attributes['autocomplete']) : '';
    }

    public function setAutoCompleteAttribute($value)
    {
        $this->attributes['autocomplete'] = implode(',', array_values(array_diff($value, array(''))));
    }
}
