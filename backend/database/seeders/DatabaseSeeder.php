<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador Inicial para el Panel
        User::updateOrCreate(
            ['email' => 'admin@grabacion.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('admin123'),
                'rol' => 'admin'
            ]
        );

        echo "Usuario administrador creado: admin@grabacion.com / admin123\n";
    }
}
