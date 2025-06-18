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

        $resp->dump();

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
}
