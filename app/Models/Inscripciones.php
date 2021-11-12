<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Inscripciones.
 *
 * @version November 13, 2016, 11:33 pm COT
 */
class Inscripciones extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $table = 'inscripciones';
    public $fillable = [
        'sede_id',
        'grupo_id',
        'grupo_competencias_id',
        'codigo_unico',
        'categoria',
        'fecha_inicio',
        'nombres',
        'apellidos',
        'sexo',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'doc_identidad',
        'rh',
        'imagen',
        'posicion_campo',
        'perfil_dominante',
        'colegio_escuela',
        'grado',
        'direccion',
        'municipio',
        'barrio',
        'zona',
        'comuna',

        'telefonos',
        'correo',
        'movil',
        'eps',

        'fotos',
        'fotocopia_doc_ident',
        'certificado_sisben_eps',
        'certificado_medico',
        'certificado_estudio',
        'peto',
        'balon',
        'morral',
        'uniforme_presentacion',
        'uniforme_competencia',
        'pago_torneo',
        'creado',
        '1p',
        '2p',
        '3p',
        '4p',
        'padre',
        'madre',
        'acudiente',
        'familiar_uno',
        'familiar_dos',
    ];
    protected $dates = ['deleted_at'];

}
