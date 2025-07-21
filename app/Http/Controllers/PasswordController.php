<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PasswordCode;
use App\Notifications\SendPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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

        $code = $this->generateCode();

        $passwordCode = new PasswordCode();
        $passwordCode->code = $code;
        $passwordCode->estudianteID = $estudiante->id;
        $passwordCode->save();

        $estudiante->notify((new SendPasswordReset($code))->afterCommit());
        return response();
    }

    private function generateCode(): string
    {
        $digits = Arr::random([1, 2, 3, 4, 5, 6, 7, 8, 9, 0], 6, true);
        return Arr::join($digits, '');
    }
}
