# Manual de Usuario — Sistema de Monitoreo de Llamadas Comerciales

**Versión:** 1.1  
**Fecha:** 2026-04-25  
**URL del sistema:** https://llamadas.innovationtechnologyperu.com

---

## Índice

1. [Usuarios del sistema](#1-usuarios-del-sistema)
2. [Panel Web — Acceso y Login](#2-panel-web--acceso-y-login)
3. [Panel Web — Reportes de Llamadas](#3-panel-web--reportes-de-llamadas)
4. [Panel Web — Detalle de una Llamada](#4-panel-web--detalle-de-una-llamada)
5. [Panel Web — Exportar datos](#5-panel-web--exportar-datos)
6. [Panel Web — Resumen de Vendedores](#6-panel-web--resumen-de-vendedores)
7. [Panel Web — Gestión de Vendedores](#7-panel-web--gestión-de-vendedores)
8. [Panel Web — Configuración y Diagnóstico](#8-panel-web--configuración-y-diagnóstico)
9. [Aplicación Android — Instalación](#9-aplicación-android--instalación)
10. [Aplicación Android — Login y primera configuración](#10-aplicación-android--login)
11. [Aplicación Android — Log de Llamadas](#11-aplicación-android--log-de-llamadas)
12. [Aplicación Android — Configuración](#12-aplicación-android--configuración)
13. [Aplicación Android — Asociar Audio](#13-aplicación-android--asociar-audio)
14. [Preguntas frecuentes](#14-preguntas-frecuentes)

---

## 1. Usuarios del sistema

El sistema tiene cuatro tipos de usuarios:

| Rol | Quién es | Qué puede hacer |
|---|---|---|
| **Vendedor** | Persona que realiza o recibe llamadas desde el celular corporativo | Usa la app Android. El sistema registra sus llamadas automáticamente |
| **Supervisor** | Jefe de equipo comercial | Accede al panel web para revisar llamadas, transcripciones y análisis de su equipo |
| **Gerencia** | Directivos | Consultan métricas agregadas y tendencias comerciales en el panel web |
| **Administrador** | Sistemas / TI | Gestiona vendedores, dispositivos, tokens de API y diagnóstico del sistema |

---

## 2. Panel Web — Acceso y Login

### 2.1 Ingresar al sistema

Abrir el navegador y dirigirse a:

```
https://llamadas.innovationtechnologyperu.com
```

Aparecerá la pantalla de inicio de sesión. Ingresar:
- **Correo electrónico:** la dirección de correo del administrador
- **Contraseña:** la clave asignada

Marcar **"Recordar sesión"** si se usa desde un equipo de oficina fijo para no tener que ingresar las credenciales cada vez.

Hacer clic en **"Acceder al Panel"**.

---

### 2.2 Recuperar contraseña olvidada

Si no recuerdas tu contraseña:

1. En la pantalla de login, hacer clic en **"¿Olvidaste tu contraseña?"** (parte inferior)
2. Ingresar el correo electrónico registrado
3. Hacer clic en **"Enviar enlace de recuperación"**
4. Revisar la bandeja de entrada del correo (también revisar la carpeta de spam)
5. Abrir el correo recibido y hacer clic en el enlace
6. Ingresar la nueva contraseña (mínimo 8 caracteres, debe tener mayúsculas, minúsculas y números)
7. Confirmar la contraseña y hacer clic en **"Guardar nueva contraseña"**
8. Serás redirigido al login para ingresar con la nueva clave

> El enlace de recuperación expira en **60 minutos**. Si venció, repetir el proceso.

---

### 2.3 Cerrar sesión

En el menú lateral izquierdo, en la parte inferior, hacer clic en **"🚪 Cerrar sesión"**.

---

## 3. Panel Web — Reportes de Llamadas

Esta es la pantalla principal del sistema. Muestra todas las llamadas registradas por los vendedores.

### 3.1 Acceder

Hacer clic en **"📊 Reportes"** en el menú lateral izquierdo.

### 3.2 Filtrar llamadas

En la parte superior hay un panel de filtros. Se pueden combinar varios filtros al mismo tiempo:

| Filtro | Para qué sirve |
|---|---|
| **Vendedor** | Ver solo las llamadas de un vendedor específico |
| **Cliente** | Ver todas las llamadas hacia/desde un número de teléfono |
| **Desde** | Fecha de inicio del período a consultar |
| **Hasta** | Fecha de fin del período a consultar |
| **Estado** | Filtrar por estado de procesamiento de la llamada |

Después de seleccionar los filtros, hacer clic en **"🔍 Filtrar"**.

Para quitar todos los filtros, hacer clic en **"Limpiar"**.

### 3.3 Interpretar la tabla de llamadas

La tabla muestra una fila por cada llamada con la siguiente información:

| Columna | Descripción |
|---|---|
| **Fecha/Hora** | Cuándo ocurrió la llamada |
| **Vendedor** | Quién realizó o recibió la llamada |
| **Cliente** | Número de teléfono y nombre referencial |
| **Tipo** | `saliente` (el vendedor llamó), `entrante` (el cliente llamó), `perdida` (no se contestó) |
| **Duración** | Cuánto duró la llamada en formato HH:MM:SS |
| **Estado** | Estado actual del procesamiento |
| **Análisis** | Ícono de sentimiento y score de venta (si fue analizada) |

### 3.4 Estados de procesamiento

| Estado | Significado |
|---|---|
| **Registrada** | La llamada fue detectada por la app. Aún sin audio |
| **Audio Pendiente** | Esperando que el vendedor adjunte el archivo de grabación |
| **Audio Subido** | El audio llegó al servidor. En espera de transcripción |
| **Transcrita** | El audio fue transcrito. En espera de análisis |
| **Analizada** | El proceso completo terminó. Hay transcript y análisis disponibles |
| **Error** | Ocurrió un problema en algún paso del procesamiento |

### 3.5 Buscar dentro de la tabla

La tabla tiene un buscador propio (arriba a la derecha de la tabla). Escribir cualquier dato para filtrar en tiempo real: número de teléfono, nombre de vendedor, estado, etc.

### 3.6 Ordenar columnas

Hacer clic en el encabezado de cualquier columna para ordenar los resultados de forma ascendente o descendente.

---

## 4. Panel Web — Detalle de una Llamada

Para ver toda la información de una llamada, hacer clic en el botón **"Ver"** en la fila correspondiente.

### 4.1 Sección: Datos de la llamada

Muestra la información básica: vendedor, cliente, tipo, fecha, duración y estado actual.

### 4.2 Sección: Reproducir audio

Si el audio fue subido exitosamente, aparece un reproductor de audio directamente en la página. Hacer clic en el botón de play para escuchar la grabación sin necesidad de descargarla.

### 4.3 Sección: Transcripción

Muestra el texto completo de la conversación generado automáticamente por el sistema de inteligencia artificial. La transcripción identifica los turnos de la conversación.

> Si la transcripción tiene errores, puede solicitarse un reprocesamiento (ver sección 4.5).

### 4.4 Sección: Análisis comercial

El sistema analiza automáticamente la conversación y produce:

| Campo | Descripción |
|---|---|
| **Sentimiento del cliente** | `positivo`, `neutral` o `negativo` según el tono detectado |
| **Tono general** | Descripción del ambiente de la conversación |
| **Intención comercial** | Nivel de interés del cliente: `alta`, `media` o `baja` |
| **Score de venta** | Número del 0 al 100 que estima la probabilidad de cierre |
| **Objeciones detectadas** | Objeciones que el cliente mencionó durante la llamada |
| **Siguiente acción recomendada** | Sugerencia de qué hacer después de esta llamada |

**Escala del score de venta:**

| Rango | Interpretación |
|---|---|
| 0 – 30 | Baja probabilidad de venta |
| 31 – 60 | Probabilidad media |
| 61 – 80 | Alta probabilidad |
| 81 – 100 | Oportunidad caliente — requiere seguimiento inmediato |

### 4.5 Reprocesar una llamada

Si la transcripción o el análisis tienen errores, se puede solicitar que el sistema lo procese nuevamente:

1. En la vista de detalle, hacer clic en el botón **"Reprocesar análisis IA"**
2. El sistema encola la llamada para ser reprocesada
3. En unos minutos, refrescar la página para ver los nuevos resultados

> El reproceso reemplaza el transcript y análisis anterior. Solo usuarios autorizados pueden realizarlo.

---

## 5. Panel Web — Exportar datos

### 5.1 Exportar tabla a Excel

En la pantalla de Reportes, encima de la tabla hay un botón **"📥 Exportar Excel"**. Al hacer clic descarga un archivo `.xlsx` con todas las filas visibles en ese momento (respetando los filtros aplicados).

### 5.2 Exportar paquete ZIP por número o fecha

Esta opción descarga un paquete completo con metadata, transcripciones, análisis **y archivos de audio** de un cliente o período.

Debajo del panel de filtros hay una sección **"📦 Exportar Paquete ZIP"**:

1. Seleccionar el **número / cliente** del desplegable (opcional)
2. Seleccionar **Desde** y/o **Hasta** (opcional)
3. Hacer clic en **"📦 Descargar ZIP"**

> Se requiere al menos un número de cliente **o** una fecha de inicio. No se puede exportar sin ningún filtro.

El ZIP descargado contiene:
- `reporte.csv` — tabla completa con todos los campos incluyendo transcript y análisis
- `audios/` — carpeta con los archivos de audio de las llamadas que tengan grabación

---

## 6. Panel Web — Resumen de Vendedores

Hacer clic en **"📈 Resumen Vendedores"** en el menú lateral.

Esta vista muestra métricas agregadas por vendedor:

| Métrica | Descripción |
|---|---|
| **Total llamadas** | Número total de llamadas registradas |
| **Salientes / Entrantes / Perdidas** | Desglose por tipo de llamada |
| **Duración promedio** | Tiempo promedio de conversación |
| **Score promedio** | Promedio del score de venta de las llamadas analizadas |
| **Sentimiento** | Cantidad de llamadas con sentimiento positivo, neutral y negativo |
| **Última llamada** | Cuándo fue la última actividad registrada |

También permite exportar este resumen a Excel con el botón **"📥 Exportar Excel"**.

---

## 7. Panel Web — Gestión de Vendedores

Hacer clic en **"👥 Vendedores"** en el menú lateral.

### 7.1 Ver listado de vendedores

Muestra todos los vendedores registrados con su nombre, teléfono corporativo, dispositivo asociado y estado.

### 7.2 Crear un nuevo vendedor

El proceso correcto es el siguiente porque el UUID del celular solo se conoce después de instalar la app:

**Paso 1 — Crear el vendedor sin UUID**

1. Hacer clic en **"+ Nuevo Vendedor"**
2. Completar el formulario:
   - **Nombre completo** del vendedor
   - **Usuario** con el que iniciará sesión en la app Android
   - **Contraseña** inicial para la app Android
   - **Teléfono corporativo** (número del celular asignado)
   - **UUID del dispositivo** — dejar en blanco por ahora
3. Hacer clic en **"Dar de Alta Vendedor"**

**Paso 2 — Obtener el UUID del celular del vendedor**

1. Instalar la app en el celular corporativo del vendedor (ver sección 9)
2. El vendedor abre la app y toca **"Ver UUID"** en la pantalla de login
3. El vendedor toca **"Copiar"** y envía el código al administrador

**Paso 3 — Registrar el UUID en el sistema**

1. En el panel web, ir a **👥 Vendedores**
2. Buscar al vendedor recién creado y hacer clic en **"Editar"**
3. Pegar el UUID en el campo **"UUID del Dispositivo Corporativo"**
4. Hacer clic en **"Guardar Cambios"**

A partir de este momento el vendedor puede iniciar sesión en la app normalmente.

### 7.3 Editar un vendedor

1. En el listado, hacer clic en **"Editar"** en la fila del vendedor
2. Modificar los datos necesarios
3. Hacer clic en **"Actualizar"**

---

## 8. Panel Web — Configuración y Diagnóstico

Hacer clic en **"🎯 Configuración"** en el menú lateral.

### 8.1 Estado de servicios

Muestra en tiempo real si los componentes del sistema están funcionando:

- **Base de Datos (MySQL):** debe mostrar `OPERATIVA`
- **IA (Microservicio Python):** debe mostrar `EN LÍNEA`
- **Worker de Tareas:** muestra el modo de cola activo

Si algún servicio aparece en rojo, contactar al administrador de sistemas.

### 8.2 Monitoreo de cola

Muestra cuántos trabajos de análisis están pendientes y si hay errores acumulados. En condiciones normales debe mostrar `🟢 Cola saludable`.

### 8.3 Tokens de API Externa

Esta sección permite a sistemas externos (CRM, BI, dashboards) consultar datos del sistema mediante una API segura.

**Crear un token:**
1. Escribir un nombre descriptivo (ej: "Integración CRM", "Dashboard Power BI")
2. Opcionalmente, definir una fecha de vencimiento
3. Hacer clic en **"+ Generar Token"**
4. **Copiar el token inmediatamente** — solo se muestra una vez

**Revocar un token:**
Hacer clic en **"Revocar"** en la fila del token. Los sistemas que lo usen perderán acceso de inmediato.

> Para más información sobre cómo usar la API externa, ver el archivo `docs/API_EXTERNA.md`.

---

## 9. Aplicación Android — Instalación

### 9.1 Requisitos del celular

- Android 8.0 o superior
- Conexión a internet (WiFi o datos móviles)
- Grabación nativa de llamadas activada (ver paso 9.2 — obligatorio)

---

### 9.2 Instalar la app de grabación de llamadas — Call Up ⚠️ OBLIGATORIO

El sistema requiere una aplicación de grabación de llamadas activa en el celular. La app recomendada y probada es **Call Up**.

#### Instalación de Call Up

1. Abrir la **Play Store** en el celular
2. Buscar **"Call Up — Call Recorder"**
3. Instalar y abrir la app
4. Aceptar todos los permisos que solicite (micrófono, teléfono, almacenamiento)
5. En la configuración de Call Up, verificar:
   - **Grabación automática:** activada para todas las llamadas
   - **Formato de audio:** MP3 (recomendado)
   - **Carpeta de guardado:** la app guarda en `Almacenamiento interno → Music → CallAppRecording`

> **Límite de almacenamiento de Call Up:** la app puede almacenar hasta 200MB de grabaciones localmente. Este límite no es un problema en la práctica porque el sistema elimina automáticamente el archivo de grabación del celular una vez que es subido exitosamente al servidor.

> **Call Up funciona en segundo plano.** No es necesario tenerla abierta durante las llamadas — graba automáticamente.

---

### 9.3 Activar la grabación nativa de llamadas ⚠️ OBLIGATORIO

> **Este paso es el más importante.** Sin grabación activa en el dispositivo, el sistema no tendrá audio que procesar y el análisis de IA no funcionará.

La app **no graba llamadas por sí sola** — utiliza la grabación nativa del dispositivo. Debe activarse una sola vez en cada celular corporativo.

#### En Xiaomi / MIUI (celulares corporativos del equipo)

1. Abrir la app **Teléfono** (el marcador nativo del celular)
2. Tocar los **tres puntos ⋮** en la esquina superior derecha → **Configuración**
3. Buscar la opción **"Grabación de llamadas"**
4. Activar **"Grabar llamadas automáticamente"**
5. En la opción de alcance, seleccionar **"Todas las llamadas"**
6. Confirmar si el sistema lo solicita

A partir de ese momento, cada llamada realizada o recibida quedará grabada automáticamente.

#### Dónde se guardan las grabaciones en Xiaomi

Las grabaciones quedan en la memoria interna del celular, en la siguiente ruta:

```
Almacenamiento interno → MIUI → sound_recorder → call_rec
```

El vendedor debe navegar a esa carpeta al momento de **Asociar Audio** para seleccionar el archivo correcto.

#### En otros modelos de Android

Si los celulares corporativos no son Xiaomi, el procedimiento varía:

| Marca | Ruta típica |
|---|---|
| Samsung | Teléfono → ⋮ → Configuración → Grabación de llamadas |
| Huawei | Teléfono → ⋮ → Configuración → Grabar llamadas |
| Motorola | Teléfono → ⋮ → Configuración → Grabación de llamadas |
| Nokia | Teléfono → ⋮ → Ajustes → Grabación de llamadas |

> Si el dispositivo no tiene opción de grabación nativa, contactar al administrador de sistemas. Puede ser necesario habilitar una opción de desarrollador o usar un modelo diferente de celular.

---

### 9.4 Instalar la aplicación de monitoreo

1. Recibir el archivo `app-debug.apk` del administrador de sistemas (por correo, WhatsApp o USB)
2. En el celular, ir a **Ajustes → Seguridad** (o **Ajustes → Aplicaciones**)
3. Activar **"Instalar aplicaciones de fuentes desconocidas"** o **"Permitir de esta fuente"**
4. Abrir el archivo `.apk` desde el administrador de archivos del celular
5. Tocar **"Instalar"** y esperar que termine
6. Tocar **"Abrir"** para iniciar la aplicación

### 9.5 Permisos requeridos

Al abrir la app por primera vez, solicitará los siguientes permisos. Es obligatorio aceptarlos todos:

| Permiso | Por qué se necesita |
|---|---|
| **Teléfono / Estado del teléfono** | Para detectar cuando una llamada termina |
| **Registros de llamadas** | Para leer los datos de la llamada (número, duración, tipo) |

---

## 10. Aplicación Android — Login

### 10.1 Configurar el servidor (primera vez)

Antes de iniciar sesión por primera vez, verificar que la app apunta al servidor correcto:

1. En la pantalla de login, tocar **"Cambiar servidor"** (parte inferior)
2. Escribir la URL del servidor: `https://llamadas.innovationtechnologyperu.com/api/`
3. Tocar **"Guardar"** — la pantalla se recarga automáticamente
4. Verificar que el texto inferior muestre la URL correcta

> Solo se hace una vez. La URL queda guardada en el celular.

---

### 10.2 Obtener el UUID del dispositivo (antes de crear el usuario)

El UUID es el identificador único del celular. El administrador lo necesita para registrar el dispositivo en el sistema **antes** de que el vendedor pueda iniciar sesión.

**Pasos para obtener y compartir el UUID:**

1. Abrir la app en el celular
2. En la pantalla de login, tocar **"Ver UUID"** (parte inferior)
3. Aparece un popup con el código único del dispositivo, por ejemplo: `c874364840e0f7d1`
4. Tocar **"Copiar"** para copiarlo al portapapeles
5. Enviar ese código al administrador por WhatsApp, correo o mensaje

El administrador usa ese código al crear o editar el vendedor en el panel web.

---

### 10.3 Iniciar sesión

Una vez que el administrador haya registrado el usuario con el UUID del dispositivo:

1. Abrir la aplicación en el celular
2. Ingresar el **usuario** asignado por el administrador
3. Ingresar la **contraseña**
4. Tocar **"Iniciar Sesión"**

Una vez dentro, el sistema comienza a detectar llamadas automáticamente en segundo plano. No es necesario mantener la app abierta.

> Si aparece el error **"Dispositivo no autorizado"**, significa que el UUID aún no fue registrado en el panel. Compartir el UUID con el administrador siguiendo los pasos de la sección 10.2.

---

## 11. Aplicación Android — Log de Llamadas

La pestaña **"📋 Log de Llamadas"** muestra el historial de llamadas registradas localmente en el celular.

### 11.1 Información de cada llamada

Cada fila del log muestra:

| Dato | Descripción |
|---|---|
| **Ícono** | 📤 Saliente / 📥 Entrante / 📵 Perdida |
| **Número** | Teléfono del cliente |
| **Tipo** | ↑ Saliente, ↓ Entrante o ✗ Perdida |
| **Fecha** | Día en que ocurrió la llamada |
| **Estado** | Estado de sincronización con el servidor |

### 11.2 Estados de sincronización

| Estado | Significado |
|---|---|
| **✓ OK** | La llamada y el audio se subieron correctamente al servidor |
| **Audio pendiente** | La llamada se registró pero falta adjuntar y subir el audio |
| **Pendiente** | La llamada aún no se sincronizó con el servidor (sin conexión) |

### 11.3 Eliminar el log

El botón rojo **"Eliminar Log"** en la parte inferior borra todos los registros locales del celular. Esto **no afecta los datos en el servidor** — solo limpia el historial local del dispositivo.

Antes de eliminar aparece una confirmación. Tocar **"Eliminar"** para confirmar o **"Cancelar"** para volver.

> Se recomienda eliminar el log solo cuando todas las llamadas muestren estado **✓ OK**.

---

## 12. Aplicación Android — Configuración

La pestaña **"⚙️ Configuración"** permite ajustar la URL del servidor y ver información de la sesión activa.

### 12.1 Cambiar la URL del servidor

Si la dirección del servidor cambia (por ejemplo al activar HTTPS), actualizar aquí:

1. Borrar la URL actual en el campo **"URL del servidor"**
2. Escribir la nueva URL. Debe terminar en `/api/`
   - Ejemplo: `http://llamadas.innovationtechnologyperu.com/api/`
   - Ejemplo con HTTPS: `https://llamadas.innovationtechnologyperu.com/api/`
3. Tocar **"Guardar URL"**

Aparecerá un mensaje **"URL guardada correctamente"**. La próxima sincronización usará la nueva dirección.

### 12.2 Información de sesión

Muestra el ID del vendedor y el UUID del dispositivo actualmente autenticados. Esta información es útil para reportar problemas al administrador.

### 12.3 Cerrar sesión

Tocar el botón **"Salir"** en la esquina superior derecha de la pantalla para cerrar la sesión. Será redirigido a la pantalla de login.

---

## 13. Aplicación Android — Asociar Audio

Cuando una llamada se registra pero no tiene grabación adjunta, el estado aparece como **"Audio pendiente"** tanto en el log de la app como en el panel web.

### 13.1 ¿Por qué puede faltar el audio?

- El dispositivo guarda las grabaciones en una carpeta que requiere selección manual
- La llamada fue muy corta y no se generó archivo de grabación
- El dispositivo no tiene habilitada la grabación automática de llamadas

### 13.2 Adjuntar el audio manualmente

1. En la pestaña **📋 Log de Llamadas**, tocar el botón **"🎙️ Asociar Audio"**
2. La app identifica la próxima llamada que necesita audio y muestra el número de teléfono
3. Se abre el selector de archivos del celular
4. Navegar hasta la carpeta donde están las grabaciones:
   - **Xiaomi Redmi 14 Pro (HyperOS):** `Almacenamiento interno → Download → Grabaciones`
   - **Xiaomi/MIUI clásico:** `Almacenamiento interno → MIUI → sound_recorder → call_rec`
   - **Samsung:** `Almacenamiento interno → Grabaciones de llamadas`
   - **Otros:** `Almacenamiento interno → Grabaciones` o `Llamadas grabadas`
5. Seleccionar el archivo de audio que corresponde a esa llamada (verificar por hora y duración)
6. La app sube el audio automáticamente al servidor
7. La app sube el audio automáticamente al servidor

> Si no hay internet en ese momento, la subida se reintenta automáticamente cuando haya conexión.

---

## 14. Preguntas frecuentes

**¿Las llamadas se graban automáticamente?**
El sistema utiliza la grabación nativa del dispositivo Android — no graba por cuenta propia. Para que funcione, la grabación automática de llamadas debe estar activada en la app Teléfono del celular (ver sección 9.2). Sin eso, no habrá archivo de audio y el análisis de IA no funcionará. Es el paso más importante antes de usar el sistema.

**¿Dónde se guardan las grabaciones en Xiaomi?**
Depende del modelo:
- **Redmi 14 Pro (HyperOS):** `Almacenamiento interno → Download → Grabaciones`
- **MIUI clásico:** `Almacenamiento interno → MIUI → sound_recorder → call_rec`

**¿Qué pasa si el celular no tiene internet durante una llamada?**
La app guarda la información de la llamada localmente y la sincroniza automáticamente cuando recupera la conexión. No se pierde ningún registro.

**¿Por qué una llamada tiene estado "Error"?**
Puede ocurrir si el archivo de audio estaba corrupto, si la transcripción falló, o si el servicio de IA no estaba disponible en ese momento. El administrador puede solicitar un reproceso desde el panel web.

**¿Cuánto tiempo tarda en aparecer el análisis de una llamada?**
Generalmente entre 1 y 5 minutos después de subir el audio, dependiendo de la duración de la llamada y la carga del servidor.

**¿Se puede escuchar el audio desde el panel web?**
Sí. En el detalle de cada llamada hay un reproductor integrado. No es necesario descargar el archivo.

**¿Qué significa que el score de venta sea 0?**
Puede significar que la llamada fue muy corta, fue una llamada perdida, o que el análisis no detectó señales de interés comercial. No indica necesariamente un problema técnico.

**¿Por qué sale "Dispositivo no autorizado" al iniciar sesión?**
El UUID del celular no está registrado en el sistema. El vendedor debe tocar **"Ver UUID"** en la pantalla de login, copiar el código y enviárselo al administrador. El administrador lo registra editando el vendedor en el panel web.

**¿Cómo se crea un usuario nuevo para el panel web?**
Solo el administrador del sistema puede crear usuarios web. Debe contactar al área de sistemas.

**El panel web muestra "IA desconectada" en Configuración. ¿Qué hago?**
Contactar al administrador de sistemas. El servicio de inteligencia artificial puede haber caído y necesita reiniciarse en el servidor.
