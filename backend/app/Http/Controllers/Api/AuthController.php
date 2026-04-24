<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Hash;
use App\Models\LogSincronizacion;

class AuthController
{
    public function login(LoginRequest $request)
    {
        $vendedor = Vendedor::where('usuario', $request->usuario)->first();
        
        if (!$vendedor || !Hash::check($request->password, $vendedor->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $dispositivo = $vendedor->dispositivos()->where('device_uuid', $request->device_uuid)->where('activo', true)->first();
        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo no autorizado o inactivo'
            ], 401);
        }

        LogSincronizacion::create([
            'dispositivo_id' => $dispositivo->id,
            'tipo_evento' => 'login_exitoso',
            'payload_json' => [],
            'resultado' => 'ok'
        ]);

        $token = $vendedor->createToken($request->device_uuid)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'login ok',
            'data' => [
                'token' => $token,
                'vendedor' => [
                    'id' => $vendedor->id,
                    'nombre' => $vendedor->nombre
                ]
            ]
        ]);
    }
}
