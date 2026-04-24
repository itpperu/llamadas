# DECISIONS.md

## D-001
### Título
Laravel como backend principal

### Estado
Aceptada

### Motivo
El equipo ya domina PHP y Laravel. Además, los reportes web también deben desarrollarse en Laravel para mantener un stack homogéneo.

---

## D-002
### Título
Excluir WhatsApp del alcance actual

### Estado
Aceptada

### Motivo
El proyecto se enfocará exclusivamente en llamadas comerciales para reducir alcance y ejecutar más rápido el MVP.

---

## D-003
### Título
No usar SIP, Twilio ni softphone

### Estado
Aceptada

### Motivo
Se busca minimizar costos y evitar complejidad de telefonía IP en esta fase. Las llamadas ocurrirán con el teléfono normal del equipo corporativo.

---

## D-004
### Título
Usar grabación nativa del dispositivo

### Estado
Aceptada

### Motivo
La app Android no debe intentar grabar llamadas por su cuenta. El sistema se apoyará en el mecanismo nativo o permitido por el modelo del equipo corporativo.

---

## D-005
### Título
Todos los equipos serán del mismo modelo

### Estado
Aceptada

### Motivo
Esto reduce la variabilidad técnica y permite construir un flujo operativo más estable para asociación de llamadas y audios.

---

## D-006
### Título
Servicio IA en Python desacoplado

### Estado
Aceptada

### Motivo
Laravel orquesta, persiste y reporta; Python procesa transcripción y análisis. Esto reduce acoplamiento y facilita evolución futura del motor IA.

---

## D-007
### Título
Arquitectura de captura y sincronización

### Estado
Aceptada

### Motivo
La llamada no será controlada en tiempo real por la plataforma. El enfoque será detectar, registrar, sincronizar y procesar después del evento.

---

## D-008
### Título
MySQL como base relacional inicial

### Estado
Aceptada

### Motivo
Es suficiente para el MVP, bien soportada por Laravel y fácil de operar.

---

## D-009
### Título
Prioridad del MVP: captura antes que IA

### Estado
Aceptada

### Motivo
No tiene sentido sofisticar el análisis si la llamada y el audio no quedan bien registrados.

---

## D-010
### Título
Ejecución del Backend vía Docker Compose

### Estado
Aceptada

### Motivo
Estandarizar el entorno de desarrollo y futura producción en Windows usando contenedores (Nginx, PHP, MySQL, Worker), evitando configuraciones locales de XAMPP.

---

## D-011
### Título
Autenticación de API mediante Laravel Sanctum

### Estado
Aceptada

### Motivo
Protección estandarizada de endpoints API usando tokens estáticos (`Bearer`) que el dispositivo móvil conservará.
