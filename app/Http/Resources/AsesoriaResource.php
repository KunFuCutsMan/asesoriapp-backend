<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsesoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'diaAsesoria' => $this->diaAsesoria,
            'horaInicial' => $this->horaInicial,
            'horaFinal' => $this->horaFinal,
            'carrera' => $this->whenLoaded('carreraAsignatura', function () {
                return [
                    'id' => $this->carreraAsignatura->id,
                    'nombre' => $this->carreraAsignatura->nombre,
                ];
            }),
            'asignatura' => $this->whenLoaded('asignatura', function () {
                return [
                    'id' => $this->asignatura->id,
                    'nombre' => $this->asignatura->nombre,
                ];
            }),
            'estadoAsesoria' => $this->estadoAsesoria,
            'estudianteID' => $this->estudianteID,
            'asesor' => $this->asesor,
        ];
    }
}
