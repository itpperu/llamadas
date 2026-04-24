<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio_file' => 'required|file|mimes:mp3,wav,m4a,amr|max:51200',
            'audio_hash' => 'nullable|string',
            'mime_type' => 'nullable|string',
            'source_mode' => 'nullable|string'
        ];
    }
}
