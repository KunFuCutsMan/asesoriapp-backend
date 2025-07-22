<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\PasswordCode;
use App\Notifications\SendPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PasswordCodeTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * Prueba si se creó un código para el usuario, y que se mandó
     */
    public function test_generate_password_reset_code(): void
    {
        /** @var Estudiante */
        $estudiante = Estudiante::factory()->state([
            'numeroTelefono' => '2294581498'
        ])->create();

        Notification::fake();

        $response = $this->post('/api/v1/password', [
            'numeroControl' => $estudiante->numeroControl,
            'numeroTelefono' => $estudiante->numeroTelefono,
        ]);

        $response->assertOk();
        Notification::assertSentTo($estudiante, SendPasswordReset::class);

        // ¿Se creó el código?
        $estudiante->refresh();
        $codigo = $estudiante->passwordCode;

        $this->assertNotEmpty($codigo);
        $this->assertEquals(6, strlen($codigo->code));
        $this->assertIsNumeric($codigo->code);
    }

    /**
     * Dado un código en la DB, se espera que:
     * 
     * 1. El estudiante no se acuerde de su contraseña
     * 2. Solicite un nuevo código (probado en otro test)
     * 3. Envia su código para verificarlo, y recibe un token de vuelta
     * 3. Utilice dicho código para cambiar su contraseña
     * 4. Ingrese sesión con la nueva contraseña
     */
    public function test_estudiante_utiliza_password_reset_code(): void
    {
        /** @var PasswordCode */
        $passwordCode = PasswordCode::factory()->state([
            'created_at' => now()->subMinutes(3) // Apenas le llegó el mensaje
        ])->create();
        /** @var Estudiante */
        $estudiante = $passwordCode->estudiante;
        $contrasena = '1234asdF';

        $this->assertModelExists($passwordCode);
        $this->assertModelExists($estudiante);

        // El estudiante se le olvida su contraseña
        $wrongLoginResponse = $this->post('api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $contrasena,
        ]);
        $wrongLoginResponse->assertClientError();

        // Solicita una nueva contraseña
        // (Envia la solicitud del codigo)

        // Utiliza dicho codigo
        $verifyPasswordCodeResponse = $this->post('api/v1/password/verify', [
            'numeroControl' => $estudiante->numeroControl,
            'numeroTelefono' => $estudiante->numeroTelefono,
            'code' => $passwordCode->code,
        ]);

        $verifyPasswordCodeResponse->assertOk();

        // Con el nuevo código el estudiante cambia su contraseña
        Sanctum::actingAs($estudiante, ['password:reset']);
        $newPasswordResponse = $this->patch('api/v1/password', [
            'code' => $passwordCode->code,
            'contrasena' => $contrasena,
            'contrasena_confirmation' => $contrasena,
        ]);

        $newPasswordResponse->assertOk();

        // Checa que no existe el token 'passwordReset'
        foreach ($estudiante->tokens() as $token) {
            $this->assertStringNotContainsString('passwordReset', $token->name);
        }


        // E ingresa sesión
        $correctLoginResponse = $this->post('api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $contrasena,
        ]);
        $correctLoginResponse->assertOk();
    }

    public function test_estudiante_no_puede_utilizar_codigo_expirado(): void
    {
        /** @var PasswordCode */
        $passwordCode = PasswordCode::factory()->state([
            'created_at' => now()->subMinutes(20) // Expiró hace 20 minutos
        ])->create();
        $estudiante = $passwordCode->estudiante;
        $contrasena = '1234asdF';

        $this->assertModelExists($passwordCode);
        $this->assertModelExists($estudiante);

        // El estudiante se le olvida su contraseña
        $wrongLoginResponse = $this->post('api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $contrasena,
        ]);
        $wrongLoginResponse->assertClientError();

        // Solicita una nueva contraseña

        // No se puede utilizar el codigo porque expiró
        Sanctum::actingAs($estudiante, ['password:reset']);
        $newPasswordResponse = $this->patch('api/v1/password', [
            'code' => $passwordCode->code,
            'contrasena' => $contrasena,
            'contrasena_confirmation' => $contrasena,
        ]);

        $newPasswordResponse->assertClientError();
    }

    public function test_estudiante_no_puede_utilizar_codigo_utilizado(): void
    {
        /** @var PasswordCode */
        $passwordCode = PasswordCode::factory()->state([
            'created_at' => now()->subMinutes(3),
            'used' => true,
        ])->create();
        $estudiante = $passwordCode->estudiante;
        $contrasena = '1234asdF';

        $this->assertModelExists($passwordCode);
        $this->assertModelExists($estudiante);

        // El estudiante se le olvida su contraseña
        $wrongLoginResponse = $this->post('api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $contrasena,
        ]);
        $wrongLoginResponse->assertClientError();

        // Solicita una nueva contraseña

        // No se puede utilizar el codigo porque expiró
        Sanctum::actingAs($estudiante, ['password:reset']);
        $newPasswordResponse = $this->patch('api/v1/password', [
            'code' => $passwordCode->code,
            'contrasena' => $contrasena,
            'contrasena_confirmation' => $contrasena,
        ]);

        $newPasswordResponse->assertClientError();
    }

    public function test_estudiante_no_puede_cambiar_contrasena_sin_token(): void
    {
        /** @var PasswordCode */
        $passwordCode = PasswordCode::factory()->state([
            'created_at' => now()->subMinutes(3),
            'used' => true,
        ])->create();
        $estudiante = $passwordCode->estudiante;
        $contrasena = '1234asdF';

        $this->assertModelExists($passwordCode);
        $this->assertModelExists($estudiante);

        $noTokenResponse = $this->patch('api/v1/password', [
            'code' => $passwordCode->code,
            'contrasena' => $contrasena,
            'contrasena_confirmation' => $contrasena,
        ]);

        $noTokenResponse->assertUnauthorized();
    }
}
