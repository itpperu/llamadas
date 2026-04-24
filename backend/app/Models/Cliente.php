<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = ['telefono_normalizado', 'nombre_referencial'];

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class);
    }
}
