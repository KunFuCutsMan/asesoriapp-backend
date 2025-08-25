<?php

namespace App\Http\Resources;

use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsesorDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'asesor' => AsesorResource::make($this),
            'estudiante' => $this->estudiante,
            'asignaturas' => AsignaturaResource::collection($this->asignaturas),
        ];
    }
}
