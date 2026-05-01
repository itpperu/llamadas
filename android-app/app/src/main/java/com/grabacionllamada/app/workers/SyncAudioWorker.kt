package com.grabacionllamada.app.workers

import android.app.NotificationManager
import android.content.Context
import android.content.pm.ServiceInfo
import android.net.Uri
import android.os.Build
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.work.CoroutineWorker
import androidx.work.ForegroundInfo
import androidx.work.WorkerParameters
import com.grabacionllamada.app.GrabacionApp
import com.grabacionllamada.app.data.api.RetrofitClient
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.utils.AudioCompressor
import com.grabacionllamada.app.utils.SessionManager
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File
import java.io.FileOutputStream

class SyncAudioWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    override suspend fun getForegroundInfo(): ForegroundInfo {
        val notification = NotificationCompat.Builder(applicationContext, GrabacionApp.CHANNEL_ID)
            .setContentTitle("Subiendo grabación...")
            .setContentText("Sincronizando audio de llamada con el servidor")
            .setSmallIcon(android.R.drawable.ic_menu_upload)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .build()
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            ForegroundInfo(3, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC)
        } else {
            ForegroundInfo(3, notification)
        }
    }

    override suspend fun doWork(): Result {
        runCatching { setForeground(getForegroundInfo()) }
            .onFailure { Log.w("SyncAudioWorker", "No se pudo promover a foreground: ${it.message}") }

        val sessionManager = SessionManager(applicationContext)
        if (!sessionManager.isLoggedIn()) {
            Log.w("SyncAudioWorker", "No hay sesión activa. Cancelando Worker de Audio.")
            return Result.failure()
        }

        val db = AppDatabase.getDatabase(applicationContext)
        val apiService = RetrofitClient.create(sessionManager)
        val callDao = db.callDao()

        val unsyncedCalls = callDao.getUnsyncedAudioCalls()
        if (unsyncedCalls.isEmpty()) {
            Log.i("SyncAudioWorker", "No hay audios pendientes de sincronización.")
            return Result.success()
        }

        Log.i("SyncAudioWorker", "Sincronizando ${unsyncedCalls.size} llamadas de audio.")
        
        var hasFailures = false

        for (call in unsyncedCalls) {
            val backendId = call.backendCallId
            if (backendId == null) {
                Log.e("SyncAudioWorker", "Llamada local ${call.id} no tiene backendCallId (metadata incompleta).")
                continue
            }

            try {
                val audioPath = call.audioPath ?: continue
                val uri = Uri.parse(audioPath)

                // Determinar extensión del archivo original
                val originalExt = audioPath.substringAfterLast('.', "mp3").lowercase()
                val ext = if (originalExt in setOf("mp3","wav","m4a","aac","ogg","3gp","amr","opus")) originalExt else "mp3"
                val mimeTypeString = when (ext) {
                    "mp3"  -> "audio/mpeg"
                    "wav"  -> "audio/wav"
                    "m4a"  -> "audio/m4a"
                    "aac"  -> "audio/aac"
                    "ogg"  -> "audio/ogg"
                    "3gp"  -> "audio/3gpp"
                    "amr"  -> "audio/amr"
                    "opus" -> "audio/opus"
                    else   -> "audio/mpeg"
                }

                // Copiar a archivo temporal — soporte para file:// y content://
                val tempFile = File(applicationContext.cacheDir, "temp_audio_${call.id}.$ext")
                val inputStream = when (uri.scheme) {
                    "file"    -> java.io.FileInputStream(uri.path!!)
                    "content" -> applicationContext.contentResolver.openInputStream(uri)
                    else      -> java.io.FileInputStream(audioPath)
                }

                inputStream?.use { input ->
                    FileOutputStream(tempFile).use { output ->
                        input.copyTo(output)
                    }
                }

                if (!tempFile.exists() || tempFile.length() == 0L) {
                    Log.e("SyncAudioWorker", "No se pudo leer el archivo de audio para llamada ID ${call.id} — path: $audioPath")
                    hasFailures = true
                    continue
                }

                // Comprimir si el archivo supera 1MB
                val uploadPath = AudioCompressor.compress(applicationContext, tempFile.absolutePath)
                val uploadFile = File(uploadPath)
                Log.i("SyncAudioWorker", "Archivo listo: ${uploadFile.name} (${uploadFile.length()/1024}KB)")

                Log.d("SyncAudioWorker", "Subiendo audio para llamada remota ID: $backendId (${tempFile.length()/1024}KB)...")
                
                // Calcular MD5 real del archivo para validación del servidor
                val md5Hash = java.security.MessageDigest.getInstance("MD5")
                    .digest(uploadFile.readBytes())
                    .joinToString("") { "%02x".format(it) }
                Log.d("SyncAudioWorker", "MD5: $md5Hash")

                val uploadMime = if (uploadPath.endsWith(".m4a")) "audio/m4a" else mimeTypeString
                val reqFile = uploadFile.asRequestBody(uploadMime.toMediaTypeOrNull())
                val multipartBody = MultipartBody.Part.createFormData("audio_file", uploadFile.name, reqFile)
                val hashBody = md5Hash.toRequestBody("text/plain".toMediaTypeOrNull())
                val mimeBody = mimeTypeString.toRequestBody("text/plain".toMediaTypeOrNull())
                val sourceModeBody = "manual".toRequestBody("text/plain".toMediaTypeOrNull())

                val response = apiService.uploadAudio(
                    callId = backendId,
                    audioFile = multipartBody,
                    audioHash = hashBody,
                    mimeType = mimeBody,
                    sourceMode = sourceModeBody
                )

                if (response.isSuccessful) {
                    Log.i("SyncAudioWorker", "Audio sincronizado con éxito para llamada ID remoto $backendId")
                    val updatedCall = call.copy(isAudioSynced = true)
                    callDao.updateCall(updatedCall)

                    // Eliminar archivos locales para liberar espacio en Call Up
                    tempFile.delete()
                    if (uploadPath != tempFile.absolutePath) File(uploadPath).delete()
                    // Eliminar el archivo original de Call Up si es accesible
                    val originalFile = if (audioPath.startsWith("file://")) File(audioPath.removePrefix("file://")) else null
                    if (originalFile?.exists() == true) {
                        originalFile.delete()
                        Log.i("SyncAudioWorker", "Archivo original eliminado de Call Up: ${originalFile.name}")
                    }
                } else {
                    val code = response.code()
                    when (code) {
                        401 -> return Result.failure()
                        422 -> {
                        val errorBody = response.errorBody()?.string() ?: "sin detalle"
                        Log.e("SyncAudioWorker", "HTTP 422 para audio $backendId: $errorBody")
                    }
                        else -> {
                            Log.e("SyncAudioWorker", "Error HTTP $code al subir audio llamada ID remoto $backendId.")
                            hasFailures = true
                        }
                    }
                }
                
                // Limpiar caché
                tempFile.delete()

            } catch (e: kotlinx.coroutines.CancellationException) {
                Log.w("SyncAudioWorker", "Worker cancelado por el sistema")
                throw e
            } catch (e: java.io.FileNotFoundException) {
                Log.e("SyncAudioWorker", "Archivo no encontrado o borrado para llamada ID local ${call.id}. Revirtiendo a pendiente. Detalle: ${e.message}")
                val revertedCall = call.copy(audioPath = null)
                callDao.updateCall(revertedCall)
                // NO marcamos hasFailures=true porque ya lo sacamos de la cola de audio y regresó a la cola de asociación.
            } catch (e: SecurityException) {
                Log.e("SyncAudioWorker", "Permiso denegado al leer URI para llamada ID local ${call.id}. Revirtiendo a pendiente. Detalle: ${e.message}")
                val revertedCall = call.copy(audioPath = null)
                callDao.updateCall(revertedCall)
            } catch (e: Exception) {
                Log.e("SyncAudioWorker", "Excepción subiendo audio llamada ID local ${call.id}: ${e.message}")
                hasFailures = true
            }
        }

        return if (hasFailures) Result.retry() else Result.success()
    }
}
