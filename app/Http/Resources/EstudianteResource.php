<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstudianteResource extends JsonResource
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
            'numeroControl' => $this->numeroControl,
            'nombre' => $this->nombre,
            'apellidoPaterno' => $this->apellidoPaterno,
            'apellidoMaterno' => $this->apellidoMaterno,
            'numeroTelefono' => $this->numeroTelefono,
            'semestre' => $this->semestre,
            'carrera' => $this->carrera,
            'especialidad' => $this->especialidad,
            'asesor' => $this->asesor,
        ];
    }
}
