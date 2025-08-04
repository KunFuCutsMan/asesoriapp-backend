<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\JsonResponse;

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
}
