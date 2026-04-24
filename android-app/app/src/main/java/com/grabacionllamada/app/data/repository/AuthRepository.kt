package com.grabacionllamada.app.data.repository

import com.grabacionllamada.app.data.api.ApiService
import com.grabacionllamada.app.utils.SessionManager

class AuthRepository(private val apiService: ApiService, private val sessionManager: SessionManager) {

    suspend fun login(usuario: String, password: String, deviceUuid: String): Result<Boolean> {
        return try {
            val response = apiService.login(
                mapOf(
                    "usuario" to usuario,
                    "password" to password,
                    "device_uuid" to deviceUuid
                )
            )

            if (response.isSuccessful && response.body() != null) {
                val body = response.body()!!
                val success = body["success"] as? Boolean ?: false
                if (success) {
                    val data = body["data"] as? Map<String, Any>
                    val token = data?.get("token") as? String
                    val vendedor = data?.get("vendedor") as? Map<String, Any>
                    
                    // JSON parsing seguro manual MVP (para Evitar DTOs complejos extras)
                    val vId = (vendedor?.get("id") as? Double)?.toInt() ?: -1
                    val vNombre = vendedor?.get("nombre") as? String ?: ""

                    if (token != null) {
                        sessionManager.saveAuthData(token, vId, vNombre, deviceUuid)
                        Result.success(true)
                    } else {
                        Result.failure(Exception("Token mal formado"))
                    }
                } else {
                    val msg = body["message"] as? String ?: "Credenciales inválidas"
                    Result.failure(Exception(msg))
                }
            } else {
                Result.failure(Exception("Error en servidor: HTTP ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
