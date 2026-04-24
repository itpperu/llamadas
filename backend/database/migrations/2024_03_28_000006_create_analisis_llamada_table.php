<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_llamada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('llamada_id')->unique()->constrained('llamadas')->cascadeOnDelete();
            $table->string('modelo_version')->nullable();
            $table->string('sentimiento_cliente')->nullable();
            $table->string('tono_general')->nullable();
            $table->string('intencion_comercial')->nullable();
            $table->integer('score_venta')->nullable();
            $table->json('objeciones_json')->nullable();
            $table->text('siguiente_accion')->nullable();
            $table->json('analisis_json')->nullable(); // Guardar el payload entero por seguridad
            $table->timestamp('analizado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_llamada');
    }
};
