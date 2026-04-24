<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores')->cascadeOnDelete();
            $table->string('device_uuid')->unique();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('version_android')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_sync_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivos');
    }
};
