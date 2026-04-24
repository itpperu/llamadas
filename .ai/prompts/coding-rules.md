# coding-rules.md

## Reglas generales
- Mantener código legible y orientado al MVP.
- Evitar complejidad innecesaria.
- Toda validación crítica debe existir en backend.
- Nombrar clases, servicios y archivos de manera clara.

## Laravel
- usar controladores delgados
- mover lógica de negocio a `Services` o `Actions`
- usar Form Requests para validación
- usar Jobs para tareas pesadas
- usar migraciones como fuente oficial del esquema
- preferir Eloquent para CRUD y Query Builder para reportes complejos
- mantener respuestas JSON consistentes en la API

## Android Kotlin
- separar UI, data y domain si aplica
- no meter lógica compleja en Activities/Fragments
- centralizar manejo de errores
- usar reintentos controlados
- registrar logs útiles para diagnóstico de sincronización

## Python IA
- separar contratos, servicios y utilitarios
- no mezclar procesamiento con demasiada lógica HTTP
- devolver resultados estructurados y predecibles

## Base de datos
- normalizar números telefónicos
- indexar filtros frecuentes
- no duplicar datos sin justificación
- dejar trazabilidad de estados y reprocesos

## Reportes
- priorizar filtros útiles
- evitar dashboards complejos en el primer corte
- mostrar primero la información más operativa

## Documentación
- cualquier cambio relevante en arquitectura o alcance debe reflejarse en `.ai/context/`
- si cambia el esquema, actualizar `.ai/docs/DB_SCHEMA.md`
- si cambia el contrato API, actualizar `.ai/docs/API.md`
