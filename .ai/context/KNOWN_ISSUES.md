# KNOWN_ISSUES.md

## KI-001
### Problema
Restricciones Android sobre acceso a grabaciones

### Impacto
Puede impedir automatización total del proceso de asociación de audio.

### Mitigación
Se adoptó flujo asistido (SAF - Storage Access Framework). El vendedor selecciona manualmente el archivo de grabación. Documentado en PILOT_GUIDE.md como riesgo #2.

### Estado
Mitigado con workaround. No hay automatización completa.

---

## KI-002
### Problema
Desfase entre la hora registrada en call log y la hora de creación del archivo de audio

### Impacto
Puede producir asociaciones incorrectas en el flujo asistido.

### Mitigación
Se confía en la selección manual del vendedor. Para fases futuras: usar ventana de tolerancia, duración, número y confirmación automatizada.

### Estado
Aceptado como riesgo del MVP.

---

## KI-003
### Problema
Conectividad móvil inestable

### Impacto
La app puede no subir metadata o audio inmediatamente.

### Mitigación
Implementado: cola local en Room (AppDatabase + CallDao) + WorkManager con reintentos automáticos (SyncCallWorker, SyncAudioWorker).

### Estado
Resuelto.

---

## KI-004
### Problema
Archivos muy grandes o formatos variables

### Impacto
Puede afectar tiempos de subida, almacenamiento y procesamiento IA. Llamadas >45 min pueden causar timeout.

### Mitigación
Validación de MIME/type en UploadAudioRequest. Límite sugerido de 45 min por llamada. Timeout de 60s en AIWorkerService.

### Estado
Parcialmente mitigado. Sin compresión implementada.

---

## KI-005
### Problema
Errores de transcripción (modelo Whisper tiny)

### Impacto
El análisis comercial puede ser incorrecto por bajo accuracy del modelo tiny.

### Mitigación
Guardar transcript bruto, permitir reproceso (RequestReprocessAction). El modelo puede escalarse a `base` o `small` cambiando un parámetro en TranscriptionService.

### Estado
Aceptado para MVP. Escalable.

---

## KI-006
### Problema
Llamadas sin audio asociado

### Impacto
Se pierde valor para análisis IA.

### Mitigación
Estado `audio_pendiente` permite flujo operativo de recuperación. El vendedor puede reintentar la asociación asistida en cualquier momento.

### Estado
Resuelto con flujo asistido.

---

## KI-007
### Problema
Ruta /settings no registrada en web.php

### Impacto
El panel de diagnóstico del sistema (SettingsController) existe pero es inaccesible desde el navegador.

### Mitigación
Ruta registrada en web.php bajo el grupo settings con middleware auth.

### Estado
Resuelto (2026-04-03).

---

## KI-008
### Problema
Carpeta services/ai-worker obsoleta coexiste con services/python-ai

### Impacto
Confusión para futuros agentes o desarrolladores sobre cuál es el servicio IA activo.

### Mitigación
Carpeta services/ai-worker eliminada del repositorio.

### Estado
Resuelto (2026-04-03).

---

## KI-009
### Problema
Análisis heurístico limitado por keywords

### Impacto
El AnalyserService usa búsqueda simple de palabras clave. No distingue contexto ni negación compleja ("no me interesa" podría detectar "interesa" como positivo).

### Mitigación
Suficiente para MVP. En fases futuras, integrar un modelo de lenguaje (GPT, Claude) para análisis contextual.

### Estado
Aceptado para MVP.

---

## KI-010
### Problema
Directorio `backend_tmp/` en la raíz del proyecto

### Impacto
Confusión para agentes y desarrolladores sobre si ese directorio era el backend activo.

### Causa raíz
Scaffold vacío de Laravel 8.75 generado al inicio del proyecto. No tenía `app/`, `database/` ni `routes/` propios. Nunca fue referenciado por ningún script, Docker Compose ni archivo de configuración.

### Resolución
Eliminado el 2026-04-24. El backend activo es exclusivamente `backend/` (Laravel 11, dockerizado).

### Estado
Resuelto (2026-04-24).
