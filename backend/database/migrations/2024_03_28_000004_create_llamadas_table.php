<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores')->cascadeOnDelete();
            $table->foreignId('dispositivo_id')->nullable()->constrained('dispositivos')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            
            $table->string('telefono_origen');
            $table->string('telefono_destino');
            $table->string('telefono_cliente_normalizado');
            $table->string('tipo_llamada'); // entrante, saliente, perdida
            
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->integer('duracion_segundos')->default(0);
            
            // registrada, audio_pendiente, audio_subido, transcripcion_pendiente, transcrita, analisis_pendiente, analizada, error
            $table->string('estado_proceso')->default('registrada');
            
            $table->text('transcript_text')->nullable();
            $table->text('summary_text')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['vendedor_id', 'fecha_inicio']);
            $table->index(['telefono_cliente_normalizado', 'fecha_inicio']);
            $table->index('estado_proceso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llamadas');
    }
};
