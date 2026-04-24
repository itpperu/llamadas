<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_sincronizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispositivo_id')->constrained('dispositivos')->cascadeOnDelete();
            $table->foreignId('llamada_id')->nullable()->constrained('llamadas')->nullOnDelete();
            $table->string('tipo_evento');
            $table->json('payload_json')->nullable();
            $table->string('resultado')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_sincronizacion');
    }
};
