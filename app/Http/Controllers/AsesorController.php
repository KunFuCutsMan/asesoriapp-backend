<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsesorDataResource;
use App\Models\Asesor;
use App\Models\Asignatura;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsesorController extends Controller
{
    public function asesoresOfAsignatura(Request $request, int $asignaturaID): JsonResponse
    {
        /** @var Asignatura */
        $asignatura = Asignatura::find($asignaturaID);
        if (!$asignatura) {
            abort(404);
        }

        $asesoresIdeales = $asignatura->asesores()
            ->with('estudiante')
            ->get();

        $otrosAsesores = Asesor::with('estudiante')
            ->get()
            ->diff($asesoresIdeales);

        return response()->json([
            'data' => [
                'ideales' => AsesorDataResource::collection($asesoresIdeales),
                'otros' => AsesorDataResource::collection($otrosAsesores),
            ]
        ]);
    }
}
