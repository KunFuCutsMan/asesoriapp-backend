<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Notifications\SendPasswordReset;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    //
    public function sendPasswordMessage(Request $request)
    {
        $request->validate([
            'numeroControl' => 'required|string|integer|min_digits:8|max_digits:8',
            'numeroTelefono' => 'required|integer|min_digits:10|max_digits:10',
        ]);

        $estudiante = Estudiante::where([
            'numeroControl' => $request->input('numeroControl'),
            'numeroTelefono' => $request->input('numeroTelefono'),
        ])->get();

        if ($estudiante == null) abort(404);

        $code = "asasa";

        $estudiante->notify((new SendPasswordReset($estudiante, $code))->afterCommit());

        return response()->json();
    }
}
