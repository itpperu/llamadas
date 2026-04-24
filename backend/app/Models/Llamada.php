<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Llamada extends Model
{
    protected $table = 'llamadas';
    protected $fillable = [
        'vendedor_id', 'dispositivo_id', 'cliente_id', 
        'telefono_origen', 'telefono_destino', 'telefono_cliente_normalizado', 
        'tipo_llamada', 'fecha_inicio', 'fecha_fin', 'duracion_segundos', 
        'estado_proceso', 'transcript_text', 'summary_text'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function audio(): HasOne
    {
        return $this->hasOne(AudioLlamada::class);
    }

    public function analisis(): HasOne
    {
        return $this->hasOne(AnalisisLlamada::class);
    }

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LogSincronizacion::class);
    }
}
