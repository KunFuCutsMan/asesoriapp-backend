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
        $asesoresPorHorario = $allAsesoresDeEseDia->filter(function (Asesor $asesor) use ($diaSemana, $horaInicio, $horaFinal) {
            return $horaFinal != null
                ? $this->asesorDisponibleAcordeHoraInicialYFinal($asesor, $diaSemana, $horaInicio, $horaFinal)
                : $this->asesorDisponibleAcordeHoraInicial($asesor, $diaSemana, $horaInicio);
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

    /**
     * Determina si un `asesor` se encuentra disponible, dados las `horaInicio` y `horaFinal` dadas
     * 
     * El asesor se filtra si:
     * - En el rango [$horaInicio, $horaFinal) todas las horas se encuentren marcadas como disponibles
     *
     * */
    private function asesorDisponibleAcordeHoraInicialYFinal(Asesor $asesor, int $diaSemanaID, Carbon $horaInicio, ?Carbon $horaFinal = null): bool
    {
        $horariosDelDia = collect($asesor->horarios)->where('diaSemanaID', $diaSemanaID)->sortBy('horaInicio');
        if ($horariosDelDia->isEmpty()) {
            return false; // Definitivamente no esta disponible ese dia
        }

        $horariosIncluyentesInicio = $horariosDelDia->where('horaInicio', '>=', $horaInicio)->where('disponible', 1);
        $horariosExcluyentesFinal = $horariosDelDia->where('horaFinal', '<', $horaFinal)->where('disponible', 1);
        $rangoDeHorarios = $horariosIncluyentesInicio->intersect($horariosExcluyentesFinal);

        // Por alguna razon, los asesores que se necesitan son aquellos cuyas
        // $horariosIncluyentesInicio y $horariosExcluyentesFinal no tienen elementos en comun
        // No se porque es asi pero bueno
        return $rangoDeHorarios->isEmpty();
    }

    /**
     * Determina si un `asesor` se encuentra disponible, dada la `horaInicio` dada
     * 
     * El asesor se filtra si:
     * - $horaInicio es la misma que horario->horaInicio
     * - horario->disponible es verdadero
     */
    private function asesorDisponibleAcordeHoraInicial(Asesor $asesor, int $diaSemanaID, Carbon $horaInicio): bool
    {
        return collect($asesor->horarios)
            ->where('diaSemanaID', $diaSemanaID)
            ->contains(function (Horario $horario) use ($horaInicio) {
                $hora = Carbon::createFromFormat('H:i', $horario->horaInicio);
                return boolval($horario->disponible) && $horaInicio->diffInHours($hora) == 0;
            });
    }
}
