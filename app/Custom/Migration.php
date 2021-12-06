<?php


namespace App\Custom;


use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Game;
use App\Models\Payment;
use App\Models\People;
use App\Models\Player;
use App\Models\SkillsControl;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class Migration
{

    public static function InscriptionMigration()
    {

        foreach (DB::connection('mysql_old')->table('partidos')->cursor() as $partido) {
            try {
                DB::beginTransaction();
                Game::create([
                    'id' => $partido->id,
                    'tournament_id' => $partido->torneos_id,
                    'competition_group_id' => $partido->grupo_competencias_id,
                    'date' => Date::createFromFormat('d-m-Y', $partido->fecha)->format('Y-m-d'),
                    'hour' => $partido->hora,
                    'num_match' => $partido->num_partido,
                    'place' => $partido->lugar,
                    'rival_name' => $partido->nombre_rival,
                    'final_score' => $partido->resultado_final,
                    'general_concept' => $partido->concepto_general,
                    'created_at' => $partido->created_at,
                    'updated_at' => $partido->updated_at,
                    'deleted_at' => $partido->deleted_at,
                ]);
                DB::commit();
            } catch (\Exception $exception) {
                logger("match", [$exception]);
                DB::rollBack();
            }
        }


        $inscripciones = DB::connection('mysql_old')->table('inscripciones')->orderBy('id')->get();

        foreach ($inscripciones as $inscripcion) {
            try {
                DB::beginTransaction();
                $player = new Player();
                $player->id = $inscripcion->id;
                $player->unique_code = $inscripcion->recibo;
                $player->names = $inscripcion->nombres;
                $player->last_names = $inscripcion->apellidos;
                $player->gender = $inscripcion->sexo;
                $player->date_birth = $inscripcion->fecha_nacimiento;
                $player->place_birth = $inscripcion->lugar_nacimiento;
                $player->identification_document = $inscripcion->doc_identidad;
                $player->rh = $inscripcion->rh;
                $player->photo = $inscripcion->imagen;
                $player->position_field = $inscripcion->posicion_campo;
                $player->dominant_profile = $inscripcion->perfil_dominante;
                $player->school = $inscripcion->colegio_escuela;
                $player->degree = $inscripcion->grado;
                $player->address = $inscripcion->direccion;
                $player->municipality = $inscripcion->municipio;
                $player->neighborhood = $inscripcion->barrio;
                $player->zone = $inscripcion->zona;
                $player->commune = $inscripcion->comuna;

                $player->phones = $inscripcion->telefonos;
                $player->email = $inscripcion->correo;
                $player->mobile = $inscripcion->movil;
                $player->eps = $inscripcion->eps;
                $player->created_at = $inscripcion->created_at;
                $player->updated_at = $inscripcion->updated_at;
                $player->save();

                $peoplesIds = self::createPeoples($inscripcion);

                $player->people()->sync($peoplesIds);

                $pagos = DB::connection('mysql_old')->table('pagos')->where('inscripcion_id', '=', $inscripcion->id)->get();

                if ($pagos->count() == 0) {
                    logger("{$inscripcion->id} {$inscripcion->recibo} => pagos", [$pagos->count()]);
                }
                foreach ($pagos as $pago) {

                    $inscription = self::createInscription($player, $inscripcion, $pago);

                    self::createPayment($pago, $inscription);

                    $controls = DB::connection('mysql_old')->table('control_competencias')
                        ->where('inscripciones_id', '=', $inscripcion->id)
                        ->whereYear('created_at', "=", "{$pago->anio}-01-01")
                        ->get();
                    if (($countControls = $controls->count()) == 0) {
                        logger("{$pago->anio} {$inscripcion->id} {$inscripcion->recibo} => control_competencias", [$countControls]);
                    }

                    $asistencias = DB::connection('mysql_old')->table('asistencias')
                        ->where('inscripcion_id', '=', $player->id)
                        ->where('anio', $pago->anio)
                        ->get();
                    if (($countAssists = $asistencias->count()) == 0) {
                        logger("{$pago->anio} {$player->id} {$player->unique_code} => asistencias", [$countAssists]);
                    }

                    self::createAssists($asistencias, $inscription);

                    self::createSkillsControls($controls, $inscription);
                }

                DB::commit();
            } catch (\Exception $exception) {
                logger("insert {$inscripcion->id}", [$exception]);
                DB::rollback();
            }
        }


    }

    /**
     * @param $inscripcion
     * @return array
     */
    public static function createPeoples($inscripcion): array
    {
        $peoplesIds = [];
        $relationship = config('variables.KEY_RELATIONSHIPS_SELECT');

        if ($inscripcion->acudiente_nombres != null) {
            $people = People::updateOrCreate(
                [
                    'names' => $inscripcion->acudiente_nombres,
                    'identification_card' => $inscripcion->acudiente_cedula,
                ],
                [
                    'is_tutor' => 1,
                    'relationship' => 30,
                    'relationship_name' => $relationship[30],
                    'phone' => $inscripcion->acudiente_telefonos,
                    'mobile' => $inscripcion->acudiente_movil,
                    'neighborhood' => $inscripcion->acudiente_barrio,
                    'email' => $inscripcion->acudiente_correo,
                    'profession' => $inscripcion->acudiente_profesion,
                    'business' => $inscripcion->acudiente_empresa,
                    'position' => $inscripcion->acudiente_cargo,
                ]
            );
            array_push($peoplesIds, $people->id);
        }

        if ($inscripcion->padre_nombres != null && $inscripcion->padre_nombres != $inscripcion->acudiente_nombres) {

            $people = People::updateOrCreate(
                [
                    'names' => $inscripcion->padre_nombres,
                    'identification_card' => $inscripcion->padre_cedula,
                ],
                [
                    'relationship' => 20,
                    'relationship_name' => $relationship[20],
                    'phone' => $inscripcion->padre_telefonos,
                    'mobile' => $inscripcion->padre_movil,
                    'neighborhood' => null,
                    'email' => null,
                    'profession' => $inscripcion->padre_profesion,
                    'business' => $inscripcion->padre_empresa,
                    'position' => $inscripcion->padre_cargo,
                ]
            );
            array_push($peoplesIds, $people->id);
        }

        if ($inscripcion->madre_nombres != null && $inscripcion->madre_nombres != $inscripcion->acudiente_nombres) {
            $people = People::updateOrCreate(
                [
                    'names' => $inscripcion->madre_nombres,
                    'identification_card' => $inscripcion->madre_cedula,
                ],
                [
                    'relationship' => 15,
                    'relationship_name' => $relationship[15],
                    'phone' => $inscripcion->madre_telefonos,
                    'mobile' => $inscripcion->madre_movil,
                    'neighborhood' => null,
                    'email' => null,
                    'profession' => $inscripcion->madre_profesion,
                    'business' => $inscripcion->madre_empresa,
                    'position' => $inscripcion->madre_cargo,
                ]
            );
            array_push($peoplesIds, $people->id);
        }

        if ($inscripcion->nombres_familiar1 != null) {
            $key = array_search($inscripcion->parentesco_familiar1, $relationship);

            $people = People::updateOrCreate(
                [
                    'relationship' => $key,
                    'names' => $inscripcion->nombres_familiar1,
                ],
                [
                    'phone' => $inscripcion->telefono_familiar1,
                    'mobile' => $inscripcion->celular_familiar1,
                    'relationship_name' => $relationship[$key]
                ]
            );
            array_push($peoplesIds, $people->id);
        }

        if ($inscripcion->nombres_familiar2 != null) {
            $key = array_search($inscripcion->parentesco_familiar2, $relationship);
            $people = People::updateOrCreate(
                [
                    'relationship' => $key,
                    'names' => $inscripcion->nombres_familiar2,
                ],
                [
                    'phone' => $inscripcion->telefono_familiar2,
                    'mobile' => $inscripcion->celular_familiar2,
                    'relationship_name' => $relationship[$key]
                ]
            );
            array_push($peoplesIds, $people->id);
        }
        return $peoplesIds;
    }

    /**
     * @param $controls
     * @param Inscription $inscription
     */
    public static function createSkillsControls($controls, Inscription $inscription): void
    {
        foreach ($controls as $control) {

            switch (strtolower($control->tarjetas)) {
                case "r":
                    $roja = 1;
                    $amarilla = 0;
                    break;
                case "roja":
                    $roja = 1;
                    $amarilla = 0;
                    break;
                case "a":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "doble amonestacion":
                    $roja = 1;
                    $amarilla = 2;
                    break;
                case "amarillo":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "amarilla":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "0":
                    $roja = 0;
                    $amarilla = 0;
                    break;
                case "ama":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "aarilla":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "2 amarilla":
                    $roja = 1;
                    $amarilla = 2;
                    break;
                case "2":
                    $roja = 1;
                    $amarilla = 2;
                    break;
                case "1":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                default:
                    $roja = 0;
                    $amarilla = 0;
            }
            $skill_control = new SkillsControl();
            $skill_control->match_id = $control->partidos_id;
            $skill_control->inscription_id = $inscription->id;
            $skill_control->assistance = boolval($control->asistencia);
            $skill_control->titular = boolval($control->titular);
            $skill_control->played_approx = is_null($control->jugo_aprox) ? 0 : (integer)$control->jugo_aprox;
            $skill_control->position = strtoupper($control->posicion);
            $skill_control->goals = intval($control->goles);
            $skill_control->red_cards = ($roja + (integer)$control->tarjetas_rojas);
            $skill_control->yellow_cards = ($amarilla + (integer)$control->tarjetas_amarillas);
            $skill_control->qualification = intval($control->cal);
            $skill_control->observation = strtoupper($control->observacion);
            $skill_control->created_at = $control->created_at;
            $skill_control->updated_at = $control->updated_at;
            $skill_control->deleted_at = $control->deleted_at;
            $skill_control->save();

        }
    }

    /**
     * @param $asistencias
     * @param Inscription $inscription
     */
    public static function createAssists($asistencias, Inscription $inscription): void
    {
        foreach ($asistencias as $asistencia) {
            Assist::create([
                'training_group_id' => $asistencia->grupo_id,
                'inscription_id' => $inscription->id,
                'year' => intval($asistencia->anio),
                'month' => $asistencia->mes,
                'assistance_one' => $asistencia->asistencia_1,
                'assistance_two' => $asistencia->asistencia_2,
                'assistance_three' => $asistencia->asistencia_3,
                'assistance_four' => $asistencia->asistencia_4,
                'assistance_five' => $asistencia->asistencia_5,
                'assistance_six' => $asistencia->asistencia_6,
                'assistance_seven' => $asistencia->asistencia_7,
                'assistance_eight' => $asistencia->asistencia_8,
                'assistance_nine' => $asistencia->asistencia_9,
                'assistance_ten' => $asistencia->asistencia_10,
                'assistance_eleven' => $asistencia->asistencia_11,
                'assistance_twelve' => $asistencia->asistencia_12,
                'assistance_thirteen' => $asistencia->asistencia_13,
                'assistance_fourteen' => $asistencia->asistencia_14,
                'assistance_fifteen' => $asistencia->asistencia_15,
                'observations' => $asistencia->observaciones,
                'created_at' => $asistencia->created_at,
                'updated_at' => $asistencia->updated_at,
                'deleted_at' => $asistencia->deleted_at,
            ]);
        }
    }

    /**
     * @param $pago
     * @param Inscription $inscription
     */
    public static function createPayment($pago, Inscription $inscription): void
    {
        Payment::create([
            'year' => $pago->anio,
            'training_group_id' => $pago->grupo_id,
            'inscription_id' => $inscription->id,
            'unique_code' => $pago->doc_identidad,
            'january' => $pago->enero,
            'february' => $pago->febrero,
            'march' => $pago->marzo,
            'april' => $pago->abril,
            'may' => $pago->mayo,
            'june' => $pago->junio,
            'july' => $pago->julio,
            'august' => $pago->agosto,
            'september' => $pago->septiembre,
            'october' => $pago->octubre,
            'november' => $pago->noviembre,
            'december' => $pago->diciembre,
            'created_at' => $pago->created_at,
            'updated_at' => $pago->updated_at,
            'deleted_at' => $pago->deleted_at,
        ]);
    }

    /**
     * @param Player $player
     * @param $inscripcion
     * @param $pago
     * @return Inscription
     */
    public static function createInscription(Player $player, $inscripcion, $pago): Inscription
    {
        $fecha_inicio = Date::createFromFormat('d-m-Y', $inscripcion->fecha_inicio)->format('Y-m-d');
        $inscription = new Inscription();
        $inscription->player_id = $player->id;
        $inscription->training_group_id = $inscripcion->grupo_id;
        $inscription->competition_group_id = $inscripcion->grupo_competencias_id;
        $inscription->unique_code = $inscripcion->recibo;
        $inscription->category = $inscripcion->categoria;
        $inscription->start_date = $fecha_inicio;
        $inscription->photos = $inscripcion->fotos;
        $inscription->copy_identification_document = $inscripcion->fotocopia_doc_ident;
        $inscription->eps_certificate = $inscripcion->certificado_sisben_eps;
        $inscription->medic_certificate = $inscripcion->certificado_medico;
        $inscription->study_certificate = $inscripcion->certificado_estudio;
        $inscription->overalls = $inscripcion->peto;
        $inscription->ball = $inscripcion->balon;
        $inscription->bag = $inscripcion->morral;
        $inscription->presentation_uniform = $inscripcion->uniforme_presentacion;
        $inscription->competition_uniform = $inscripcion->uniforme_competencia;
        $inscription->tournament_pay = $inscripcion->pago_torneo;

        $p1 = '1p';
        $p2 = '2p';
        $p3 = '3p';
        $p4 = '4p';
        $inscription->period_one = $inscripcion->$p1;
        $inscription->period_two = $inscripcion->$p2;
        $inscription->period_three = $inscripcion->$p3;
        $inscription->period_four = $inscripcion->$p4;
        $inscription->year = $pago->anio;
        $inscription->created_at = $pago->created_at;
        $inscription->updated_at = $pago->updated_at;
        $inscription->deleted_at = $pago->deleted_at;

        Inscription::withoutEvents(function () use ($inscription) {
            $inscription->save();
        });
        return $inscription;
    }
}
