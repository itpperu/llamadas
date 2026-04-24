package com.grabacionllamada.app.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.telephony.TelephonyManager
import android.util.Log
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.data.local.CallEntity
import com.grabacionllamada.app.utils.CallLogReader
import com.grabacionllamada.app.utils.PhoneNumberNormalizer
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.grabacionllamada.app.workers.SyncCallWorker
class PhoneStateReceiver : BroadcastReceiver() {

    private var previousState = TelephonyManager.EXTRA_STATE_IDLE

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != TelephonyManager.ACTION_PHONE_STATE_CHANGED) return

        val state = intent.getStringExtra(TelephonyManager.EXTRA_STATE) ?: return
        Log.d("PhoneStateReceiver", "Cambio de Phone state: $state")

        // Intercepta cuando la llamada cambia de OFFHOOK/RINGING a IDLE (fin llamada)
        if (state == TelephonyManager.EXTRA_STATE_IDLE && previousState != TelephonyManager.EXTRA_STATE_IDLE) {
            Log.d("PhoneStateReceiver", "Fin de llamada detectada (Transición a IDLE).")

            val pendingResult = goAsync() // Vida util de corrutina permitida por OS
            val db = AppDatabase.getDatabase(context)

            CoroutineScope(Dispatchers.IO).launch {
                try {
                    Log.i("PhoneStateReceiver", "Esperando 2.5s para consolidación del CallLog nativo...")
                    delay(2500L) // Android demora un instante en persistir el log

                    val lastCall = CallLogReader.getLastCall(context)
                    if (lastCall != null) {
                        val normalizedNumber = PhoneNumberNormalizer.normalize(lastCall.number)
                        
                        val entity = CallEntity(
                            telefonoCliente = normalizedNumber,
                            tipo = lastCall.type,
                            fechaInicio = lastCall.dateStartIso,
                            fechaFin = lastCall.dateEndIso,
                            duracionSegundos = lastCall.durationSeconds,
                            isMetadataSynced = false,
                            isAudioSynced = false,
                            audioPath = null
                        )

                        val id = db.callDao().insertCall(entity)
                        Log.i("PhoneStateReceiver", "Llamada guardada limpia en DB local. (ID Local: $id). Pendiente de Sincronización.")

                        val constraints = Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build()
                        val workRequest = OneTimeWorkRequestBuilder<SyncCallWorker>()
                            .setConstraints(constraints)
                            .build()
                        WorkManager.getInstance(context).enqueue(workRequest)
                        Log.i("PhoneStateReceiver", "Worker de sincronización encolado automáticamentente.")
                    } else {
                        Log.w("PhoneStateReceiver", "El Cursor vino vacío o falló la extracción de Metadatos.")
                    }
                } catch (e: Exception) {
                    Log.e("PhoneStateReceiver", "Error atrapando la llamada finalizada: ${e.message}")
                } finally {
                    Log.i("PhoneStateReceiver", "Liberando Recepción.")
                    pendingResult.finish()
                }
            }
        }
        previousState = state
    }
}
