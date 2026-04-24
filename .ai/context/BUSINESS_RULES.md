# BUSINESS_RULES.md

## Regla 1: la llamada es la unidad principal
Cada llamada representa una interacción comercial independiente dentro del sistema.

## Regla 2: identificación del cliente
El cliente se identifica principalmente por el número telefónico normalizado.

## Regla 3: normalización
Todo número debe pasar por un proceso de normalización antes de:
- buscar cliente
- crear cliente
- registrar llamada
- mostrar reportes agregados

## Regla 4: tipos de llamada
El sistema reconoce tres tipos base:
- entrante
- saliente
- perdida

## Regla 5: grabación
El sistema no graba llamadas de forma nativa por software propio. Debe apoyarse en la grabación nativa del dispositivo o en el flujo permitido por el modelo corporativo definido.

## Regla 6: asociación llamada-audio
La asociación entre llamada y audio debe validarse usando una combinación de:
- número telefónico
- fecha/hora aproximada
- duración
- vendedor autenticado
- reglas de tolerancia temporal
- confirmación manual si aplica

## Regla 7: estados de procesamiento
Toda llamada debe tener un estado claro. No se permiten llamadas “sin estado”.
Estados esperados:
- registrada
- audio_pendiente
- audio_subido
- transcripcion_pendiente
- transcrita
- analisis_pendiente
- analizada
- error

## Regla 8: transcript
El transcript debe almacenarse como texto persistente y debe poder reprocesarse.

## Regla 9: análisis comercial mínimo
Cada llamada analizada debe intentar producir:
- sentimiento_cliente
- tono_general
- intencion_comercial
- score_venta
- objeciones_detectadas
- siguiente_accion_recomendada

## Regla 10: score de venta
Escala sugerida:
- 0 a 30: baja probabilidad
- 31 a 60: media probabilidad
- 61 a 80: alta probabilidad
- 81 a 100: oportunidad caliente

## Regla 11: trazabilidad
Toda acción relevante debe poder auditarse:
- quién registró o subió
- desde qué dispositivo
- cuándo se procesó
- qué versión del servicio IA intervino
- si hubo reproceso

## Regla 12: permisos
- El vendedor puede operar sobre sus propias llamadas si así se configura.
- El supervisor puede revisar el equipo a su cargo.
- Gerencia puede ver información agregada.
- El reproceso debe quedar restringido a usuarios autorizados.

## Regla 13: prioridad del MVP
El orden de importancia es:
1. captura confiable
2. persistencia confiable
3. consulta y reporte
4. análisis IA

No debe sacrificarse la captura por intentar automatización avanzada prematura.

## Regla 14: corrección operativa
Debe existir un flujo para:
- reprocesar transcripción
- reprocesar análisis
- reasociar audio si se detectó error
- marcar una llamada como inválida si corresponde

## Regla 15: foco comercial
El análisis no debe limitarse a “positivo o negativo”. Debe orientarse a decisiones comerciales:
- interés
- objeciones
- cierre potencial
- seguimiento sugerido
