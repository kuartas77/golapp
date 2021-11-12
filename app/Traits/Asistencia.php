<?php

namespace App\Traits;

trait Asistencia
{
    public function contarAsistencias($asistencia, $diasClases)
    {
        $i = 0;
        $asistencia->asistencia_1 == 'as' ? $i++ : $i;
        $asistencia->asistencia_2 == 'as' ? $i++ : $i;
        $asistencia->asistencia_3 == 'as' ? $i++ : $i;
        $asistencia->asistencia_4 == 'as' ? $i++ : $i;
        $asistencia->asistencia_5 == 'as' ? $i++ : $i;
        $asistencia->asistencia_6 == 'as' ? $i++ : $i;
        $asistencia->asistencia_7 == 'as' ? $i++ : $i;
        $asistencia->asistencia_8 == 'as' ? $i++ : $i;
        $asistencia->asistencia_9 == 'as' ? $i++ : $i;
        $asistencia->asistencia_10 == 'as' ? $i++ : $i;
        $asistencia->asistencia_11 == 'as' ? $i++ : $i;
        $asistencia->asistencia_12 == 'as' ? $i++ : $i;
        $asistencia->asistencia_13 == 'as' ? $i++ : $i;
        $asistencia->asistencia_14 == 'as' ? $i++ : $i;
        $asistencia->asistencia_15 == 'as' ? $i++ : $i;

        return round(($i * 100) / count($diasClases));
    }

    public function checkAsistencia($value)
    {
        $resp = '';
        switch ($value) {
            case '':
                $resp = '';
                break;
            case 'as':
                $resp = 'X';
                break;
            case 'fa':
                $resp = 'F';
                break;
            case 'ex':
                $resp = 'E';
                break;
            case 're':
                $resp = 'R';
                break;
            case 'in':
                $resp = 'I';
                break;
        }

        return $resp;
    }

    public function validarAsistencia($asistencia)
    {
        $inasistencias = 0;
        if ($asistencia->asistencia_1 == 'fa' && $asistencia->asistencia_2 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_2 == 'fa' && $asistencia->asistencia_3 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_3 == 'fa' && $asistencia->asistencia_4 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_4 == 'fa' && $asistencia->asistencia_5 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_5 == 'fa' && $asistencia->asistencia_6 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_6 == 'fa' && $asistencia->asistencia_7 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_7 == 'fa' && $asistencia->asistencia_8 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_8 == 'fa' && $asistencia->asistencia_9 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_9 == 'fa' && $asistencia->asistencia_10 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_11 == 'fa' && $asistencia->asistencia_12 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_13 == 'fa' && $asistencia->asistencia_14 == 'fa') {
            ++$inasistencias;
        }
        if ($asistencia->asistencia_15 == 'fa') {
            ++$inasistencias;
        }

        return $inasistencias;
    }

    public function checkAsistencias($asistencias, $diasClases)
    {
        $asistencias->each(function ($item) use ($diasClases) {
            $item->cantidadAsistencias = $this->contarAsistencias($item, $diasClases);
            $item->asistencia_1 = $this->checkAsistencia($item->asistencia_1);
            $item->asistencia_2 = $this->checkAsistencia($item->asistencia_2);
            $item->asistencia_3 = $this->checkAsistencia($item->asistencia_3);
            $item->asistencia_4 = $this->checkAsistencia($item->asistencia_4);
            $item->asistencia_5 = $this->checkAsistencia($item->asistencia_5);
            $item->asistencia_6 = $this->checkAsistencia($item->asistencia_6);
            $item->asistencia_7 = $this->checkAsistencia($item->asistencia_7);
            $item->asistencia_8 = $this->checkAsistencia($item->asistencia_8);
            $item->asistencia_9 = $this->checkAsistencia($item->asistencia_9);
            $item->asistencia_10 = $this->checkAsistencia($item->asistencia_10);
            $item->asistencia_11 = $this->checkAsistencia($item->asistencia_11);
            $item->asistencia_12 = $this->checkAsistencia($item->asistencia_12);
            $item->asistencia_13 = $this->checkAsistencia($item->asistencia_13);
            $item->asistencia_14 = $this->checkAsistencia($item->asistencia_14);
            $item->asistencia_15 = $this->checkAsistencia($item->asistencia_15);
        });

        return $asistencias;
    }

    public function checkAsistenciasMultiple($inscripcion, $dias)
    {
        $inscripcion->todo_asistencias->each(function ($item) use ($dias) {
            $diasClases = $this->diasClases(count($dias), $dias, $item->mes, $item->anio);

            $item->cantidadAsistencias = $this->contarAsistencias($item, $diasClases);
            $item->asistencia_1 = $this->checkAsistencia($item->asistencia_1);
            $item->asistencia_2 = $this->checkAsistencia($item->asistencia_2);
            $item->asistencia_3 = $this->checkAsistencia($item->asistencia_3);
            $item->asistencia_4 = $this->checkAsistencia($item->asistencia_4);
            $item->asistencia_5 = $this->checkAsistencia($item->asistencia_5);
            $item->asistencia_6 = $this->checkAsistencia($item->asistencia_6);
            $item->asistencia_7 = $this->checkAsistencia($item->asistencia_7);
            $item->asistencia_8 = $this->checkAsistencia($item->asistencia_8);
            $item->asistencia_9 = $this->checkAsistencia($item->asistencia_9);
            $item->asistencia_10 = $this->checkAsistencia($item->asistencia_10);
            $item->asistencia_11 = $this->checkAsistencia($item->asistencia_11);
            $item->asistencia_12 = $this->checkAsistencia($item->asistencia_12);
            $item->asistencia_13 = $this->checkAsistencia($item->asistencia_13);
            $item->asistencia_14 = $this->checkAsistencia($item->asistencia_14);
            $item->asistencia_15 = $this->checkAsistencia($item->asistencia_15);
        });

        return $inscripcion;
    }
}
