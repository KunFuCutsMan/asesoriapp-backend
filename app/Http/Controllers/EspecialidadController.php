<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\EstudianteEspecialidad;
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
        $estudiante->withRelationshipAutoloading();

        /** @var Especialidad */
        $especialidad = Especialidad::find($especialidadID);

        if ($especialidad->carreraID != $estudiante->carreraID) {
            abort(400); // Solo se puede asignar una especialidad de su carrera
        }

        $espEstudiante = new EstudianteEspecialidad([
            'especialidadID' => $especialidad->id,
            'estudianteID' => $estudiante->id,
        ]);

        $estudiante->especialidadEstudiante()->save($espEstudiante);
        $estudiante->push();

        return response()->json(
            $estudiante->with('especialidad')->find($estudiante->id)
        );
    }
}
