<?php

namespace App\Models;

use App\Traits\Fields;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Master extends Model
{
    use Fields;
    use HasFactory;
    
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

    public static function saveAutoComplete($request)
    {
        $keys = ['school', 'place_birth', 'neighborhood', 'eps', 'place', 'rival_name', 'zone', 'commune', 'degree'];
        $fields = [
            'school' => 'colegio_escuela', 'place_birth' => 'lugar_nacimiento', 
            'neighborhood' => 'barrio', 'eps' => 'eps', 'place' => 'lugar', 
            'rival_name' => 'nombre_rival', 'zone' => 'zona', 
            'commune' => 'comuna', 'degree' => 'grado'
        ];
        for ($i = 0; $i < count($keys); ++$i) {
            if (array_key_exists($keys[$i], $request->all())) {

                $key = $keys[$i];
                $fieldRequest = Str::title($request[$key]);
                $master = static::select('autocomplete')
                    ->firstWhere('field', $fields[$key]);

                $value = array_unique(
                    array_merge(
                        explode(',', $master->autocomplete),
                        explode(',', $fieldRequest)
                    )
                );
                $master->update(['autocomplete' => $value]);
            }
        }
    }

    public function getAutoCompleteExplodeAttribute()
    {
        return $this->attributes['autocomplete'] ? explode(',', $this->attributes['autocomplete']) : '';
    }

    public function setAutoCompleteAttribute($value)
    {
        $this->attributes['autocomplete'] = implode(',', array_values(array_diff($value, array(''))));
    }
}
