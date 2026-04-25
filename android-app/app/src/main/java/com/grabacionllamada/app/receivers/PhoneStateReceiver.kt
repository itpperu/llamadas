package com.grabacionllamada.app.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.telephony.TelephonyManager
import android.util.Log
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.grabacionllamada.app.utils.WorkerUtils
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.data.local.CallEntity
import com.grabacionllamada.app.utils.CallLogReader
import com.grabacionllamada.app.utils.PhoneNumberNormalizer
import com.grabacionllamada.app.workers.SyncCallWorker
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class PhoneStateReceiver : BroadcastReceiver() {

    companion object {
        // Estado estático — persiste entre instancias del receiver
        @Volatile private var previousState = TelephonyManager.EXTRA_STATE_IDLE
        private const val TAG = "PhoneStateReceiver"
    }

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != TelephonyManager.ACTION_PHONE_STATE_CHANGED) return

        val state = intent.getStringExtra(TelephonyManager.EXTRA_STATE) ?: return
        Log.d(TAG, "Estado: $previousState → $state")

        // Detectar fin de llamada: venía de OFFHOOK o RINGING y pasó a IDLE
        val callEnded = state == TelephonyManager.EXTRA_STATE_IDLE &&
            (previousState == TelephonyManager.EXTRA_STATE_OFFHOOK ||
             previousState == TelephonyManager.EXTRA_STATE_RINGING)

        previousState = state

        if (!callEnded) return

        Log.i(TAG, "Fin de llamada detectado. Procesando...")

        val pendingResult = goAsync()

        CoroutineScope(Dispatchers.IO).launch {
            try {
                // Esperar que el sistema registre la llamada en el CallLog
                delay(3000L)

                val lastCall = CallLogReader.getLastCall(context)
                if (lastCall == null) {
                    Log.w(TAG, "No se encontró llamada en el CallLog")
                    pendingResult.finish()
                    return@launch
                }

                val normalizedNumber = PhoneNumberNormalizer.normalize(lastCall.number)
                Log.i(TAG, "Llamada detectada: $normalizedNumber | ${lastCall.type} | ${lastCall.durationSeconds}s")

                val entity = CallEntity(
                    telefonoCliente  = normalizedNumber,
                    tipo             = lastCall.type,
                    fechaInicio      = lastCall.dateStartIso,
                    fechaFin         = lastCall.dateEndIso,
                    duracionSegundos = lastCall.durationSeconds,
                    isMetadataSynced = false,
                    isAudioSynced    = false,
                    audioPath        = null
                )

                val db = AppDatabase.getDatabase(context)
                val id = db.callDao().insertCall(entity)
                Log.i(TAG, "Llamada guardada localmente con ID: $id")

                WorkerUtils.enqueueSyncCall(context)
                Log.i(TAG, "Worker de sincronización encolado")

            } catch (e: Exception) {
                Log.e(TAG, "Error procesando llamada: ${e.message}")
            } finally {
                pendingResult.finish()
            }
        }
    }
}
