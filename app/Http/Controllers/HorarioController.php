<?php

namespace App\Http\Controllers;

use App\Http\Resources\HorarioResource;
use App\Models\Asesor;
use App\Models\DiaSemana;
use App\Models\Horario;
use App\Rules\OnlyHasHours;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $asesorID)
    {
        $asesor = Asesor::findOrFail($asesorID);
        return HorarioResource::collection($asesor->horarios);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $asesorID, int $horario)
    {
        $estudiante = $request->user();
        if (! $estudiante->isAsesor()) {
            abort(403);
        }
        if ($estudiante->asesor->id !== $asesorID) {
            abort(403);
        }
        $horarioModel = $estudiante->asesor->horarios->find($horario);
        return $horarioModel->toResource() ?? abort(403);
    }

    public function upsertHorarios(Request $request, int $asesorID)
    {
        $request->validate([
            'horas' => 'required',
            'horas.*.hora' => ['required', Rule::date()->format('H:i'), new OnlyHasHours()],
            'horas.*.disponible' => 'required|boolean',
            'horas.*.diaSemanaID' => 'required|numeric|integer|exists:dias_semana,id',
        ]);

        $estudiante = $request->user();
        if (!$estudiante->isAsesor()) {
            abort(403);
        }
        $asesor = $estudiante->asesor;
        if ($asesor->id != $asesorID) {
            abort(403);
        }

        $horas = $request->input('horas');
        collect($horas)->each(function (array $valor) use ($asesor) {
            $diaSemana = DiaSemana::find($valor['diaSemanaID']);

            /** @var Horario|null */
            $horario = $asesor->horarios()
                ->where('horaInicio', '=', $valor['hora'])
                ->where('diaSemanaID', '=', $diaSemana->id)
                ->first();

            if ($horario != null) {
                $horario->disponible = $valor['disponible'];
                $horario->horaInicio = $valor['hora'];
                $horario->diaSemanaID = $diaSemana->id;

                $horario->push();
            } else {
                $newHorario = new Horario();
                $newHorario->disponible = $valor['disponible'];
                $newHorario->horaInicio = $valor['hora'];
                $newHorario->diaSemana()->associate($diaSemana);

                $asesor->horarios()->save($newHorario);
            }
        });

        return HorarioResource::collection($asesor->horarios);
    }
}
