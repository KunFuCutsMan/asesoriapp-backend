<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    function getToken(Request $request): JsonResponse
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'contrasena' => ['required', Password::defaults()],
        ]);

        $estudiante = Estudiante::where('numeroControl', $request->numeroControl)->first();
        if (! $estudiante || ! Hash::check($request->contrasena, $estudiante->contrasena)) {
            return response()->json([], 400);
        }

        return response()->json([
            'token' => $this->creaToken($estudiante)
        ]);
    }

    public static function creaToken(Estudiante $estudiante)
    {
        $tokenName = 'login';
        if ($estudiante->tokens()->where('name', $tokenName)->exists()) {
            $estudiante->tokens()->where('name', $tokenName)->delete();
        }
        return $estudiante->createToken($tokenName)->plainTextToken;
    }
}
