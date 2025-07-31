<?php

namespace App\Http\Controllers;

use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Rules\IDExistsInTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estudiante = request()->user();
        $asesorias = Asesoria::where('estudianteID', $estudiante->id)
            ->with(['carrera', 'asignatura'])
            ->get();

        return response()->json($asesorias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
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
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE
        ]);

        $asesoria->save();
        $asesoria->refresh();

        return response()->json($asesoria, 201);
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
    public function update(Request $request, string $id): JsonResponse
    {
        if ($request->user()->tokenCan('role:admin')) {

            if ($request->has('asesorID')) {
                return $this->asignaAsesor($request, $id);
            }
        }

        return abort(400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function asignaAsesor(Request $request, int $asesoriaID): JsonResponse
    {
        $request->validate([
            'asesorID' => ['required', 'numeric', 'integer', new IDExistsInTable('asesor')]
        ]);

        $asesoria = Asesoria::findOrFail($asesoriaID);

        if ($asesoria->estadoAsesoriaID !== AsesoriaEstado::PENDIENTE) {
            return abort(400);
        }

        $asesoria->asesorID = $request->input('asesorID');
        $asesoria->save();

        return response()->json($asesoria);
    }
}
