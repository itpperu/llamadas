<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisLlamada extends Model
{
    protected $table = 'analisis_llamada';
    protected $fillable = [
        'llamada_id', 'modelo_version', 'sentimiento_cliente', 'tono_general',
        'intencion_comercial', 'score_venta', 'objeciones_json', 'siguiente_accion',
        'analisis_json', 'analizado_at'
    ];

    protected $casts = [
        'objeciones_json' => 'array',
        'analisis_json' => 'array',
        'analizado_at' => 'datetime',
    ];

    public function llamada(): BelongsTo
    {
        return $this->belongsTo(Llamada::class);
    }
}
