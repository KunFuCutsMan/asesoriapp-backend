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
    public function index()
    {
        return Especialidad::all()->toResourceCollection();
    }

    public function byCarrera(string $carrera)
    {
        return Especialidad::where('carreraID', $carrera)->get()->toResourceCollection();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) return response()->json(null, 404);
        return $especialidad->toResource();
    }

    public function asignaEspecialidad(Request $request)
    {
        $request->validate([
            'especialidadID' => 'required|numeric|integer|exists:especialidades,id',
        ]);

        $especialidadID = $request->input('especialidadID');

        $estudiante = $request->user();
        if ($estudiante == null) return response()->json(null, 404);

        /** @var Especialidad */
        $especialidad = Especialidad::where('carreraID', $estudiante->carreraID)
            ->where('id', $especialidadID)
            ->first();

        if ($especialidad == null) return response()->json(null, 404);

        $especialidad->estudiantes()->save($estudiante);
        $especialidad->push();

        return $estudiante->toResource();
    }
}
