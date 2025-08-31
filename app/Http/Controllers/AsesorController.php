<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsesorDataResource;
use App\Models\Asesor;
use App\Models\Asignatura;
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
        $asignaturaAsesorada = Asignatura::findOrFail($asignaturaID);

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
        $allAsesoresDeEseDia = Asesor::with('estudiante')
            ->whereRelation('horarios', 'diaSemanaID', $diaSemana)
            ->get();

        // Obten los asesores que coincidan con el horario
        $asesoresPorHorario = $allAsesoresDeEseDia->filter(function (Asesor $asesor) use ($horaInicio, $horaFinal) {
            return $asesor->horarios->contains(function (Horario $horario) use ($horaInicio, $horaFinal) {
                $horaHorario = Carbon::createFromFormat('H:i', $horario->horaInicio);

                $horaCoincide = $horaInicio->diffInHours($horaHorario) == 0;
                $pasadaDeHoraInicial = $horaInicio->diffInHours($horaHorario) > 0;
                $pasadaDeHoraFinal = $horaFinal?->diffInHours($horaHorario) == -1 ?? false;
                $esDisponible = boolval($horario->disponible);

                return $horaFinal != null
                    ? $pasadaDeHoraInicial && $pasadaDeHoraFinal
                    : $esDisponible && $horaCoincide;
            });
        });

        // Obten los asesores que coincidan con la asignatura
        $asesoresDeMateria = $asignaturaAsesorada->asesores;

        // Los asesores ideales son aquellos donde coincida su horario, e impartan la asignatura
        $asesoresIdeales = $asesoresDeMateria->intersect($asesoresPorHorario);

        // Los asesores por asignatura son aquellos si pueden impartir la asignatura,
        // pero el horario en donde la pueden dar no es la misma
        $asesoresPorAsignatura = $asesoresDeMateria->diff($asesoresIdeales);

        $asesoresNoIdeales = $allAsesores
            ->diff($asesoresDeMateria)
            ->diff($asesoresPorHorario);

        // Los asesores de carrera son aquellos que no cumplen las condiciones anteriores,
        // Pero la carrera que cursan tiene dicha asignatura
        $asesoresDeCarrera = $asesoresNoIdeales->filter(function (Asesor $asesor) use ($asignaturaAsesorada) {
            return collect($asignaturaAsesorada->carreras)->contains('id', $asesor->estudiante->carreraID);
        });

        // Todos los asesores que no tienen nada que ver se consideran como otros asesores
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
