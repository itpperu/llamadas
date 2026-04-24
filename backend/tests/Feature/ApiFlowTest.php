<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Vendedor;
use App\Models\Dispositivo;
use App\Models\Llamada;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessCallAIJob;
use Illuminate\Http\UploadedFile;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    private $vendedor;
    private $dispositivo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->vendedor = Vendedor::create([
            'nombre' => 'Test Vendedor',
            'usuario' => 'testuser',
            'password_hash' => bcrypt('password123'),
            'telefono_corporativo' => '999999999',
            'estado' => 'activo'
        ]);

        $this->dispositivo = Dispositivo::create([
            'vendedor_id' => $this->vendedor->id,
            'device_uuid' => 'test-device-uuid',
            'modelo' => 'Samsung S23',
            'version_android' => '14',
            'app_version' => '1.0.0',
            'activo' => true
        ]);
    }

    public function test_login_exitoso_y_retorna_sanctum_token()
    {
        $response = $this->postJson('/api/auth/login', [
            'usuario' => 'testuser',
            'password' => 'password123',
            'device_uuid' => 'test-device-uuid'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data' => ['token', 'vendedor']]);
    }

    public function test_rutas_protegidas_requieren_auth()
    {
        $response = $this->postJson('/api/calls', []);
        
        // Debe rebotar el Sanctum por el auth:sanctum middleware
        $response->assertStatus(401);
    }

    public function test_registro_de_llamada()
    {
        $token = $this->vendedor->createToken('test-device-uuid')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calls', [
            'device_uuid' => 'test-device-uuid',
            'vendedor_id' => $this->vendedor->id,
            'telefono_cliente' => '+51999999999',
            'tipo' => 'saliente',
            'fecha_inicio' => '2026-03-30 09:00:00',
            'fecha_fin' => '2026-03-30 09:05:00',
            'duracion_segundos' => 300,
            'estado_audio' => 'pendiente'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('llamadas', [
            'telefono_cliente' => '+51999999999',
            'tipo' => 'saliente',
            'vendedor_id' => $this->vendedor->id
        ]);
        
        // Verifica escritura en LogSincronizacion
        $this->assertDatabaseHas('logs_sincronizacion', [
            'tipo_evento' => 'registro_llamada'
        ]);
    }

    public function test_subida_de_audio_y_despacho_de_job()
    {
        Queue::fake();
        Storage::fake('local');
        
        $token = $this->vendedor->createToken('test-device-uuid')->plainTextToken;

        $llamada = Llamada::create([
            'dispositivo_id' => $this->dispositivo->id,
            'vendedor_id' => $this->vendedor->id,
            'telefono_cliente' => '+51999999999',
            'tipo' => 'entrante',
            'fecha_inicio' => now(),
            'duracion_segundos' => 10,
            'estado_audio' => 'pendiente',
            'estado_proceso' => 'pendiente'
        ]);

        $file = UploadedFile::fake()->create('prueba.mp3', 100, 'audio/mpeg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calls/' . $llamada->id . '/audio', [
            'audio_file' => $file,
            'mime_type' => 'audio/mpeg',
            'source_mode' => 'auto'
        ]);

        $response->assertStatus(200);

        // Verifica que se subió a BD
        $this->assertDatabaseHas('audios_llamada', [
            'llamada_id' => $llamada->id,
            'file_name' => 'prueba.mp3'
        ]);

        // Verifica log de subida
        $this->assertDatabaseHas('logs_sincronizacion', [
            'tipo_evento' => 'subida_audio'
        ]);

        // Verifica encolamiento
        Queue::assertPushed(ProcessCallAIJob::class);
    }
}
