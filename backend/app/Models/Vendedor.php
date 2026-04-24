<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Vendedor extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'vendedores';
    protected $fillable = ['nombre', 'usuario', 'password_hash', 'telefono_corporativo', 'estado'];
    protected $hidden = ['password_hash'];

    public function dispositivos(): HasMany
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class);
    }
}
