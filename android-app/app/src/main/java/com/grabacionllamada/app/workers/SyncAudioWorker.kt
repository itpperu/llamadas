package com.grabacionllamada.app.workers

import android.content.Context
import android.net.Uri
import android.util.Log
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.grabacionllamada.app.data.api.RetrofitClient
import com.grabacionllamada.app.data.local.AppDatabase
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

    override suspend fun doWork(): Result {
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
                // Leer el URI guardado
                val uri = Uri.parse(call.audioPath)
                val contentResolver = applicationContext.contentResolver
                
                // Tratar de averiguar el MIME Type
                val mimeTypeString = contentResolver.getType(uri) ?: "audio/mpeg"
                
                // Copiar a un archivo temporal para enviar con OkHttp
                val tempFile = File(applicationContext.cacheDir, "temp_audio_${call.id}.m4a")
                contentResolver.openInputStream(uri)?.use { input ->
                    FileOutputStream(tempFile).use { output ->
                        input.copyTo(output)
                    }
                }

                if (!tempFile.exists()) {
                    Log.e("SyncAudioWorker", "No se pudo crear archivo temporal para llamada ID ${call.id}")
                    hasFailures = true
                    continue
                }

                Log.d("SyncAudioWorker", "Subiendo audio para llamada remota ID: $backendId...")
                
                val reqFile = tempFile.asRequestBody(mimeTypeString.toMediaTypeOrNull())
                val multipartBody = MultipartBody.Part.createFormData("audio_file", tempFile.name, reqFile)
                val hashBody = "dummy-hash".toRequestBody("text/plain".toMediaTypeOrNull())
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
                } else {
                    val code = response.code()
                    when (code) {
                        401 -> return Result.failure()
                        422 -> Log.e("SyncAudioWorker", "HTTP 422 Payload Invalido para audio llamada $backendId.")
                        else -> {
                            Log.e("SyncAudioWorker", "Error HTTP $code al subir audio llamada ID remoto $backendId.")
                            hasFailures = true
                        }
                    }
                }
                
                // Limpiar caché
                tempFile.delete()

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
