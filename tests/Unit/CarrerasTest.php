<?php

namespace Tests\Unit;

use Tests\TestCase;

class CarrerasTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_Carreras_Index_Route(): void
    {
        $resp = $this->get('/api/carreras');

        $resp->assertOk();
        $resp->assertJsonIsArray();
        $resp->assertJsonStructure([
            '*' => [
                'id',
                'nombre',
                'codigo',
            ]
        ]);
    }

    public function test_Carreras_Show_Route(): void
    {
        $resp = $this->get('api/carreras/6');

        $resp->assertOk();
        $resp->assertJsonIsObject();
        $resp->assertJsonStructure([
            'id',
            'nombre',
            'codigo'
        ]);

        $this->assertStringContainsString("Mecatrónica", $resp["nombre"]);
    }
}
