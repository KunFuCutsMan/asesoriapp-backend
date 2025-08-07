<?php

namespace App\Http\Controllers;

use App\Http\Resources\HorarioResource;
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
    public function index(Request $request)
    {
        $estudiante = $request->user();
        if (!$estudiante->isAsesor()) abort(404);
        return HorarioResource::collection($estudiante->asesor->horarios);
    }

    /**
     * Display the specified resource.
     */
    public function show(Horario $horario)
    {
        return $horario->toResource();
    }

    public function upsertHorarios(Request $request)
    {
        $request->validate([
            'horas' => 'required',
            'horas.*.hora' => ['required', Rule::date()->format('H:i'), new OnlyHasHours()],
            'horas.*.disponible' => 'required|boolean',
            'horas.*.diaSemanaID' => 'required|numeric|integer|exists:dias-semana,id',
        ]);

        $estudiante = $request->user();
        if (!$estudiante->isAsesor()) {
            abort(403);
        }
        $asesor = $estudiante->asesor;

        /** @var Collection */
        $horas = $request->input('horas');
        $horas->each(function (array $valor) use ($asesor) {
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
