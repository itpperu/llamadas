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
        # language="es" evita que detecte mal el idioma en audios ruidosos.
        # Los thresholds y temperature=0 reducen alucinaciones en clips cortos
        # (por ejemplo, "Suscríbete al canal" o frases random sin relación al audio).
        result = self.model.transcribe(
            audio_path,
            fp16=torch.cuda.is_available(),
            language="es",
            task="transcribe",
            initial_prompt="Transcripcion de llamada comercial en espanol, Peru.",
            temperature=0.0,
            condition_on_previous_text=False,
            no_speech_threshold=0.6,
            compression_ratio_threshold=2.4,
            logprob_threshold=-1.0
        )
        return result["text"].strip()
