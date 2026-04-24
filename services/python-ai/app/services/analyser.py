import re
from typing import Dict, List, Any

class AnalyserService:
    def analyse(self, transcript: str, metadata: Dict[str, Any]) -> Dict[str, Any]:
        """
        Analiza el texto de la transcripción para extraer sentimiento, 
        probabilidad de venta y objeciones de forma heurística para el MVP.
        """
        transcript_lower = transcript.lower()

        # Análisis básico de sentimiento
        is_positive = any(word in transcript_lower for word in ["interesa", "quiero", "perfecto", "buen", "excelente", "comprar", "precio"])
        is_negative = any(word in transcript_lower for word in ["no", "precio alto", "competencia", "molesto", "queja", "mal", "nunca"])

        sentimiento = "neutral"
        if is_positive and not is_negative:
            sentimiento = "positivo"
        elif is_negative:
            sentimiento = "negativo"

        # Probabilidad de venta basada en palabras clave
        probabilidad = 30 # Base
        if "precio" in transcript_lower: probabilidad += 10
        if "interesa" in transcript_lower: probabilidad += 30
        if "cuando" in transcript_lower or "mañana" in transcript_lower: probabilidad += 15
        if is_negative: probabilidad -= 25
        
        probabilidad = max(0, min(100, probabilidad))

        # Detección de objeciones simples
        objeciones = []
        if "caro" in transcript_lower or "precio alto" in transcript_lower:
            objeciones.append("Precio alto")
        if "competencia" in transcript_lower:
            objeciones.append("Comparación con competencia")
        if "pensar" in transcript_lower:
            objeciones.append("Necesita tiempo para decidir")

        # Generación de resumen simple
        resumen = transcript[:150] + "..." if len(transcript) > 150 else transcript
        
        # Siguiente acción sugerida
        siguiente_accion = "Seguimiento comercial estándar."
        if probabilidad > 60:
            siguiente_accion = "Enviar propuesta formal de inmediato."
        elif sentimiento == "negativo":
            siguiente_accion = "Escalación con supervisor por insatisfacción."

        return {
            "sentimiento": sentimiento,
            "probabilidad_venta": probabilidad,
            "objeciones": objeciones,
            "resumen": f"Llamada {metadata['tipo']} analizada. Resumen: {resumen}",
            "siguiente_accion": siguiente_accion
        }
