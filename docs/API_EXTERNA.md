# API Externa — AI Call Sync

Versión: **v1**  
Fecha: 2026-04-24  
Base URL: `http://<host>:8000/api/v1`

---

## Descripción

Esta API permite a sistemas externos consultar las llamadas comerciales registradas en la plataforma y sus análisis de inteligencia artificial (sentimiento, intención comercial, score de venta, objeciones detectadas).

---

## Autenticación

Todas las solicitudes deben incluir un token de acceso en el header `Authorization`.

```
Authorization: Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Cómo obtener un token

Los tokens se generan desde el panel administrativo:

1. Ingresar a `http://<host>:8000/settings`
2. Sección **"Tokens de API Externa"**
3. Completar nombre descriptivo y opcionalmente fecha de vencimiento
4. Hacer clic en **"Generar Token"**
5. **Copiar el token inmediatamente** — no se vuelve a mostrar

Los tokens pueden ser revocados en cualquier momento desde el mismo panel.

### Características de seguridad

- Los tokens se almacenan como hash SHA-256 en la base de datos (nunca en texto plano)
- Cada token puede tener fecha de vencimiento opcional
- Límite de **60 solicitudes por minuto** por token
- Si un token es comprometido, puede revocarse sin afectar a otros tokens

---

## Endpoints

### GET /api/v1/calls

Retorna el listado de llamadas y sus análisis para un número telefónico o cliente, con filtros de fecha opcionales.

#### URL

```
GET /api/v1/calls
```

#### Headers

| Header | Valor |
|---|---|
| `Authorization` | `Bearer callsync_xxxx` |
| `Accept` | `application/json` |

#### Parámetros de consulta (query string)

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `phone` | string | Sí (o `cliente_id`) | Número de teléfono en formato internacional. Ejemplo: `+51912345678` |
| `cliente_id` | integer | Sí (o `phone`) | ID interno del cliente en la plataforma |
| `fecha_desde` | string (YYYY-MM-DD) | No | Fecha de inicio del período a consultar |
| `fecha_hasta` | string (YYYY-MM-DD) | No | Fecha de fin del período a consultar |

> Se debe enviar al menos **`phone`** o **`cliente_id`**. Si se envían ambos, `phone` tiene precedencia.  
> Si no se envían fechas, se retornan **todas** las llamadas del cliente.

---

#### Respuesta exitosa — 200 OK

```json
{
  "success": true,
  "cliente": {
    "id": 2,
    "telefono": "+51912345678",
    "nombre": "Empresa ABC"
  },
  "periodo": {
    "fecha_desde": "2026-04-01",
    "fecha_hasta": "2026-04-30"
  },
  "total": 2,
  "llamadas": [
    {
      "id": 14,
      "fecha_inicio": "2026-04-10T12:05:30+00:00",
      "fecha_fin": "2026-04-10T12:08:30+00:00",
      "tipo": "saliente",
      "duracion_segundos": 180,
      "estado": "analizada",
      "vendedor": "Juan Pérez",
      "transcript": "Vendedor: Buenos días, le llamo para...\nCliente: Sí, me interesa el producto...",
      "resumen": "El cliente mostró interés en el producto. Solicitó más información sobre precios.",
      "analisis": {
        "sentimiento_cliente": "positivo",
        "tono_general": "cordial",
        "intencion_comercial": "alta",
        "score_venta": 78,
        "objeciones": [
          "precio alto"
        ],
        "siguiente_accion": "Enviar cotización y llamar en 3 días",
        "analizado_at": "2026-04-10T12:11:45+00:00"
      }
    },
    {
      "id": 15,
      "fecha_inicio": "2026-04-14T09:30:00+00:00",
      "fecha_fin": "2026-04-14T09:31:10+00:00",
      "tipo": "entrante",
      "duracion_segundos": 70,
      "estado": "analizada",
      "vendedor": "María López",
      "transcript": "...",
      "resumen": "...",
      "analisis": {
        "sentimiento_cliente": "neutral",
        "tono_general": "indiferente",
        "intencion_comercial": "media",
        "score_venta": 42,
        "objeciones": [],
        "siguiente_accion": "Hacer seguimiento en una semana",
        "analizado_at": "2026-04-14T09:33:00+00:00"
      }
    }
  ]
}
```

#### Campos de respuesta

##### Objeto raíz

| Campo | Tipo | Descripción |
|---|---|---|
| `success` | boolean | `true` si la solicitud fue exitosa |
| `cliente` | object | Información del cliente consultado |
| `periodo` | object | Fechas del filtro aplicado (puede ser null si no se enviaron) |
| `total` | integer | Cantidad total de llamadas retornadas |
| `llamadas` | array | Lista de llamadas con su análisis |

##### Objeto `llamada`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | integer | ID único de la llamada |
| `fecha_inicio` | ISO 8601 | Fecha y hora de inicio de la llamada |
| `fecha_fin` | ISO 8601 | Fecha y hora de fin (puede ser null en llamadas perdidas) |
| `tipo` | string | `saliente`, `entrante` o `perdida` |
| `duracion_segundos` | integer | Duración en segundos (0 para llamadas perdidas) |
| `estado` | string | Estado de procesamiento (ver tabla de estados) |
| `vendedor` | string | Nombre del vendedor que realizó/atendió la llamada |
| `transcript` | string | Texto completo de la transcripción (null si no fue procesada) |
| `resumen` | string | Resumen breve de la llamada (null si no fue procesada) |
| `analisis` | object | Análisis de IA (null si la llamada no fue analizada) |

##### Objeto `analisis`

| Campo | Tipo | Valores posibles | Descripción |
|---|---|---|---|
| `sentimiento_cliente` | string | `positivo`, `neutral`, `negativo` | Sentimiento general detectado en el cliente |
| `tono_general` | string | variable | Descripción del tono de la conversación |
| `intencion_comercial` | string | `alta`, `media`, `baja` | Nivel de interés comercial detectado |
| `score_venta` | integer | 0–100 | Probabilidad de venta estimada |
| `objeciones` | array\<string\> | — | Lista de objeciones detectadas en la llamada |
| `siguiente_accion` | string | — | Acción recomendada para el vendedor |
| `analizado_at` | ISO 8601 | — | Fecha y hora en que se procesó el análisis |

##### Escala de `score_venta`

| Rango | Interpretación |
|---|---|
| 0 – 30 | Baja probabilidad de venta |
| 31 – 60 | Probabilidad media |
| 61 – 80 | Alta probabilidad |
| 81 – 100 | Oportunidad caliente |

##### Estados de procesamiento

| Estado | Descripción |
|---|---|
| `registrada` | Llamada registrada, sin audio |
| `audio_pendiente` | Esperando que el vendedor adjunte el audio |
| `audio_subido` | Audio recibido, pendiente de procesamiento |
| `transcripcion_pendiente` | En cola para transcripción |
| `transcrita` | Transcripción lista, pendiente de análisis |
| `analisis_pendiente` | En cola para análisis de IA |
| `analizada` | Procesamiento completo |
| `error` | Error en el procesamiento |

---

#### Respuestas de error

##### 401 — No autorizado

```json
{
  "error": "Token de autenticación requerido."
}
```

```json
{
  "error": "Token inválido o revocado."
}
```

##### 404 — Cliente no encontrado

```json
{
  "error": "No se encontró ningún cliente con ese número.",
  "phone": "+51999999999"
}
```

##### 422 — Parámetros inválidos

```json
{
  "message": "Debe proporcionar phone o cliente_id.",
  "errors": { ... }
}
```

##### 429 — Rate limit excedido

Se retorna cuando se superan 60 solicitudes por minuto. Reintentar después de 1 minuto.

##### 500 — Error interno

Contactar al administrador del sistema.

---

## Ejemplos

### cURL — Consulta por número con rango de fechas

```bash
curl -X GET "http://<host>:8000/api/v1/calls?phone=%2B51912345678&fecha_desde=2026-04-01&fecha_hasta=2026-04-30" \
  -H "Authorization: Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Accept: application/json"
```

> El símbolo `+` en el número debe codificarse como `%2B` en la URL.

### cURL — Consulta por ID de cliente sin filtro de fecha

```bash
curl -X GET "http://<host>:8000/api/v1/calls?cliente_id=2" \
  -H "Authorization: Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Accept: application/json"
```

### JavaScript (fetch)

```javascript
const response = await fetch(
  'http://<host>:8000/api/v1/calls?phone=%2B51912345678&fecha_desde=2026-04-01',
  {
    headers: {
      'Authorization': 'Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
      'Accept': 'application/json',
    }
  }
);
const data = await response.json();
console.log(data.total, 'llamadas encontradas');
```

### Python (requests)

```python
import requests

response = requests.get(
    'http://<host>:8000/api/v1/calls',
    params={
        'phone': '+51912345678',
        'fecha_desde': '2026-04-01',
        'fecha_hasta': '2026-04-30',
    },
    headers={
        'Authorization': 'Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'Accept': 'application/json',
    }
)
data = response.json()
print(f"{data['total']} llamadas encontradas")
```

---

## Versionado

La API usa prefijo `/api/v1/`. Cambios que rompan compatibilidad incrementarán la versión (`v2`, etc.). Los cambios aditivos (nuevos campos opcionales) no cambian la versión.

---

## Soporte

Para solicitar tokens de acceso o reportar problemas, contactar al administrador del sistema.
