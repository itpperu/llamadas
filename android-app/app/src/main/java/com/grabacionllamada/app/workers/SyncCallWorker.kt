package com.grabacionllamada.app.workers

import android.content.Context
import android.content.pm.ServiceInfo
import android.os.Build
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.work.Constraints
import androidx.work.CoroutineWorker
import androidx.work.ForegroundInfo
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import androidx.work.WorkerParameters
import com.grabacionllamada.app.GrabacionApp
import com.grabacionllamada.app.data.api.RetrofitClient
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.utils.RecordingFinder
import com.grabacionllamada.app.utils.SessionManager
import com.grabacionllamada.app.utils.WorkerUtils
import kotlinx.coroutines.delay

class SyncCallWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    override suspend fun getForegroundInfo(): ForegroundInfo {
        val notification = NotificationCompat.Builder(applicationContext, GrabacionApp.CHANNEL_ID)
            .setContentTitle("Sincronizando llamada...")
            .setContentText("Registrando llamada en el servidor")
            .setSmallIcon(android.R.drawable.ic_menu_send)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .build()
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            ForegroundInfo(4, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC)
        } else {
            ForegroundInfo(4, notification)
        }
    }

    override suspend fun doWork(): Result {
        val sessionManager = SessionManager(applicationContext)
        if (!sessionManager.isLoggedIn()) {
            Log.w("SyncCallWorker", "No hay sesión activa. Cancelando Worker.")
            return Result.failure()
        }

        val deviceUuid = sessionManager.getDeviceUuid() ?: return Result.failure()
        val vendedorId = sessionManager.getVendedorId()
        if (vendedorId == -1) return Result.failure()

        val db = AppDatabase.getDatabase(applicationContext)
        val apiService = RetrofitClient.create(sessionManager)
        val callDao = db.callDao()

        val unsyncedCalls = callDao.getUnsyncedMetadataCalls()
        if (unsyncedCalls.isEmpty()) {
            Log.i("SyncCallWorker", "No hay metadata pendiente de sincronización.")
            return Result.success()
        }

        Log.i("SyncCallWorker", "Sincronizando ${unsyncedCalls.size} llamadas de metadata.")
        
        var hasFailures = false

        for (call in unsyncedCalls) {
            val payload = mapOf<String, Any>(
                "device_uuid" to deviceUuid,
                "vendedor_id" to vendedorId,
                "telefono_cliente" to call.telefonoCliente,
                "tipo" to call.tipo,
                "fecha_inicio" to call.fechaInicio,
                "fecha_fin" to call.fechaFin,
                "duracion_segundos" to call.duracionSegundos,
                "estado_audio" to "no_grabado"
            )

            try {
                Log.d("SyncCallWorker", "Sincronizando llamada ID local: ${call.id}...")
                val response = apiService.registerCall(payload)
                if (response.isSuccessful) {
                    val responseBody = response.body()
                    val data = responseBody?.get("data") as? Map<*, *>
                    val remoteId = (data?.get("call_id") as? Double)?.toInt()

                    Log.i("SyncCallWorker", "Llamada sincronizada: ID local ${call.id} -> ID remoto $remoteId")

                    // Marcar metadata como sincronizada primero
                    callDao.updateCall(call.copy(isMetadataSynced = true, backendCallId = remoteId))

                    // Buscar grabación con reintentos — el sistema puede tardar en escribir el archivo
                    val callEndMillis = try {
                        java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", java.util.Locale.getDefault())
                            .parse(call.fechaFin)?.time ?: System.currentTimeMillis()
                    } catch (e: Exception) { System.currentTimeMillis() }

                    var recordingUri: android.net.Uri? = null
                    val delays = listOf(8_000L, 15_000L, 30_000L) // 3 intentos: 8s, 15s, 30s

                    for (waitMs in delays) {
                        delay(waitMs)
                        Log.d("SyncCallWorker", "Buscando grabación (espera acumulada ${delays.take(delays.indexOf(waitMs) + 1).sum() / 1000}s)...")
                        recordingUri = RecordingFinder.findRecording(
                            applicationContext, callEndMillis,
                            call.duracionSegundos, call.telefonoCliente
                        )
                        if (recordingUri != null) break
                    }

                    if (recordingUri != null) {
                        Log.i("SyncCallWorker", "Grabación encontrada automáticamente. Encolando subida.")
                        callDao.updateCall(
                            callDao.getCallById(call.id)!!.copy(audioPath = recordingUri.toString())
                        )
                        WorkerUtils.enqueueSyncAudio(applicationContext)
                    } else {
                        Log.w("SyncCallWorker", "Grabación no encontrada tras 3 intentos. Asociación manual requerida.")
                    }
                } else {
                    val code = response.code()
                    when (code) {
                        401 -> {
                            Log.e("SyncCallWorker", "HTTP 401 No Autorizado. Token inválido/expirado para la llamada local ${call.id}. Falla definitiva.")
                            return Result.failure() // Error definitivo (requiere relogin)
                        }
                        422 -> {
                            Log.e("SyncCallWorker", "HTTP 422 Validación Fallida. El payload de la llamada ${call.id} no es válido. Omitiendo reintento para evitar loop.")
                            // No aplicamos hasFailures = true para que el Worker finalice en SUCCESS en este lote, 
                            // aunque la llamada específica quedará pendiente (pero no bloqueamos a las demás eternamente).
                        }
                        in 500..599 -> {
                            Log.e("SyncCallWorker", "HTTP $code Error de Servidor al sincronizar llamada ${call.id}. Reintento transitorio.")
                            hasFailures = true
                        }
                        else -> {
                            Log.e("SyncCallWorker", "Error HTTP $code al sincronizar llamada ID ${call.id}.")
                            hasFailures = true
                        }
                    }
                }
            } catch (e: Exception) {
                Log.e("SyncCallWorker", "Excepción de Red/Transitoria sincronizando llamada ID ${call.id}: ${e.message}. Reintento transitorio.")
                hasFailures = true
            }
        }

        return if (hasFailures) Result.retry() else Result.success()
    }
}
