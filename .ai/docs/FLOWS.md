# FLOWS.md

## Flujo 1: registro de llamada
1. El vendedor realiza o recibe una llamada en el equipo corporativo.
2. Android registra la llamada en call log.
3. La app detecta la llamada.
4. La app normaliza el número.
5. La app envía metadata a Laravel.
6. Laravel crea o vincula cliente.
7. Laravel registra la llamada con estado inicial.

## Flujo 2: asociación y carga de audio
1. La app espera disponibilidad de la grabación nativa.
2. La app intenta ubicar el archivo.
3. Si lo encuentra, inicia la subida.
4. Si no lo encuentra, activa flujo asistido.
5. Laravel valida tamaño, tipo y hash.
6. Laravel almacena el archivo.
7. Laravel actualiza estado a `audio_subido`.

## Flujo 3: transcripción
1. Laravel despacha job.
2. Job envía audio o referencia al servicio Python.
3. Python procesa y devuelve transcript.
4. Laravel guarda el transcript y cambia estado.

## Flujo 4: análisis comercial
1. Laravel solicita análisis al servicio Python.
2. Python devuelve sentimiento, intención, score y objeciones.
3. Laravel guarda el análisis.
4. La llamada pasa a estado `analizada`.

## Flujo 5: reportes
1. Supervisor entra al panel web.
2. Filtra por vendedor, cliente o fecha.
3. Laravel consulta llamadas y análisis.
4. Se muestra listado.
5. En el detalle se muestra:
   - metadata
   - audio
   - transcript
   - análisis comercial

## Flujo 6: reproceso
1. Usuario autorizado solicita reproceso.
2. Laravel reencola job.
3. Python vuelve a procesar.
4. Laravel reemplaza o versiona el resultado según la política definida.
