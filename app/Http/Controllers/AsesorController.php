<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsesorDataResource;
use App\Models\Asesor;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Horario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AsesorController extends Controller
{
    /**
     * 1. Tenga horas libres que coincidan con la asesoría
     * 2. Si la asignatura pedida es parte del repertorio de los asesores
     * 3. Si no hay nadie, entonces alguien que sea de la misma carrera
     * 4. Si no hay absolutamente nadie, entonces que se busquen todos los asesores
     * 
     */
    public function asesoresOfAsignatura(Request $request, int $asignaturaID): JsonResponse
    {
        /** @var Asignatura */
        $asignatura = Asignatura::find($asignaturaID);
        if (!$asignatura) {
            abort(404);
        }

        $request->validate([
            'diaSemanaID' => 'required|numeric|integer|exists:dias_semana,id',
            'horaInicio' => 'required|date_format:H:i',
            'horaFinal' => 'sometimes|date_format:H:i',
        ]);

        $diaSemana = $request->input('diaSemanaID');
        $horaInicio = Carbon::createFromFormat('H:i', $request->input('horaInicio'));
        $horaFinal = $request->input('horaFinal', "") != ""
            ? Carbon::createFromFormat('H:i', $request->input('horaFinal'))
            : null;

        $allAsesores = Asesor::with('estudiante')->get();

        // Obten los asesores que coincidan con el horario
        $asesoresPorHorario = $allAsesores->filter(function (Asesor $asesor) use ($horaInicio, $horaFinal, $diaSemana) {
            $horariosDeEseDia = collect($asesor->horarios->where('diaSemanaID', $diaSemana));

            if ($horariosDeEseDia->count() == 0)
                return false; // No esta libre ese dia

            $esIdeal = $horariosDeEseDia->contains(function (Horario $horario) use ($horaInicio, $horaFinal) {
                $hora = Carbon::createFromFormat('H:i', $horario->horaInicio);
                $horaCoincide = $horaInicio->diffInHours($hora) == 0;
                $pasadaDeHoraFinal = $horaFinal?->diffInHours($hora) == -1 ?? false;
                $esDisponible = boolval($horario->disponible);

                if ($horaFinal != null) // La hora final es exactamente la final que la anterior, y hay posibilidad que hay horas anteriores
                    return $pasadaDeHoraFinal && $horaInicio->diffInHours($hora) > 0;

                return $esDisponible && $horaCoincide;
            });

            return $esIdeal;
        });

        // Obten los asesores que coincidan con la asignatura
        $asesoresDeMateria = $allAsesores->filter(function (Asesor $asesor) use ($asignaturaID) {
            return collect($asesor->asignaturas)->contains(function (Asignatura $asignatura) use ($asignaturaID) {
                return $asignatura->id == $asignaturaID;
            });
        });

        $asesoresIdeales = $asesoresDeMateria->intersect($asesoresPorHorario);
        $asesoresPorAsignatura = $asesoresDeMateria->diff($asesoresIdeales);

        $asesoresNoIdeales = $allAsesores->diff($asesoresDeMateria)->diff($asesoresPorHorario);
        $asesoresDeCarrera = $asesoresNoIdeales->filter(function (Asesor $asesor) use ($asignatura) {
            return collect($asignatura->carreras)->contains(function (Carrera $carrera) use ($asesor) {
                return $asesor->estudiante->carreraID == $carrera->id;
            });
        });

        $otrosAsesores = $asesoresNoIdeales->diff($asesoresDeCarrera);

        return response()->json([
            'data' => [
                'ideales' => AsesorDataResource::collection($asesoresIdeales),
                'asignatura' => AsesorDataResource::collection($asesoresPorAsignatura),
                'carrera' => AsesorDataResource::collection($asesoresDeCarrera),
                'otros' => AsesorDataResource::collection($otrosAsesores),
            ]
        ]);
    }
}
