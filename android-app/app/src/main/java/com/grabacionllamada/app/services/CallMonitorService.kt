package com.grabacionllamada.app.services

import android.annotation.SuppressLint
import android.app.PendingIntent
import android.app.Service
import android.content.Context
import android.content.Intent
import android.content.pm.ServiceInfo
import android.os.Build
import android.os.IBinder
import android.telephony.PhoneStateListener
import android.telephony.TelephonyCallback
import android.telephony.TelephonyManager
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.grabacionllamada.app.GrabacionApp
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.data.local.CallEntity
import com.grabacionllamada.app.ui.main.MainActivity
import com.grabacionllamada.app.utils.CallLogReader
import com.grabacionllamada.app.utils.PhoneNumberNormalizer
import com.grabacionllamada.app.utils.WorkerUtils
import com.grabacionllamada.app.workers.SyncCallWorker
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class CallMonitorService : Service() {

    private var telephonyManager: TelephonyManager? = null
    private var previousState = TelephonyManager.CALL_STATE_IDLE
    private var telephonyCallback: Any? = null
    private var legacyListener: PhoneStateListener? = null

    companion object {
        private const val TAG = "CallMonitorService"

        fun start(context: Context) {
            val intent = Intent(context, CallMonitorService::class.java)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                context.startForegroundService(intent)
            } else {
                context.startService(intent)
            }
        }

        fun stop(context: Context) {
            context.stopService(Intent(context, CallMonitorService::class.java))
        }
    }

    override fun onCreate() {
        super.onCreate()
        Log.i(TAG, "Servicio iniciado (Android ${Build.VERSION.SDK_INT})")
        startForegroundNotification()
        registerCallStateListener()
    }

    private fun startForegroundNotification() {
        val pendingIntent = PendingIntent.getActivity(
            this, 0,
            Intent(this, MainActivity::class.java),
            PendingIntent.FLAG_IMMUTABLE
        )
        val notification = NotificationCompat.Builder(this, GrabacionApp.CHANNEL_ID)
            .setContentTitle("Monitoreo activo")
            .setContentText("Detectando llamadas comerciales...")
            .setSmallIcon(android.R.drawable.ic_menu_call)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .build()

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            startForeground(1, notification, ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC)
        } else {
            startForeground(1, notification)
        }
    }

    @SuppressLint("MissingPermission")
    private fun registerCallStateListener() {
        telephonyManager = getSystemService(TELEPHONY_SERVICE) as TelephonyManager

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            val callback = object : TelephonyCallback(), TelephonyCallback.CallStateListener {
                override fun onCallStateChanged(state: Int) {
                    Log.d(TAG, "TelephonyCallback estado: $previousState → $state")
                    handleStateChange(state)
                }
            }
            telephonyCallback = callback
            telephonyManager?.registerTelephonyCallback(mainExecutor, callback)
            Log.i(TAG, "TelephonyCallback registrado (API 31+)")
        } else {
            @Suppress("DEPRECATION")
            legacyListener = object : PhoneStateListener() {
                @Deprecated("Deprecated in Java")
                override fun onCallStateChanged(state: Int, phoneNumber: String?) {
                    Log.d(TAG, "PhoneStateListener estado: $previousState → $state")
                    handleStateChange(state)
                }
            }
            @Suppress("DEPRECATION")
            telephonyManager?.listen(legacyListener, PhoneStateListener.LISTEN_CALL_STATE)
            Log.i(TAG, "PhoneStateListener registrado (API < 31)")
        }
    }

    private fun handleStateChange(state: Int) {
        val callEnded = state == TelephonyManager.CALL_STATE_IDLE &&
            (previousState == TelephonyManager.CALL_STATE_OFFHOOK ||
             previousState == TelephonyManager.CALL_STATE_RINGING)
        previousState = state
        if (callEnded) {
            Log.i(TAG, "Llamada terminada — iniciando procesamiento")
            processCallEnded()
        }
    }

    private fun processCallEnded() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                Log.i(TAG, "Esperando 3s para que el sistema registre la llamada...")
                delay(3000L)

                val lastCall = CallLogReader.getLastCall(this@CallMonitorService)
                if (lastCall == null) {
                    Log.w(TAG, "CallLog vacío — reintentando en 3s...")
                    delay(3000L)
                    val retry = CallLogReader.getLastCall(this@CallMonitorService)
                    if (retry == null) {
                        Log.e(TAG, "No se encontró llamada tras reintento. Verificar permiso READ_CALL_LOG.")
                        return@launch
                    }
                    saveAndSync(retry)
                    return@launch
                }

                saveAndSync(lastCall)

            } catch (e: Exception) {
                Log.e(TAG, "Error procesando llamada: ${e.message}")
            }
        }
    }

    private suspend fun saveAndSync(lastCall: CallLogReader.CallMetadata) {
        val normalizedNumber = PhoneNumberNormalizer.normalize(lastCall.number)

        // Evitar duplicados
        val db       = AppDatabase.getDatabase(applicationContext)
        val existing = db.callDao().getAllCalls()
        if (existing.any { it.fechaInicio == lastCall.dateStartIso && it.telefonoCliente == normalizedNumber }) {
            Log.d(TAG, "Llamada ya registrada, ignorando duplicado")
            return
        }

        Log.i(TAG, "Nueva llamada: $normalizedNumber | ${lastCall.type} | ${lastCall.durationSeconds}s")

        val id = db.callDao().insertCall(
            CallEntity(
                telefonoCliente  = normalizedNumber,
                tipo             = lastCall.type,
                fechaInicio      = lastCall.dateStartIso,
                fechaFin         = lastCall.dateEndIso,
                duracionSegundos = lastCall.durationSeconds,
                isMetadataSynced = false,
                isAudioSynced    = false,
                audioPath        = null
            )
        )
        Log.i(TAG, "Llamada guardada con ID local: $id")

        WorkerUtils.enqueueSyncCall(applicationContext)
        Log.i(TAG, "Worker de sincronización encolado (único)")
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int = START_STICKY

    override fun onDestroy() {
        super.onDestroy()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            (telephonyCallback as? TelephonyCallback)?.let {
                telephonyManager?.unregisterTelephonyCallback(it)
            }
        } else {
            @Suppress("DEPRECATION")
            telephonyManager?.listen(legacyListener, PhoneStateListener.LISTEN_NONE)
        }
        Log.i(TAG, "Servicio detenido")
    }

    override fun onBind(intent: Intent?): IBinder? = null
}
