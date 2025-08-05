<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EspecialidadResource extends JsonResource
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
            'nombre' => $this->id,
            'carreraID' => $this->carreraID,
            'estudiantes' => EstudianteResource::collection($this->whenLoaded('estudiantes')),
        ];
    }
}
