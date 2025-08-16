<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsesoriaResource;
use App\Http\Resources\CodigoAsesoriaResource;
use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'estudianteID' => 'sometimes|numeric|integer|exists:estudiante,id',
            'asesorID' => 'sometimes|numeric|integer|exists:asesor,id',
        ]);

        if ($request->has('estudianteID')) {
            $asesorias = Asesoria::where('estudianteID', $request->input('estudianteID'))->get();
            return AsesoriaResource::collection($asesorias)->response($request)->setStatusCode(200);
        } elseif ($request->has('asesorID')) {
            $asesorias = Asesoria::where('asesorID', $request->input('asesorID'))->get();
            return AsesoriaResource::collection($asesorias)->response($request)->setStatusCode(200);
        } else {
            $estudiante = $request->user();
            $asesorias = Asesoria::where('estudianteID', $estudiante->id)->get();
            return AsesoriaResource::collection($asesorias)->response($request)->setStatusCode(200);
        }

        return abort(404, 'No se encontraron asesorías.');
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
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'codigoSeguridad' => $this->codigoSeguridadAleatorio(),
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
        if ($request->has('asesorID') && $request->user()->isAdmin()) {
            return $this->asignaAsesor($request, $id);
        }

        if ($request->has('estadoAsesoriaID') && $request->user()->isAsesor()) {
            return $this->cambiaEstadoAsesoria($request, $id);
        }

        return abort(400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /** @var Asesoria */
        $asesoria = Asesoria::find($id);
        if (!$asesoria) abort(404);

        $estudiante = request()->user();

        if ($asesoria->estadoAsesoria->id == AsesoriaEstado::REALIZADA) {
            return abort(403, 'No se puede cancelar una asesoría que ya ha sido realizada.');
        }

        if ($estudiante->isAdmin()) {
            $asesoria->estadoAsesoria()->associate(AsesoriaEstado::find(AsesoriaEstado::CANCELADA));
            $asesoria->push();
            return $asesoria->toResource()->response()->setStatusCode(200);
        }

        if ($estudiante->isAsesor() && $asesoria?->asesorID == $estudiante?->asesor->id) {
            $asesoria->estadoAsesoria()->associate(AsesoriaEstado::find(AsesoriaEstado::CANCELADA));
            $asesoria->push();
            return $asesoria->toResource()->response()->setStatusCode(200);
        }

        if ($asesoria->estudianteID === $estudiante->id) {
            $asesoria->estadoAsesoria()->associate(AsesoriaEstado::find(AsesoriaEstado::CANCELADA));
            $asesoria->push();
            return $asesoria->toResource()->response()->setStatusCode(200);
        }

        return response()->json([], 403);
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

        /** @var Asesoria */
        $asesoria = Asesoria::find($asesoriaID);
        /** @var Asesor */
        $asesor = $request->user()->asesor;

        if ($asesor->id !== $asesoria->asesorID) {
            return abort(403, 'No tienes permiso para cambiar el estado de esta asesoría.');
        }

        if ($asesoria->estadoAsesoria->id === AsesoriaEstado::CANCELADA || $asesoria->estadoAsesoria->id == AsesoriaEstado::REALIZADA) {
            return abort(400, 'No se puede cambiar el estado de una asesoría cancelada o terminada.');
        }

        $horaInicialPasada = horaEsMenorIgualQue($asesoria->horaInicial, now()->format('H:i'));
        $horaFinalPasada = horaEsMenorIgualQue($asesoria->horaFinal, now()->format('H:i'));

        if (!$horaInicialPasada && !$horaFinalPasada) {
            return abort(400, 'No se puede cambiar el estado de la asesoría antes de su hora inicial o después de su hora final.');
        }

        /** @var int */
        $nuevoEstado = $request->input('estadoAsesoriaID');

        // Si se la asesoria va a estar en progreso, revisa si el tiempo de inicio es anterior a la actual
        if ($nuevoEstado == AsesoriaEstado::EN_PROGRESO && $horaInicialPasada) {
            $estadoProgreso = AsesoriaEstado::find(AsesoriaEstado::EN_PROGRESO);
            $asesoria->estadoAsesoria()->associate($estadoProgreso);
        } else if ($nuevoEstado == AsesoriaEstado::REALIZADA && $horaInicialPasada && $horaFinalPasada) {
            // Si se va a terminar la asesoria, revisa si el tiempo final es posterior al actual
            $estadoRealizado = AsesoriaEstado::find(AsesoriaEstado::REALIZADA);
            $asesoria->estadoAsesoria()->associate($estadoRealizado);
        }

        $asesoria->push();
        return $asesoria->toResource()->response($request)->setStatusCode(200);
    }

    public function obtenCodigoSeguridad(Request $request, int $asesoriaID): JsonResponse
    {
        /** @var Asesoria */
        $asesoria = Asesoria::find($asesoriaID);
        if (!$asesoria) abort(404);

        $estudiante = $request->user();
        if ($asesoria->asesorID !== $estudiante->asesor?->id) {
            return abort(403, 'No tienes permiso para ver el código de seguridad de esta asesoría.');
        }

        return $asesoria->toResource(CodigoAsesoriaResource::class)->response($request)->setStatusCode(200);
    }

    public function terminaAsesoria(Request $request, int $asesoriaID): JsonResponse
    {
        /** @var Asesoria */
        $asesoria = Asesoria::find($asesoriaID);
        if (!$asesoria) abort(404);

        $estudiante = $request->user();
        if ($asesoria->estudianteID !== $estudiante->id) {
            return abort(403, 'No tienes permiso para terminar esta asesoría.');
        }

        if ($asesoria->estadoAsesoriaID !== AsesoriaEstado::EN_PROGRESO) {
            return abort(403, 'La asesoría no está en progreso.');
        }

        if (!horaEsMayorIgualQue(now()->format('H:i'), $asesoria->horaFinal)) {
            return abort(403, 'No puedes terminar una asesoría antes de su hora final.');
        }

        $request->validate([
            'codigo' => 'required|string|size:6',
        ]);
        $codigoSeguridad = $request->input('codigo');
        if ($codigoSeguridad !== $asesoria->codigoSeguridad) {
            return abort(400, 'El código de seguridad proporcionado es incorrecto.');
        }

        $estadoRealizado = AsesoriaEstado::find(AsesoriaEstado::REALIZADA);
        $asesoria->estadoAsesoria()->associate($estadoRealizado);
        $asesoria->push();

        return $asesoria->toResource()->response($request)->setStatusCode(200);
    }

    private function codigoSeguridadAleatorio(): string
    {
        return str_pad(random_int(0, 999999), 6, '0');
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
