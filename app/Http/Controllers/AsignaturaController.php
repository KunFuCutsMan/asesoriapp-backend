<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Carrera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        if ($carreraID = $request->query('carreraID')) {

            if (!is_numeric($carreraID)) return response()->json(null, 400);

            $carrera = Carrera::find($carreraID);

            if (!$carrera) return response()->json(null, 404);

            $asignaturas = $carrera
                ->asignaturas()
                ->orderByPivot('semestre')
                ->get();

            return response()->json($asignaturas);
        } else {
            return response()->json(Asignatura::all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $carrera = Asignatura::find($id);

        return response()->json($carrera);
    }
}
