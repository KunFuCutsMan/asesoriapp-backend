<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsesoriaResource;
use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Rules\IDExistsInTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estudiante = request()->user();
        $asesorias = Asesoria::where('estudianteID', $estudiante->id)
            ->get();

        return AsesoriaResource::collection($asesorias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'carreraID' => 'required|numeric|integer|exists:carrera,id',
            'asignaturaID' => 'required|numeric|integer|exists:asignatura,id',
            'diaAsesoria' => 'required|date',
            'horaInicial' => 'required|date_format:H:i',
            'horaFinal' => 'required|date_format:H:i|after:horaInicial'
        ]);

        $estudiante = $request->user();

        if ($estudiante->carrera->id !== $request->input('carreraID')) {
            return abort(403, 'El estudiante no pertenece a la carrera seleccionada.');
        }

        $carrera = $estudiante->carrera;
        $asignatura = $carrera->asignaturas()->find($request->input('asignaturaID'));

        if (!$asignatura) {
            return abort(404, 'La asignatura no pertenece a la carrera seleccionada.');
        }

        $asesoria = new Asesoria([
            'diaAsesoria' => $request->date('diaAsesoria'),
            'horaInicial' => $request->input('horaInicial'),
            'horaFinal' => $request->input('horaFinal'),
            'estudianteID' => $estudiante->id,
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE
        ]);

        $asesoria->carreraAsignatura()->associate($carrera);
        $asesoria->asignatura()->associate($asignatura);

        $asesoria->push();
        $asesoria->refresh();

        return $asesoria->toResource()->response($request)->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asesoria = Asesoria::find($id);
        return $asesoria->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if ($request->user()->isAdmin()) {

            if ($request->has('asesorID')) {
                return $this->asignaAsesor($request, $id);
            }
        } else if ($request->user()->isAsesor()) {

            if ($request->has('estadoAsesoriaID')) {
                return $this->cambiaEstadoAsesoria($request, $id);
            }
        }

        return abort(400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $estudiante = request()->user();

        $asesoria = Asesoria::where('id', $id)
            ->where('estudianteID', $estudiante->id)
            ->orWhere('asesorID', $estudiante->asesor->id)
            ->firstOrFail();

        if ($asesoria->estadoAsesoriaID !== AsesoriaEstado::PENDIENTE) {
            return abort(400, 'No se puede cancelar una asesoría que no está pendiente.');
        }

        $asesoria->estadoAsesoriaID = AsesoriaEstado::CANCELADA;
        $asesoria->save();

        return response()->json($asesoria);
    }

    private function asignaAsesor(Request $request, int $asesoriaID): JsonResponse
    {
        $request->validate([
            'asesorID' => 'required|numeric|integer|exists:asesor,id',
        ]);

        /** @var Asesoria */
        $asesoria = Asesoria::find($asesoriaID);

        if ($asesoria->estadoAsesoriaID !== AsesoriaEstado::PENDIENTE) {
            return abort(400);
        }

        $estado = AsesoriaEstado::find(AsesoriaEstado::EN_PROGRESO);
        $asesoria->estadoAsesoria()->associate($estado);

        $asesor = Asesor::find($request->input('asesorID'));
        $asesoria->asesor()->associate($asesor);
        $asesoria->push();

        return $asesoria->toResource()->response($request)->setStatusCode(200);
    }

    private function cambiaEstadoAsesoria(Request $request, int $asesoriaID): JsonResponse
    {
        $request->validate([
            'estadoAsesoriaID' => 'required|numeric|integer|exists:asesoria-estados,id',
        ]);

        $asesoria = Asesoria::findOrFail($asesoriaID);
        $asesor = $request->user()->asesor;

        if ($asesor->id !== $asesoria->asesorID) {
            return abort(403, 'No tienes permiso para cambiar el estado de esta asesoría.');
        }

        if ($asesoria->estadoAsesoria->id === AsesoriaEstado::CANCELADA || $asesoria->estadoAsesoria->id == AsesoriaEstado::REALIZADA) {
            return abort(400, 'No se puede cambiar el estado de una asesoría cancelada o terminada.');
        }

        $nuevoEstado = $request->input('estadoAsesoriaID');

        $horaInicialPasada = horaEsMenorIgualQue($asesoria->horaInicial, now()->format('H:i'));
        $horaFinalPasada = horaEsMenorIgualQue($asesoria->horaFinal, now()->format('H:i'));

        if (!$horaInicialPasada && !$horaFinalPasada) {
            return abort(400, 'No se puede cambiar el estado de la asesoría antes de su hora inicial o después de su hora final.');
        }

        // Si se la asesoria va a estar en progreso, revisa si el tiempo de inicio es anterior a la actual
        if ($nuevoEstado == AsesoriaEstado::EN_PROGRESO && $horaInicialPasada) {
            $asesoria->estadoAsesoriaID = AsesoriaEstado::EN_PROGRESO;
        } else if ($nuevoEstado == AsesoriaEstado::REALIZADA && $horaInicialPasada && $horaFinalPasada) {
            // Si se va a terminar la asesoria, revisa si el tiempo final es posterior al actual
            $asesoria->estadoAsesoriaID = AsesoriaEstado::REALIZADA;
        }

        $asesoria->save();
        return response()->json($asesoria);
    }
}

function horaEsMayorIgualQue(string $hora, string $comparacion): bool
{
    return strtotime($hora) >= strtotime($comparacion);
}

function horaEsMenorIgualQue(string $hora, string $comparacion): bool
{
    return strtotime($hora) <= strtotime($comparacion);
}
