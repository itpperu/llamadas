<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class PilotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando Seeder del Piloto...');

        // Vendedor 1
        Artisan::call('app:register-vendedor', [
            'nombre' => 'Juan Pérez (Piloto 1)',
            'usuario' => 'juanp1',
            'password' => 'piloto2024',
            'device_uuid' => 'uuid-android-001'
        ]);
        
        $this->command->info('Juan Pérez registrado.');

        // Vendedor 2
        Artisan::call('app:register-vendedor', [
            'nombre' => 'María García (Piloto 2)',
            'usuario' => 'mariag2',
            'password' => 'piloto2024',
            'device_uuid' => 'uuid-android-002'
        ]);
        
        $this->command->info('María García registrada.');

        $this->command->info('✅ El sistema está listo para el inicio del piloto.');
    }
}
