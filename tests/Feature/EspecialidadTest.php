<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EspecialidadTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    public function test_obten_especialidades(): void
    {
        $response = $this->get('/api/v1/especialidades');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'nombre',
                'carrera' => [
                    'id',
                    'nombre',
                ],
            ],
        ]);
    }

    public function test_obten_especialidad_de_carrera(): void
    {
        $response = $this->get('api/v1/carreras/6/especialidades');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'nombre',
                'carreraID',
            ],
        ]);
        $response->assertJsonCount(3);
    }

    public function test_obten_especialidad_por_id(): void
    {
        $response = $this->get('api/v1/especialidades/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'nombre',
            'carreraID',
        ]);
    }

    public function test_obten_especialidad_no_encontrada(): void
    {
        $response = $this->get('api/v1/especialidades/999');
        $response->assertStatus(404);
    }
}
