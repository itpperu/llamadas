<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vendedor;
use App\Models\Dispositivo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterVendedorCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:register-vendedor 
                            {nombre : Nombre completo del vendedor} 
                            {usuario : Usuario para login} 
                            {password : Contraseña inicial} 
                            {device_uuid : UUID del dispositivo corporativo asignado}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Registra un nuevo vendedor y su dispositivo asociado para el piloto real.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nombre = $this->argument('nombre');
        $usuario = $this->argument('usuario');
        $password = $this->argument('password');
        $device_uuid = $this->argument('device_uuid');

        try {
            DB::beginTransaction();

            // 1. Crear Vendedor
            $vendedor = Vendedor::updateOrCreate(
                ['usuario' => $usuario],
                [
                    'nombre' => $nombre,
                    'password_hash' => Hash::make($password),
                    'estado' => 'activo'
                ]
            );

            // 2. Asociar Dispositivo
            $dispositivo = Dispositivo::updateOrCreate(
                ['device_uuid' => $device_uuid],
                [
                    'vendedor_id' => $vendedor->id,
                    'marca' => 'Asignado corporativo',
                    'modelo' => 'Piloto',
                    'activo' => true
                ]
            );

            DB::commit();

            $this->info("✅ Vendedor registrado exitosamente:");
            $this->line("- Nombre: {$vendedor->nombre}");
            $this->line("- Usuario: {$vendedor->usuario}");
            $this->line("- Dispositivo vinculado: {$dispositivo->device_uuid}");
            $this->info("¡Ya puede iniciar sesión desde la App Android!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error al registrar: " . $e->getMessage());
        }
    }
}
