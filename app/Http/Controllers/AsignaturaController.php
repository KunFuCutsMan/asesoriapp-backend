<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Carrera;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($carreraID = $request->query('carreraID')) {
            $request->validate([
                'carreraID' => 'numeric|integer|exists:carrera,id'
            ]);
            $carrera = Carrera::with('asignaturas')->find($carreraID);
            return $carrera != null
                ? $carrera->asignaturas->toResourceCollection()
                : response()->json(null, 404);
        } else {
            return Asignatura::with('carreras')->get()->toResourceCollection();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asignatura = Asignatura::with('carreras')->find($id);
        return $asignatura != null
            ? $asignatura->toResource()
            : response()->json(null, 404);
    }
}
