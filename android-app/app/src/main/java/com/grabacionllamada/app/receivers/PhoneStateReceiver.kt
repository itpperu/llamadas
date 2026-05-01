package com.grabacionllamada.app.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.telephony.TelephonyManager
import android.util.Log
import com.grabacionllamada.app.services.CallMonitorService
import com.grabacionllamada.app.utils.SessionManager

/**
 * Receptor de respaldo. Su único rol es asegurarse de que CallMonitorService
 * esté corriendo cuando el sistema envía eventos de teléfono.
 * NO inserta llamadas — eso lo hace el servicio directamente.
 */
class PhoneStateReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action != TelephonyManager.ACTION_PHONE_STATE_CHANGED) return
        if (!SessionManager(context).isLoggedIn()) return

        Log.d("PhoneStateReceiver", "Evento de teléfono — verificando que el servicio esté activo")
        CallMonitorService.start(context)
    }
}
