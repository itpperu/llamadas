package com.grabacionllamada.app.utils

import android.content.Context
import android.content.SharedPreferences
import com.grabacionllamada.app.BuildConfig

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

    fun saveServerUrl(url: String) {
        prefs.edit().putString("SERVER_URL", url.trimEnd('/') + "/").apply()
    }

    fun getServerUrl(): String {
        return prefs.getString("SERVER_URL", null) ?: BuildConfig.BASE_URL
    }

    fun clearSession() {
        prefs.edit()
            .remove("AUTH_TOKEN")
            .remove("VENDEDOR_ID")
            .remove("VENDEDOR_NOMBRE")
            .remove("DEVICE_UUID")
            .apply()
    }
}
