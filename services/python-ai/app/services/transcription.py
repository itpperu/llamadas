import whisper
import os
import torch

class TranscriptionService:
    def __init__(self, model_name="base"):
        # Load model only once
        print(f"Buscando modelo Whisper: {model_name}...")
        self.model = whisper.load_model(model_name)
        print("Modelo Whisper cargado exitosamente.")

    def transcribe(self, audio_path: str) -> str:
        if not os.path.exists(audio_path):
            raise FileNotFoundError(f"Audio no encontrado en la ruta: {audio_path}")

        print(f"Transcribiendo audio: {audio_path}")
        # FP16=False for CPU usage
        result = self.model.transcribe(audio_path, fp16=torch.cuda.is_available())
        return result["text"].strip()
