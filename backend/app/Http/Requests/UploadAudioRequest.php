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
            'audio_file' => 'required|file|max:102400|mimetypes:audio/mpeg,audio/mp3,audio/mp4,audio/x-mpeg,audio/wav,audio/x-wav,audio/m4a,audio/aac,audio/amr,audio/ogg,audio/opus,audio/3gpp,audio/x-m4a,video/mp4',
            'audio_hash' => 'nullable|string',
            'mime_type' => 'nullable|string',
            'source_mode' => 'nullable|string'
        ];
    }
}
