<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioLlamada extends Model
{
    protected $table = 'audios_llamada';
    protected $fillable = [
        'llamada_id', 'storage_disk', 'storage_path', 'file_name', 
        'mime_type', 'file_size', 'file_hash', 'source_mode', 'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function llamada(): BelongsTo
    {
        return $this->belongsTo(Llamada::class);
    }
}
