<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlCompetencias extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'control_competencias';

    public $fillable = [
        'sede_id',
        'partidos_id',
        'asistencia',
        'titular',
        'jugo_aprox',
        'posicion',
        'goles',
        'tarjetas',
        'tarjetas_amarillas',
        'tarjetas_rojas',
        'cal',
        'ma',
        'up',
        'observacion',
        'inscripciones_id',
    ];
}
