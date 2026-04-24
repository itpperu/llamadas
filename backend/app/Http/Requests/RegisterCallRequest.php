<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seguridad operativa: verificar que el token pertenece al vendedor que envía la metadata
        return \Illuminate\Support\Facades\Auth::check() && (int) $this->input('vendedor_id') === \Illuminate\Support\Facades\Auth::id();
    }

    public function rules(): array
    {
        return [
            'device_uuid' => 'required|string',
            'vendedor_id' => 'required|integer|exists:vendedores,id',
            'telefono_cliente' => 'required|string',
            'tipo' => 'required|in:entrante,saliente,perdida',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'duracion_segundos' => 'required|integer|min:0',
            'estado_audio' => 'nullable|string'
        ];
    }
}
