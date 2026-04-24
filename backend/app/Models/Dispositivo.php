<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispositivo extends Model
{
    protected $table = 'dispositivos';
    protected $fillable = ['vendedor_id', 'device_uuid', 'marca', 'modelo', 'version_android', 'activo', 'ultimo_sync_at'];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_sync_at' => 'datetime',
    ];

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class);
    }
}
