<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    function getToken(Request $request)
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'contrasena' => ['required', Password::defaults()],
        ]);

        $estudiante = Estudiante::where('numeroControl', $request->numeroControl)->first();
        if (! $estudiante || ! Hash::check($request->contrasena, $estudiante->contrasena)) {
            return response(null, 400);
        }

        $nombre = $estudiante->nombre . $estudiante->apellidoPaterno . $estudiante->apeellidoMaterno;
        return $estudiante->createToken($nombre)->plainTextToken;
    }
}
