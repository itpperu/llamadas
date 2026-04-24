package com.grabacionllamada.app.data.api

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST

interface ApiService {

    @POST("auth/login")
    suspend fun login(@Body request: Map<String, String>): Response<Map<String, Any>>

    @POST("calls")
    suspend fun registerCall(@Body request: Map<String, @JvmSuppressWildcards Any>): Response<Map<String, @JvmSuppressWildcards Any>>

    @retrofit2.http.Multipart
    @POST("calls/{call_id}/audio")
    suspend fun uploadAudio(
        @retrofit2.http.Path("call_id") callId: Int,
        @retrofit2.http.Part audioFile: okhttp3.MultipartBody.Part,
        @retrofit2.http.Part("audio_hash") audioHash: okhttp3.RequestBody,
        @retrofit2.http.Part("mime_type") mimeType: okhttp3.RequestBody,
        @retrofit2.http.Part("source_mode") sourceMode: okhttp3.RequestBody
    ): Response<Map<String, @JvmSuppressWildcards Any>>
}
