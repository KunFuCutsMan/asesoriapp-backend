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
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8|unique:estudiante',
            'contrasena' => ['required', 'confirmed', Password::defaults()],
            'nombre' => 'required|string|max:32',
            'apellidoPaterno' => 'required|string|max:32',
            'apellidoMaterno' => 'required|string|max:32',
            'numeroTelefono' => 'required|integer|min_digits:10|max_digits:10',
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
            'numeroTelefono' => $request->input('numeroTelefono'),
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
        $estudiante = Estudiante::find($id);
        if (!$estudiante) {
            abort(404); // No se encontro el estudiante
        }

        if ($request->user()->id != $id) {
            abort(403); // No se puede editar otro usuario
        }

        if ($request->user()->id != $id && $request->user()->tokenCant('role:admin')) {
            abort(403); // Si no eres admin no puedes editar otros usuarios
        }

        $fields = $request->validate([
            'numeroControl' => 'string|integer|min_digits:8|max_digits:8|unique:estudiante',
            'contrasena' => ['confirmed', Password::defaults()],
            'nombre' => 'string|max:32',
            'apellidoPaterno' => 'string|max:32',
            'apellidoMaterno' => 'string|max:32',
            'numeroTelefono' => 'integer|min_digits:10|max_digits:10',
            'semestre' => 'numeric|integer|gt:0',
            'carreraID' => ['required', 'numeric', 'integer', new IDExistsInTable('carrera')]
        ]);

        $modificable = [
            'numeroControl',
            'nombre',
            'apellidoPaterno',
            'apellidoMaterno',
            'semestre',
            'carreraID'
        ];

        foreach ($modificable as $key) {
            if (array_key_exists($key, $fields)) {
                $estudiante->{$key} = $fields[$key];
            }
        }

        $estudiante->save();
        $estudiante->refresh();
        return response()->json($estudiante);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
