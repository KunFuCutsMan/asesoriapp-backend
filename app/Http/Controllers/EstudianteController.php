<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Rules\IDExistsInTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\HasApiTokens;

class EstudianteController extends Controller
{
    use HasApiTokens;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estudiantes = Estudiante::all();
        return response()->json($estudiantes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8|unique:estudiante,numeroControl',
            'contrasena' => ['required', 'confirmed', Password::defaults()],
            'nombre' => 'required|string|alpha|max:32',
            'apellidoPaterno' => 'required|string|alpha|max:32',
            'apellidoMaterno' => 'required|string|alpha|max:32',
            'numeroTelefono' => 'required',
            'semestre' => 'numeric|integer|gt:0',
            'carreraID' => ['required', 'numeric', 'integer', new IDExistsInTable('carrera')]
        ]);

        $estudiante = new Estudiante([
            'numeroControl' => $request->input('numeroControl'),
            'contrasena' => $request->input('contrasena'),
            'nombre' => $request->input('nombre'),
            'apellidoPaterno' => $request->input('apellidoPaterno'),
            'apellidoMaterno' => $request->input('apellidoMaterno'),
            'semestre' => $request->input('semestre', 1),
            'carreraID' => $request->input('carreraID')
        ]);

        $estudiante->save();
        $estudiante->refresh();

        if (! $estudiante->id) {
            abort(500); // De alguna manera no se insertó
        }

        return response(null, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $estudiante = Estudiante::find($id);
        return response()->json($estudiante);
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
