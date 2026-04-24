<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = ['nombre', 'token_hash', 'activo', 'expires_at'];

    protected $casts = [
        'activo'      => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function isValid(): bool
    {
        if (!$this->activo) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    public static function findByPlainToken(string $plain): ?self
    {
        $hash = hash('sha256', $plain);
        return static::where('token_hash', $hash)->first();
    }
}
