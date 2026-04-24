# AGENTS.md

## Propósito
Este repositorio usa contexto persistente para que cualquier LLM o agente técnico pueda trabajar con continuidad en el proyecto sin depender de una sola sesión.

## Proyecto
Sistema de monitoreo y análisis de llamadas comerciales desde dispositivos móviles corporativos.

## Objetivo operativo
Construir una plataforma que permita:
- detectar llamadas realizadas o recibidas desde celulares corporativos Android del mismo modelo
- registrar metadata de llamadas
- asociar grabaciones nativas del dispositivo
- subir audios al backend Laravel
- transcribir llamadas
- analizar sentimiento, intención comercial y probabilidad de venta
- mostrar reportes web en PHP/Laravel para supervisión y gestión

## Orden obligatorio de lectura
Todo agente debe leer en este orden antes de proponer cambios:
1. `.ai/context/PROJECT_CONTEXT.md`
2. `.ai/context/ARCHITECTURE.md`
3. `.ai/context/BUSINESS_RULES.md`
4. `.ai/context/TECH_STACK.md`
5. `.ai/docs/DB_SCHEMA.md`
6. `.ai/docs/API.md`
7. `.ai/docs/FLOWS.md`
8. `.ai/context/CURRENT_SPRINT.md`
9. `.ai/context/KNOWN_ISSUES.md`
10. `.ai/context/DECISIONS.md`
11. `.ai/context/SESSION_HANDOFF.md`

## Reglas globales
- No proponer SIP, Twilio o softphone como base del MVP.
- No ampliar el alcance a WhatsApp en esta fase.
- Toda llamada se modela como una interacción comercial.
- La app Android no debe intentar grabar audio por su cuenta; debe aprovechar la grabación nativa del equipo.
- El backend principal debe ser Laravel.
- Los reportes deben estar implementados en Laravel/PHP.
- La transcripción y análisis IA pueden correr como servicio Python desacoplado.
- Todo cambio relevante de arquitectura debe registrarse en `DECISIONS.md`.
- Todo cambio de alcance o prioridad debe registrarse en `TASKS.md` y `CURRENT_SPRINT.md`.
- No asumir reglas no documentadas.
- No cambiar stack ni estructura sin justificación explícita.

## Cómo deben trabajar los agentes
### fullstack-agent
Se enfoca en app Android, backend Laravel, frontend Blade, flujos y experiencia completa.

### db-agent
Diseña tablas, índices, relaciones, migraciones, políticas de retención y consultas de reportes.

### integration-agent
Se enfoca en sincronización Android → API Laravel, subida de audios, colas, workers y servicios IA.

### devops-agent
Se enfoca en despliegue, almacenamiento, seguridad, backups, colas, monitoreo y operación.

## Política de cambios
Antes de implementar:
- revisar contexto
- verificar si la decisión ya existe
- proponer cambio mínimo viable
- indicar impacto técnico
- registrar decisión si cambia comportamiento base

Después de implementar:
- actualizar documentación afectada
- registrar supuestos
- registrar pendientes
- dejar handoff claro

## Definición de listo
Una tarea se considera lista cuando:
- funciona en el flujo definido
- respeta arquitectura y reglas del negocio
- tiene documentación actualizada
- incluye validaciones y manejo de errores
- deja trazabilidad para el siguiente agente
