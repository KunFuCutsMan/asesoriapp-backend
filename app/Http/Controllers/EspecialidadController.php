<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Especialidad::all());
    }

    public function byCarrera(string $carrera): JsonResponse
    {
        $especialidades = Especialidad::where('carreraID', $carrera)->get();
        return response()->json($especialidades);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) {
            abort(404);
        }

        return response()->json($especialidad);
    }

    public function asignaEspecialidad(Request $request): JsonResponse
    {
        $request->validate([
            'especialidadID' => 'required|numeric|integer|exists:especialidades,id',
        ]);

        $especialidadID = $request->input('especialidadID');

        $estudiante = $request->user();
        if ($estudiante == null) abort(404);

        /** @var Especialidad */
        $especialidad = Especialidad::where('carreraID', $estudiante->carreraID)
            ->where('id', $especialidadID)
            ->first();

        if ($especialidad == null) abort(400);

        $especialidad->estudiantes()->save($estudiante);
        $especialidad->push();

        return response()->json($estudiante->withRelationshipAutoloading()->with('especialidad')->first());
    }
}
