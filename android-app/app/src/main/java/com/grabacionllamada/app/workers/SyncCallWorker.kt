package com.grabacionllamada.app.workers

import android.content.Context
import android.util.Log
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.grabacionllamada.app.data.api.RetrofitClient
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.utils.SessionManager

class SyncCallWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

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
                    
                    Log.i("SyncCallWorker", "Llamada sincronizada con éxito: ID local ${call.id} -> ID remoto $remoteId")
                    val updatedCall = call.copy(isMetadataSynced = true, backendCallId = remoteId)
                    callDao.updateCall(updatedCall)
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
