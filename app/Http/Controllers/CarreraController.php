<?php

namespace App\Http\Controllers;

use App\Models\Carrera;

class CarreraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carreras = Carrera::all();
        return $carreras->toResourceCollection();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $carrera = Carrera::find($id);
        return $carrera != null
            ? $carrera->toResource()
            : response()->json(null, 404);
    }
}
