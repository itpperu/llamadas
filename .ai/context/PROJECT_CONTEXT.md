# PROJECT_CONTEXT.md

## Nombre del proyecto
Sistema de monitoreo y análisis de llamadas comerciales desde dispositivos móviles corporativos.

## Resumen ejecutivo
La empresa necesita una solución que permita centralizar y auditar las llamadas comerciales realizadas por sus vendedores desde celulares corporativos. El sistema debe capturar la metadata de cada llamada, asociar la grabación nativa del dispositivo, almacenar el audio en un backend Laravel, transcribir la conversación, aplicar análisis con IA y mostrar reportes web para supervisión y gestión comercial.

## Problema de negocio
Actualmente las llamadas comerciales ocurren en dispositivos móviles, pero la empresa no tiene una trazabilidad confiable sobre:
- qué vendedor habló con qué cliente
- cuándo ocurrió la llamada
- cuánto duró
- qué se dijo
- si el cliente estaba interesado, molesto o indeciso
- qué oportunidades comerciales quedaron abiertas
- qué desempeño tienen los vendedores en sus interacciones

Esto genera falta de control, poca visibilidad para supervisión, dificultad para mejorar el proceso comercial y pérdida de información valiosa para seguimiento.

## Objetivo principal
Construir un sistema que transforme una llamada móvil en un registro comercial trazable, consultable y analizable.

## Objetivos específicos
- Registrar llamadas entrantes, salientes y perdidas desde equipos Android corporativos.
- Asociar la grabación nativa del equipo a cada llamada.
- Subir metadata y audio a un backend centralizado.
- Transcribir el contenido de la llamada.
- Generar análisis de sentimiento, intención comercial y score de venta.
- Mostrar reportes en Laravel por vendedor, cliente y rango de fechas.
- Permitir reproceso cuando una llamada o análisis requiera corrección.

## Usuarios del sistema

### Vendedor
Usa el equipo corporativo para realizar o recibir llamadas. Su objetivo no es administrar el sistema, sino operar con la menor fricción posible.

### Supervisor
Revisa la actividad del equipo comercial. Necesita listar llamadas, ver transcripciones, escuchar audios y detectar alertas operativas o comerciales.

### Gerencia
Necesita métricas agregadas, tendencias, oportunidades, calidad de atención y señales de riesgo comercial.

### Administrador del sistema
Gestiona configuración, monitoreo técnico, reprocesos, catálogos y correcciones de operación.

## Alcance funcional del MVP
El MVP incluye:
- app Android corporativa
- backend Laravel
- almacenamiento de metadata y audios
- transcripción
- análisis básico de IA
- reportes web

## Fuera de alcance del MVP
Queda explícitamente fuera del alcance actual:
- WhatsApp
- SIP
- Twilio
- softphone
- central telefónica IP
- marcación automática
- CRM completo
- analítica omnicanal
- integraciones contables
- automatización de campañas

## Supuestos del proyecto
- Todos los celulares corporativos serán del mismo modelo.
- El dispositivo permite un flujo viable de grabación nativa.
- El número telefónico es la clave principal para identificar al cliente en el MVP.
- El backend principal será Laravel.
- El análisis de IA correrá como servicio desacoplado en Python.
- La empresa quiere minimizar costos operativos y evitar Twilio o SIP en esta fase.

## Resultado esperado
Una plataforma donde cada llamada comercial termine convertida en:
- un registro persistente
- un audio almacenado
- un transcript
- un análisis comercial
- una visualización clara en reportes
