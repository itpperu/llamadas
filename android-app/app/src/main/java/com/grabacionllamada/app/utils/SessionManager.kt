package com.grabacionllamada.app.utils

import android.content.Context
import android.content.SharedPreferences

class SessionManager(context: Context) {
    private val prefs: SharedPreferences = context.getSharedPreferences("GrabacionAppPrefs", Context.MODE_PRIVATE)

    fun saveAuthData(token: String, vendedorId: Int, vendedorNombre: String, deviceUuid: String) {
        prefs.edit()
            .putString("AUTH_TOKEN", token)
            .putInt("VENDEDOR_ID", vendedorId)
            .putString("VENDEDOR_NOMBRE", vendedorNombre)
            .putString("DEVICE_UUID", deviceUuid)
            .apply()
    }

    fun getAuthToken(): String? {
        return prefs.getString("AUTH_TOKEN", null)
    }

    fun getVendedorId(): Int {
        return prefs.getInt("VENDEDOR_ID", -1)
    }

    fun getDeviceUuid(): String? {
        return prefs.getString("DEVICE_UUID", null)
    }

    fun isLoggedIn(): Boolean {
        return getAuthToken() != null
    }

    fun clearSession() {
        prefs.edit().clear().apply()
    }
}
