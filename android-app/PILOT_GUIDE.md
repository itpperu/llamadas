# Guía de Riesgos y Piloto (Fase de Endurecimiento MVP)

Esta guía documenta los riesgos operativos residuales, políticas adoptadas y recomendaciones de operación para lanzar la fase Piloto con un grupo reducido de vendedores (5 a 10 dispositivos).

## Endurecimiento Aplicado (Fase 6)
Durante los test de QA, se ajustaron las siguientes murallas de seguridad:
1. **Validaciones de Identidad Cruzada:** Hemos forzado a nivel Backend que ningún token válido pueda enviar audios o llamadas a nombre de otro Vendedor o de dispositivos ajenos.
2. **Idempotencia Estricta:** Si un asesor cruza un puente sin señal y el Payload de la llamada es enviado dos veces a Laravel, el Backend absorberá el segundo ignorándolo gracias al chequeo por Clave Compuesta (`dispositivo` + `fecha` + `numero`).
3. **Limpieza de Temporales:** Tras copiar un audio al multipart, el Android borra el caché temporal al instante para no dejar gigabytes ocultos en la memoria del celular. A su vez, Laravel sobre-escribe audios descartando (borrando del disco) los obsoletos para tu higiene de disco.
4. **Resistencia a Llamadas en 0s:** Las llamadas perdidas y rechazadas con duración `0` están legalmente habilitadas y fluyen hasta el panel web sin crashear.

## Riesgos y Limitaciones Abiertas
A pesar del endurecimiento, existen reglas funcionales puras del MVP que hay que tener presentes:

1. **Retención Indefinida Local (SQLite)**: El app no fue diseñada aún con un *Job* que purgue su base de datos. Acumulará las 5,000 llamadas del año hasta que limpies los datos de App o desarrollemos la Fase Posterior (Limpieza Remanente D+7).
2. **Asociación Asistida Falible**: Dado que el usuario escoge el `.mp3` de su carpeta, confiamos en qué elegirá el de la hora correspondiente. Si sube la canción *Despacito.mp3*, el Backend lo enviará a la IA de transcripción, perdiendo algo de centavos y devolviendo basura en el texto (riesgo asumido por falta de SDK de automatización Android 11+).
3. **Duración Excesiva de Archivos**: Si graban una llamada de 2 horas continuas, el timeout de OkHttp o Guzzle (PHP) podrían quebrar. Se sugieren llamadas de menos de 45 minutos.
4. **Eliminación del Audios Raíz**: Si el Vendedor borra la grabación telefónica de su grabadora Samsung *antes* de adjuntarlo en la APP, la APP arrojará una alerta y revertirá su estado a "Pendiente por asociar" infinitamente.

## Checklist Sugerido para Despliegue en Piloto Real
- [ ] Celulares limpios asignados al equipo (idealmente del MISMO modelo para acotar fallas del explorador OS).
- [ ] Vendedores notificados del uso del botón de asociación al fin del día o en sus "pausas activas".
- [ ] Credenciales individuales registradas y probadas en el servidor web previamente.
- [ ] Supervisión habilitada en Laravel (revisar que las transcripciones asíncronas de Python no se saturen si todos suben de golpe a las 5PM).
