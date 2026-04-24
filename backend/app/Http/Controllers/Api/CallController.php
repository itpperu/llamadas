<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RegisterCallRequest;
use App\Http\Requests\UploadAudioRequest;
use Illuminate\Http\Request;
use App\Actions\RegisterCallAction;
use App\Actions\UploadAudioAction;
use App\Actions\RequestReprocessAction;

class CallController
{
    public function store(RegisterCallRequest $request, RegisterCallAction $action)
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'llamada registrada',
            'data' => $result
        ]);
    }

    public function uploadAudio(UploadAudioRequest $request, $call_id, UploadAudioAction $action)
    {
        $result = $action->execute($call_id, $request->file('audio_file'), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'audio subido',
            'data' => $result
        ]);
    }

    public function reprocess(Request $request, $call_id, RequestReprocessAction $action)
    {
        $action->execute($call_id);

        return response()->json([
            'success' => true,
            'message' => 'reproceso en cola'
        ]);
    }
}
