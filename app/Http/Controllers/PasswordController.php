<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PasswordCode;
use App\Notifications\SendPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    //
    public function sendPasswordMessage(Request $request)
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'numeroTelefono' => 'required|integer|min_digits:10|max_digits:10',
        ]);

        /** @var Estudiante */
        $estudiante = Estudiante::where([
            'numeroControl' => $request->input('numeroControl'),
            'numeroTelefono' => $request->input('numeroTelefono'),
        ])->firstOrFail();

        $passwordCode = PasswordCode::factory()->state([
            'estudianteID' => $estudiante->id
        ])->create();
        $passwordCode->refresh();

        $estudiante->notify((new SendPasswordReset($passwordCode->code))->afterCommit());
        return response()->json();
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'numeroTelefono' => 'required|integer|min_digits:10|max_digits:10',
            'code' => 'required|string|integer|min_digits:6|max_digits:6',
            'contrasena' => ['required', 'confirmed', Password::default()]
        ]);

        /** @var Estudiante */
        $estudiante = Estudiante::where([
            'numeroControl' => $request->input('numeroControl'),
            'numeroTelefono' => $request->input('numeroTelefono'),
        ])->firstOrFail();

        // Revisa si el codigo activo es el mismo enviado
        $passwordCode = $estudiante->activePasswordCode;
        if ($passwordCode == null) abort(404); // NO se encontró
        if ($passwordCode->code != $request->input('code')) abort(400); // Ese no es

        $estudiante->forceFill([
            // Al hacer el set se realiza un hash
            'contrasena' => $request->input('contrasena')
        ]);
        $estudiante->save();

        $passwordCode->used = true;
        $passwordCode->save();

        return response(null, 200);
    }
}
