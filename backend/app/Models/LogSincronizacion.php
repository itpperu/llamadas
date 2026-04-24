<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSincronizacion extends Model
{
    protected $table = 'logs_sincronizacion';
    const UPDATED_AT = null; // Solo tiene created_at en el esquema

    protected $fillable = [
        'dispositivo_id', 'llamada_id', 'tipo_evento', 'payload_json', 'resultado'
    ];

    protected $casts = [
        'payload_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function llamada(): BelongsTo
    {
        return $this->belongsTo(Llamada::class);
    }
}
