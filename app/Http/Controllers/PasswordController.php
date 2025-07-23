<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PasswordCode;
use App\Notifications\SendPasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    //
    public function sendPasswordMessage(Request $request): JsonResponse
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

    public function verifyPasswordCode(Request $request): JsonResponse
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'numeroTelefono' => 'required|integer|min_digits:10|max_digits:10',
            'code' => 'required|string|integer|min_digits:6|max_digits:6',
        ]);

        /** @var Estudiante */
        $estudiante = Estudiante::where([
            'numeroControl' => $request->input('numeroControl'),
            'numeroTelefono' => $request->input('numeroTelefono'),
        ])->firstOrFail();

        $this->revisaCodigoDeUsuario($estudiante, $request->input('code'));

        $token = $estudiante->createToken('passwordReset', ['password:reset'], now()->addMinutes(5))->plainTextToken;
        return response()->json([
            'token' => $token
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|integer|min_digits:6|max_digits:6',
            'contrasena' => ['required', 'confirmed', Password::default()]
        ]);

        /** @var Estudiante */
        $estudiante = $request->user();

        if ($estudiante == null) {
            abort(401);
        }

        if ($estudiante->tokenCant('password:reset')) {
            abort(401);
        }

        $passwordCode = $this->revisaCodigoDeUsuario($estudiante, $request->input('code'));

        $estudiante->forceFill([
            // Al hacer el set se realiza un hash
            'contrasena' => $request->input('contrasena')
        ]);
        $estudiante->save();

        $passwordCode->used = true;
        $passwordCode->save();

        $request->user()->currentAccessToken()->delete();

        return response()->json([], 200);
    }

    private function revisaCodigoDeUsuario(Estudiante $estudiante, string $code)
    {
        // Revisa si el codigo activo es el mismo enviado
        $passwordCode = $estudiante->activePasswordCode;
        if ($passwordCode == null) abort(404); // No se encontró
        if ($passwordCode->code != $code) abort(400); // Ese no es
        return $passwordCode;
    }
}
