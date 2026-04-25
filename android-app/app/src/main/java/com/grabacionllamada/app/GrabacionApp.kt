package com.grabacionllamada.app

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build

class GrabacionApp : Application() {

    companion object {
        const val CHANNEL_ID = "call_monitor_channel"
    }

    override fun onCreate() {
        super.onCreate()
        createNotificationChannel()
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Monitor de Llamadas",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Servicio activo de monitoreo de llamadas comerciales"
            }
            val manager = getSystemService(NotificationManager::class.java)
            manager.createNotificationChannel(channel)
        }
    }
}
