<?php

namespace App\Http\Controllers;

use App\Rules\DateIsAfter;
use App\Rules\IDExistsInTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsesoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'carreraID' => ['required', 'numeric', 'integer', new IDExistsInTable('carrera')],
            'asignaturaID' => ['required', 'numeric', 'integer', new IDExistsInTable('asignatura')],
            'diaAsesoria' => 'required|date',
            'horaInicial' => 'required|date_format:H:i',
            'horaFinal' => 'required|date_format:H:i|after:horaInicial'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
