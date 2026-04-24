from pydantic import BaseModel
from typing import List, Optional

class Metadata(BaseModel):
    duracion: int
    tipo: str
    telefono_cliente: str

class ProcessCallRequest(BaseModel):
    call_id: int
    audio_path: str
    metadata: Metadata

class AnalysisResponse(BaseModel):
    transcript: str
    sentimiento: str  # positivo|neutral|negativo
    probabilidad_venta: int  # 0-100
    objeciones: List[str]
    resumen: str
    siguiente_accion: str
    modelo_version: str = "whisper-tiny-v1"
