# OPERATIONS.md — Procedimientos de Soporte y Operación

## 1. Arquitectura de Servicios

```
[Android App] → [Nginx :8000] → [Laravel App] → [MySQL :3306]
                                       ↓
                              [Laravel Worker] → [Python-AI :8001]
```

### Contenedores Docker
| Contenedor | Imagen | Función | Puerto |
|:-----------|:-------|:--------|:-------|
| `laravel_app` | PHP 8.2 + Laravel | API y Panel Web | interno |
| `laravel_worker` | PHP 8.2 + Laravel | Procesamiento de colas | interno |
| `laravel_nginx` | nginx:alpine | Reverse proxy HTTP | 8000 |
| `laravel_mysql` | mysql:8.0 | Base de datos | 33061 (host) |

### Servicio externo
| Servicio | Tecnología | Puerto | Ubicación |
|:---------|:-----------|:-------|:----------|
| Python-AI | FastAPI + Whisper | 8001 | `services/python-ai/` (host Windows) |

---

## 2. Comandos de Operación Diaria

### Levantar el sistema
```bash
# Backend Laravel (Docker)
cd backend
docker-compose up -d

# Servicio Python-AI (host Windows)
cd services/python-ai
venv\Scripts\activate
uvicorn app.main:app --host 0.0.0.0 --port 8001
```

### Verificar estado
```bash
# Estado de contenedores
docker-compose ps

# Estado de cola de trabajos
docker exec laravel_app php artisan system:queue-status

# Logs del worker en tiempo real
docker logs -f laravel_worker

# Logs de la aplicación
docker exec laravel_app tail -f storage/logs/laravel.log
```

### Detener el sistema
```bash
docker-compose down
```

---

## 3. Monitoreo de Cola de Trabajos

### Verificar estado rápido
```bash
docker exec laravel_app php artisan system:queue-status
```

### Si hay jobs acumulados (>10)
1. Verificar que `laravel_worker` está corriendo: `docker-compose ps`
2. Si el worker se detuvo: `docker-compose restart worker`
3. Si sigue acumulando: revisar logs → `docker logs laravel_worker`

### Si hay jobs fallidos
```bash
# Ver fallos detallados
docker exec laravel_app php artisan queue:failed

# Reintentar todos los fallidos
docker exec laravel_app php artisan queue:retry all

# Reintentar uno específico
docker exec laravel_app php artisan queue:retry {ID}

# Limpiar tabla de fallidos
docker exec laravel_app php artisan queue:flush
```

### Causas comunes de fallos
| Causa | Síntoma | Solución |
|:------|:--------|:---------|
| Python-AI caído | `Connection refused :8001` | Levantar servicio Python |
| Audio no encontrado | `FileNotFoundError` | Verificar ruta de audio en `storage/app/audios` |
| Timeout Whisper | `cURL error 28` | Llamada muy larga (>45min), considerar partir el audio |
| Worker crasheado | Jobs se acumulan | `docker-compose restart worker` |

---

## 4. Backups

### Backup completo
```bash
docker exec laravel_app php artisan system:backup
```
Esto genera:
- Dump SQL en `storage/app/backups/` (o CSVs si mysqldump no está instalado)
- Inventario de registros por tabla

### Backup manual de BD
```bash
docker exec laravel_mysql mysqldump -u llamadas_user -pllamadas_pass llamadas_db > backup_$(date +%Y%m%d).sql
```

### Backup de audios
```bash
# Copiar directorio de audios desde el volumen Docker
docker cp laravel_app:/var/www/html/storage/app/audios ./backup_audios/
```

### Programar backup automático (Windows Task Scheduler)
Crear una tarea programada que ejecute:
```bat
docker exec laravel_app php artisan system:backup
```
Frecuencia recomendada: diaria a las 02:00 AM

---

## 5. Política de Retención de Datos

### Simulación (dry-run)
```bash
docker exec laravel_app php artisan system:purge --days=90 --dry-run
```

### Ejecución real
```bash
# Purgar todo (llamadas + audios + análisis + logs) mayores a 90 días
docker exec laravel_app php artisan system:purge --days=90

# Purgar pero conservar audios
docker exec laravel_app php artisan system:purge --days=90 --keep-audio
```

### Política recomendada
| Dato | Retención | Justificación |
|:-----|:----------|:-------------|
| Llamadas + Análisis | 90 días | Ciclo de ventas típico |
| Audios | 60 días | Espacio en disco limitado |
| Logs de sincronización | 30 días | Solo para depuración |

---

## 6. Troubleshooting Común

### Panel web no carga
1. Verificar Nginx: `docker logs laravel_nginx`
2. Verificar PHP-FPM: `docker exec laravel_app php -v`
3. Limpiar caché: `docker exec laravel_app php artisan config:cache`

### Android no sincroniza
1. Verificar que el celular tiene conectividad a la IP del servidor
2. Verificar que puerto 8000 está abierto en firewall Windows
3. Revisar token Sanctum: `docker exec laravel_app php artisan tinker` → `PersonalAccessToken::latest()->first()`
4. Revisar logs: `docker exec laravel_app php artisan system:queue-status`

### IA no procesa audios
1. Verificar servicio Python corriendo: `curl http://localhost:8001/`
2. Verificar FFmpeg instalado: `ffmpeg -version` (desde la máquina host)
3. Verificar ruta de audio: el archivo debe existir en `storage/app/audios/`
4. El mapeo Docker→Windows se hace en `services/python-ai/app/main.py` (`map_dockered_path`)

### Rendimiento lento
1. Verificar recursos del host: RAM > 4GB recomendado para Whisper
2. Modelo Whisper `tiny` es el más rápido pero menos preciso
3. Para mejorar: cambiar a `base` en `services/python-ai/app/services/transcription.py` línea 6

---

## 7. Contactos y Accesos

| Recurso | Acceso |
|:--------|:-------|
| Panel Web | `http://{IP_SERVIDOR}:8000` |
| API Android | `http://{IP_SERVIDOR}:8000/api/` |
| MySQL (desde host) | `localhost:33061` (user: `llamadas_user`) |
| Python-AI | `http://localhost:8001` |
| Diagnóstico | `http://{IP_SERVIDOR}:8000/settings` |

---

## 8. Checklist Pre-Piloto

- [ ] Docker levantado y todos los contenedores `healthy`
- [ ] Servicio Python-AI respondiendo en `:8001`
- [ ] Panel web accesible y mostrando servicios en verde
- [ ] Vendedores registrados con dispositivos vinculados
- [ ] App Android instalada en celulares corporativos
- [ ] Login exitoso desde la app
- [ ] Llamada de prueba registrada y visible en el panel
- [ ] Audio de prueba subido y transcrito correctamente
- [ ] Backup inicial ejecutado
- [ ] Firewall configurado para exponer puerto 8000
