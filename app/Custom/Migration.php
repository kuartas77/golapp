<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */


namespace App\Custom;


use App\Models\Game;
use App\Models\Assist;
use App\Models\People;
use App\Models\Player;
use App\Models\Payment;
use Exception;
use Jenssegers\Date\Date;
use App\Models\Inscription;
use App\Models\SkillsControl;
use Illuminate\Support\Facades\DB;

class Migration
{

    public static function InscriptionMigration()
    {
        try {
            DB::beginTransaction();

            foreach (DB::connection('mysql_old')->table('partidos')->cursor() as $partido) {
                $game = new Game();
                $game->id = $partido->id;
                $game->tournament_id = $partido->torneos_id;
                $game->competition_group_id = $partido->grupo_competencias_id;
                $game->date = Date::createFromFormat('d-m-Y', $partido->fecha)->format('Y-m-d');
                $game->hour = $partido->hora;
                $game->num_match = $partido->num_partido;
                $game->place = $partido->lugar;
                $game->rival_name = $partido->nombre_rival;
                $game->final_score = $partido->resultado_final;
                $game->general_concept = $partido->concepto_general;
                $game->created_at = $partido->created_at;
                $game->updated_at = $partido->updated_at;
                $game->deleted_at = $partido->deleted_at;

                Game::withoutEvents(function () use ($game) {
                    $game->save();
                });
            }

            $inscripciones = DB::connection('mysql_old')->table('inscripciones')->orderBy('recibo')->get();

            foreach ($inscripciones as $inscripcion) {
                $date_birth = Date::parse($inscripcion->fecha_nacimiento);
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
                $player->category = categoriesName($date_birth->year);

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
                    /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                    /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                    logger("{$inscripcion->id} {$inscripcion->recibo} => pagos", [$pagos->count()]);
                }
                foreach ($pagos as $pago) {

                    $inscription = self::createInscription($player, $inscripcion, $pago);

                    self::createPayment($pago, $inscription);

                    /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                    $controls = DB::connection('mysql_old')->table('control_competencias')
                        ->where('inscripciones_id', '=', $inscripcion->id)
                        ->whereYear('created_at', "=", "{$pago->anio}-01-01")
                        ->get();
                    if (($countControls = $controls->count()) == 0) {
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        logger("{$pago->anio} {$inscripcion->id} {$inscripcion->recibo} => control_competencias", [$countControls]);
                    }

                    $asistencias = DB::connection('mysql_old')->table('asistencias')
                        ->where('inscripcion_id', '=', $player->id)
                        ->where('anio', $pago->anio)
                        ->get();
                    if (($countAssists = $asistencias->count()) == 0) {
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        logger("{$pago->anio} {$player->id} {$player->unique_code} => asistencias", [$countAssists]);
                    }

                    self::createAssists($asistencias, $inscription);

                    self::createSkillsControls($controls, $inscription);
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            logger()->error('InscriptionMigration', [
                "error" => $ex->getMessage(),
                "line" => $ex->getLine(),
                "file" => $ex->getFile(),
                "code" => $ex->getCode(),
            ]);
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
                case "roja":
                case "r":
                    $roja = 1;
                    $amarilla = 0;
                    break;
                case "1":
                case "amarillo":
                case "amarilla":
                case "ama":
                case "aarilla":
                case "a":
                    $roja = 0;
                    $amarilla = 1;
                    break;
                case "2 amarilla":
                case "2":
                case "doble amonestacion":
                    $roja = 1;
                    $amarilla = 2;
                    break;
                case "0":
                    $roja = 0;
                    $amarilla = 0;
                    break;
                default:
                    $roja = 0;
                    $amarilla = 0;
            }
            $skill_control = new SkillsControl();
            $skill_control->game_id = $control->partidos_id;
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

            SkillsControl::withoutEvents(function () use ($skill_control) {
                $skill_control->save();
            });
        }
    }

    /**
     * @param $asistencias
     * @param Inscription $inscription
     */
    public static function createAssists($asistencias, Inscription $inscription): void
    {
        foreach ($asistencias as $asistencia) {
            $assist = new Assist();
            $assist->training_group_id = $asistencia->grupo_id;
            $assist->inscription_id = $inscription->id;
            $assist->year = intval($asistencia->anio);
            $assist->month = $asistencia->mes;
            $assist->assistance_one = $asistencia->asistencia_1;
            $assist->assistance_two = $asistencia->asistencia_2;
            $assist->assistance_three = $asistencia->asistencia_3;
            $assist->assistance_four = $asistencia->asistencia_4;
            $assist->assistance_five = $asistencia->asistencia_5;
            $assist->assistance_six = $asistencia->asistencia_6;
            $assist->assistance_seven = $asistencia->asistencia_7;
            $assist->assistance_eight = $asistencia->asistencia_8;
            $assist->assistance_nine = $asistencia->asistencia_9;
            $assist->assistance_ten = $asistencia->asistencia_10;
            $assist->assistance_eleven = $asistencia->asistencia_11;
            $assist->assistance_twelve = $asistencia->asistencia_12;
            $assist->assistance_thirteen = $asistencia->asistencia_13;
            $assist->assistance_fourteen = $asistencia->asistencia_14;
            $assist->assistance_fifteen = $asistencia->asistencia_15;
            $assist->observations = $asistencia->observaciones;
            $assist->created_at = $asistencia->created_at;
            $assist->updated_at = $asistencia->updated_at;
            $assist->deleted_at = $asistencia->deleted_at;
            Assist::withoutEvents(function () use ($assist) {
                $assist->save();
            });
        }
    }

    /**
     * @param $pago
     * @param Inscription $inscription
     */
    public static function createPayment($pago, Inscription $inscription): void
    {
        $payment = new Payment();
        $payment->year = $pago->anio;
        $payment->training_group_id = $pago->grupo_id;
        $payment->inscription_id = $inscription->id;
        $payment->unique_code = $pago->doc_identidad;
        $payment->january = $pago->enero;
        $payment->february = $pago->febrero;
        $payment->march = $pago->marzo;
        $payment->april = $pago->abril;
        $payment->may = $pago->mayo;
        $payment->june = $pago->junio;
        $payment->july = $pago->julio;
        $payment->august = $pago->agosto;
        $payment->september = $pago->septiembre;
        $payment->october = $pago->octubre;
        $payment->november = $pago->noviembre;
        $payment->december = $pago->diciembre;
        $payment->created_at = $pago->created_at;
        $payment->updated_at = $pago->updated_at;
        $payment->deleted_at = $pago->deleted_at;

        Payment::withoutEvents(function () use ($payment) {
            $payment->save();
        });
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

        $inscription->period_one = $inscripcion->{'1p'};
        $inscription->period_two = $inscripcion->{'2p'};
        $inscription->period_three = $inscripcion->{'3p'};
        $inscription->period_four = $inscripcion->{'4p'};
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
