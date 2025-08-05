<?php

namespace App\Http\Resources;

use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsignaturaResource extends JsonResource
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
            'nombre' => $this->nombre,
            'carreras' => $this->whenLoaded('carreras', function () {
                return $this->carreras->map(function (Carrera $carrera) {
                    return [
                        'carreraID' => $carrera->id,
                        'semestre' => $carrera->pivot->semestre,
                    ];
                });
            }),
            'carrera' => $this->whenPivotLoaded('carrera-asignatura', function () {
                return [
                    'carreraID' => $this->whenNotNull($this->pivot->carreraID),
                    'semestre' => $this->whenNotNull($this->pivot->semestre),
                ];
            }),
        ];
    }
}
