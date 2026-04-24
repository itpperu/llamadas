# ARCHITECTURE.md

## Principio arquitectónico
La solución del MVP usa una arquitectura de **captura y sincronización**, no de control directo de la llamada.

Eso significa que:
- la llamada ocurre usando el teléfono normal del equipo corporativo
- Android registra la llamada
- la app corporativa detecta el evento
- la app intenta ubicar y subir la grabación nativa
- el backend centraliza, procesa y reporta

## Vista general

```text
[android-app]
Celular corporativo Android
    ├─ detección de llamadas
    ├─ lectura de call log
    ├─ asociación de grabación nativa
    ├─ cola local y reintentos
    └─ subida al backend

        ↓ REST API autenticada

[backend]
Laravel
    ├─ autenticación del dispositivo/vendedor
    ├─ registro de llamadas
    ├─ carga de audios
    ├─ almacenamiento de archivos
    ├─ jobs y colas
    ├─ panel web y reportes
    └─ orquestación del servicio IA

        ↓ HTTP interno / worker bridge

[services/ai-worker]
Python
    ├─ transcripción
    ├─ limpieza de texto
    ├─ análisis de sentimiento
    ├─ intención comercial
    ├─ score de venta
    └─ salida estructurada a Laravel
```

## Componentes

### 1. Android app
Responsabilidades:
- autenticar al vendedor y/o dispositivo
- detectar nuevas llamadas
- leer el call log
- normalizar número telefónico
- asociar llamada con grabación nativa
- subir metadata
- subir audio
- manejar reintentos si no hay internet
- registrar eventos para soporte y auditoría

No debe:
- actuar como backend
- almacenar lógica de negocio compleja
- grabar llamadas por cuenta propia a bajo nivel
- asumir acceso universal a cualquier carpeta del sistema sin validación por modelo

### 2. Backend Laravel
Responsabilidades:
- exponer API móvil
- recibir metadata y audios
- almacenar y versionar estados
- crear o vincular cliente por número
- despachar jobs
- invocar el servicio IA
- persistir transcript y análisis
- mostrar reportes y detalles de llamada
- permitir reproceso

No debe:
- convertirse en motor de STT
- mezclar lógica de presentación con lógica de dominio
- depender de paquetes innecesarios fuera del MVP

### 3. Base de datos MySQL
Responsabilidades:
- persistir vendedores, dispositivos, clientes, llamadas, audios y análisis
- soportar reportes rápidos por fecha, vendedor y cliente
- mantener estados de procesamiento
- facilitar trazabilidad histórica

### 4. Servicio IA Python
Responsabilidades:
- recibir audio o referencia al audio
- generar transcript
- producir análisis estructurado
- devolver resultado consistente y versionado
- tolerar reintentos y errores controlados

### 5. Panel web Laravel
Responsabilidades:
- listar llamadas
- filtrar por vendedor, cliente, fecha y estado
- mostrar detalle
- reproducir audio
- mostrar transcript
- mostrar análisis comercial
- ofrecer reproceso si aplica por permisos

## Flujo principal extremo a extremo
1. El vendedor realiza o recibe una llamada.
2. Android registra la llamada en call log.
3. La app detecta el evento.
4. La app normaliza número y arma payload.
5. La app registra la llamada en Laravel.
6. La app intenta ubicar la grabación nativa.
7. Si la encuentra, la sube; si no, habilita flujo asistido.
8. Laravel persiste llamada y audio.
9. Laravel encola un job de transcripción.
10. El servicio Python procesa el audio.
11. Python devuelve transcript y análisis.
12. Laravel actualiza estados y resultados.
13. Supervisor o gerencia consultan el panel.

## Estados de procesamiento sugeridos
- registrada
- audio_pendiente
- audio_subido
- transcripcion_pendiente
- transcrita
- analisis_pendiente
- analizada
- error

## Principios no negociables
- simplicidad del MVP
- trazabilidad completa
- desacoplamiento entre captura, persistencia y análisis
- soporte a reproceso
- evitar dependencia de telefonía IP en esta fase
- evitar sobreingeniería

## Riesgos técnicos principales
- acceso irregular al archivo de grabación
- diferencias entre timestamp de llamada y archivo
- fallos de conectividad
- archivos corruptos o incompletos
- tiempos altos de procesamiento
- errores de asociación llamada ↔ grabación

## Mitigaciones
- usar un solo modelo de equipo
- construir una app de diagnóstico del dispositivo
- registrar hashes y metadata del audio
- usar cola local en Android
- usar colas y jobs en Laravel
- permitir corrección y reproceso manual
