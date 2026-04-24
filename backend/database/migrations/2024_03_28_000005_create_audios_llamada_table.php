<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audios_llamada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('llamada_id')->unique()->constrained('llamadas')->cascadeOnDelete();
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_hash')->nullable();
            $table->string('source_mode')->default('auto'); // auto o manual
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audios_llamada');
    }
};
