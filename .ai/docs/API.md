# API.md

## Propósito
Definir los endpoints iniciales del MVP para la interacción entre Android y Laravel.

## Principios
- respuestas JSON consistentes
- validación en backend
- tokens de autenticación
- soporte a reintentos
- errores claros para la app móvil

## Endpoints iniciales

### POST /api/auth/login
Autentica vendedor o dispositivo.

#### Request
```json
{
  "usuario": "vendedor01",
  "password": "******",
  "device_uuid": "android-001"
}
```

#### Response
```json
{
  "success": true,
  "message": "login ok",
  "data": {
    "token": "TOKEN",
    "vendedor": {
      "id": 1,
      "nombre": "Juan Pérez"
    }
  }
}
```

---

### POST /api/calls
Registra la metadata de una llamada.

#### Request
```json
{
  "device_uuid": "android-001",
  "vendedor_id": 1,
  "telefono_cliente": "+51999999999",
  "tipo": "saliente",
  "fecha_inicio": "2026-03-28 10:00:00",
  "fecha_fin": "2026-03-28 10:03:15",
  "duracion_segundos": 195,
  "estado_audio": "pendiente"
}
```

#### Response
```json
{
  "success": true,
  "message": "llamada registrada",
  "data": {
    "call_id": 150,
    "estado_proceso": "audio_pendiente"
  }
}
```

---

### POST /api/calls/{call_id}/audio
Sube el audio asociado a la llamada.

#### Form-data
- `audio_file`
- `audio_hash`
- `mime_type`
- `source_mode` (`auto` o `manual`)

#### Response
```json
{
  "success": true,
  "message": "audio subido",
  "data": {
    "audio_id": 88,
    "estado_proceso": "audio_subido"
  }
}
```

---

### POST /api/calls/{call_id}/reprocess
Solicita reproceso de transcripción y análisis.

#### Response
```json
{
  "success": true,
  "message": "reproceso en cola"
}
```

---

### GET /api/calls
Lista llamadas con filtros.

#### Parámetros sugeridos
- `vendedor_id`
- `telefono_cliente`
- `fecha_desde`
- `fecha_hasta`
- `estado`
- `page`

---

### GET /api/calls/{call_id}
Devuelve el detalle completo de llamada:
- metadata
- audio
- transcript
- análisis
- estados
- logs relevantes

---

### GET /api/reports/vendors
Resumen por vendedor.

---

### GET /api/reports/clients
Resumen por cliente.

## Respuesta estándar sugerida
```json
{
  "success": true,
  "message": "ok",
  "data": {}
}
```

## Errores sugeridos
- 401 no autenticado
- 403 sin permisos
- 404 no encontrado
- 422 validación
- 500 error interno

## Consideraciones futuras
- idempotencia por request key
- versionado de endpoints
- paginación estandarizada
- trazabilidad de errores para soporte móvil

---

## API Externa v1 (sistemas terceros)

Documentación completa en `docs/API_EXTERNA.md`.

### Autenticación
Token generado en el panel `/settings` → "Tokens de API Externa".  
Se envía como `Authorization: Bearer callsync_xxxx`.  
Tokens almacenados con hash SHA-256, nunca en texto plano.  
Rate limit: 60 requests/minuto por token.

### GET /api/v1/calls
Retorna llamadas y sus análisis para un número telefónico o cliente específico.

**Middleware:** `api.token` + `throttle:60,1`

**Parámetros query:**

| Param | Tipo | Requerido | Descripción |
|---|---|---|---|
| `phone` | string | Uno de los dos | Número en formato internacional (+51...) |
| `cliente_id` | integer | Uno de los dos | ID interno del cliente |
| `fecha_desde` | date | No | Filtro fecha inicio (YYYY-MM-DD) |
| `fecha_hasta` | date | No | Filtro fecha fin (YYYY-MM-DD) |

**Respuesta 200:**
```json
{
  "success": true,
  "cliente": { "id": 2, "telefono": "+51912345678", "nombre": "Empresa ABC" },
  "periodo": { "fecha_desde": "2026-04-01", "fecha_hasta": "2026-04-30" },
  "total": 3,
  "llamadas": [
    {
      "id": 1,
      "fecha_inicio": "2026-04-10T12:05:30+00:00",
      "fecha_fin": "2026-04-10T12:08:30+00:00",
      "tipo": "saliente",
      "duracion_segundos": 180,
      "estado": "analizada",
      "vendedor": "Juan Pérez",
      "transcript": "...",
      "resumen": "...",
      "analisis": {
        "sentimiento_cliente": "positivo",
        "tono_general": "cordial",
        "intencion_comercial": "alta",
        "score_venta": 75,
        "objeciones": ["precio alto"],
        "siguiente_accion": "Llamar en 3 días",
        "analizado_at": "2026-04-10T12:10:00+00:00"
      }
    }
  ]
}
```

**Errores:**
- `401` — Token ausente o inválido
- `404` — Número no encontrado
- `422` — Parámetros inválidos o faltantes
- `429` — Rate limit excedido
