<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HorarioResource extends JsonResource
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
            'horaInicio' => $this->horaInicio,
            'disponible' => boolval($this->disponible),
            'diaSemana' => $this->diaSemana,
            'asesor' => $this->asesor,
        ];
    }
}
