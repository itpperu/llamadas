import os
import uvicorn
from fastapi import FastAPI, HTTPException
from app.schemas import ProcessCallRequest, AnalysisResponse
from app.services.transcription import TranscriptionService
from app.services.analyser import AnalyserService

app = FastAPI(title="Python AI Worker", version="1.0")

# Inicializar servicios (Singleton style)
whisper_service = TranscriptionService(model_name="base") # Base: mejor accuracy que tiny en español, sigue rápido en CPU
analyser_service = AnalyserService()

def map_dockered_path(provided_path: str) -> str:
    """
    Si Laravel envía una ruta interna de Docker (/var/www/html/...), 
    la mapeamos a la ruta real en Windows local si es necesario.
    """
    # Intentar detectar si es un path de Docker
    if provided_path.startswith('/var/www/html/'):
        # Asumimos que la carpeta real es el root del proyecto en el host
        # En tu caso, c:/xampp/htdocs/grabacion_llamada/backend/
        root_path = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        # El backend suele estar en ../../backend/
        base_backend = os.path.abspath(os.path.join(root_path, "../../backend/"))
        return provided_path.replace('/var/www/html/', base_backend + os.sep).replace('/', os.sep)
    
    return provided_path

@app.post("/process-call", response_model=AnalysisResponse)
async def process_call(request: ProcessCallRequest):
    print(f"Recibida petición para procesar la llamada ID: {request.call_id}")
    
    # Mapper de path si es necesario
    audio_path = map_dockered_path(request.audio_path)
    
    if not os.path.exists(audio_path):
        print(f"ERROR: Audio no encontrado en {audio_path}")
        raise HTTPException(status_code=404, detail=f"Archivo de audio no encontrado: {audio_path}")

    try:
        # 1. Transcripción
        transcript = whisper_service.transcribe(audio_path)
        
        # 2. Análisis heurístico
        analysis_data = analyser_service.analyse(transcript, request.metadata.dict())
        
        # 3. Respuesta estructurada
        response = AnalysisResponse(
            transcript=transcript,
            sentimiento=analysis_data["sentimiento"],
            probabilidad_venta=analysis_data["probabilidad_venta"],
            objeciones=analysis_data["objeciones"],
            resumen=analysis_data["resumen"],
            siguiente_accion=analysis_data["siguiente_accion"],
            modelo_version="whisper-tiny-v1"
        )
        
        print("Llamada procesada con éxito.")
        return response

    except Exception as e:
        print(f"ERROR inesperado procesando la llamada: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run("app.main:app", host="0.0.0.0", port=8001, reload=True)
