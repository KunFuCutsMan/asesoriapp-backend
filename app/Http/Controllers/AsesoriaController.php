<?php

namespace App\Http\Controllers;

use App\Models\Asesoria;
use App\Rules\IDExistsInTable;
use Illuminate\Http\Request;

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

        $estudiante = $request->user();

        $asesoria = new Asesoria([
            'diaAsesoria' => $request->date('diaAsesoria'),
            'carreraID' => $request->input('carreraID'),
            'asignaturaID' => $request->input('asignaturaID'),
            'horaInicial' => $request->input('horaInicial'),
            'horaFinal' => $request->input('horaFinal'),
            'estudianteID' => $estudiante->id,
        ]);

        $asesoria->save();
        $asesoria->refresh();

        return $asesoria;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asesoria = Asesoria::find($id);
        return $asesoria;
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
