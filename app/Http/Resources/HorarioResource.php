<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'horaInicio' => Carbon::parse($this->horaInicio)->format('H:i:s'),
            'disponible' => boolval($this->disponible),
            'diaSemana' => $this->diaSemana,
            'asesor' => $this->asesor,
        ];
    }
}
