package com.grabacionllamada.app.receivers

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import com.grabacionllamada.app.services.CallMonitorService
import com.grabacionllamada.app.utils.SessionManager

class BootReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Intent.ACTION_BOOT_COMPLETED) {
            // Solo iniciar si hay sesión activa
            if (SessionManager(context).isLoggedIn()) {
                CallMonitorService.start(context)
            }
        }
    }
}
