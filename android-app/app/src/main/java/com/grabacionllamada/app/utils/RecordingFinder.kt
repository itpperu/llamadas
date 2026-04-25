package com.grabacionllamada.app.utils

import android.content.Context
import android.net.Uri
import android.os.Build
import android.provider.MediaStore
import android.util.Log
import androidx.core.content.FileProvider
import java.io.File

object RecordingFinder {

    private const val TAG = "RecordingFinder"

    private val KNOWN_PATHS = listOf(
        // Call Up app — ruta confirmada en Redmi 14 Pro
        "/storage/emulated/0/Music/CallAppRecording",
        // HyperOS Redmi 14 Pro — grabadora nativa
        "/storage/emulated/0/Download/Grabaciones",
        // MIUI clásico
        "/storage/emulated/0/MIUI/sound_recorder/call_rec",
        "/storage/emulated/0/MIUI/sound_recorder",
        // HyperOS / MIUI 14+
        "/storage/emulated/0/Recordings/Call",
        "/storage/emulated/0/Recordings",
        "/storage/emulated/0/Record/Call",
        "/storage/emulated/0/Record",
        // Otras apps de grabación
        "/storage/emulated/0/CallRecorder",
        "/storage/emulated/0/CallRecordings",
        "/storage/emulated/0/PhoneRecord",
        "/storage/emulated/0/DCIM/call_rec",
        "/storage/emulated/0/Download"
    )

    private val AUDIO_EXTENSIONS = setOf(
        "wav", "m4a", "mp3", "aac", "ogg", "3gp", "amr",
        "opus", "caf", "wma", "flac", "mp4", "mkv"
    )

    fun findRecording(
        context: Context,
        callEndMillis: Long,
        durationSecs: Int,
        phoneNumber: String
    ): Uri? {
        Log.i(TAG, "Buscando grabación — fin llamada: ${java.util.Date(callEndMillis)}, duración: ${durationSecs}s, número: $phoneNumber")

        // Extraer últimos 6 dígitos del número para buscar en nombres de archivo
        val phoneDigits = phoneNumber.filter { it.isDigit() }.takeLast(6)

        // 1. Buscar en rutas conocidas
        val fileUri = findInKnownPaths(context, callEndMillis, phoneDigits)
        if (fileUri != null) return fileUri

        // 2. Fallback: MediaStore
        return findInMediaStore(context, callEndMillis)
    }

    private fun findInKnownPaths(context: Context, callEndMillis: Long, phoneDigits: String): Uri? {
        val windowStart = callEndMillis - (10 * 60 * 1000)
        val windowEnd   = callEndMillis + (3  * 60 * 1000)

        for (path in KNOWN_PATHS) {
            val dir = File(path)
            if (!dir.exists()) continue
            val result = searchDirectory(context, dir, windowStart, windowEnd, phoneDigits, depth = 0)
            if (result != null) return result
        }
        return null
    }

    private fun searchDirectory(
        context: Context,
        dir: File,
        windowStart: Long,
        windowEnd: Long,
        phoneDigits: String,
        depth: Int
    ): Uri? {
        val allFiles = dir.listFiles() ?: return null
        Log.d(TAG, "Buscando en: ${dir.absolutePath} — ${allFiles.size} elementos")

        allFiles.sortedByDescending { it.lastModified() }.take(5).forEach { f ->
            Log.d(TAG, "  → ${f.name} [${f.extension}] | ${java.util.Date(f.lastModified())} | ${f.length()/1024}KB")
        }

        val audioFiles = allFiles.filter { it.isFile && AUDIO_EXTENSIONS.contains(it.extension.lowercase()) }

        // Prioridad 1: archivo con número de teléfono en el nombre dentro de la ventana de tiempo
        val byPhone = audioFiles.filter {
            it.name.contains(phoneDigits) && it.lastModified() in windowStart..windowEnd
        }.sortedByDescending { it.lastModified() }

        if (byPhone.isNotEmpty()) {
            Log.i(TAG, "Coincidencia por número encontrada: ${byPhone.first().absolutePath}")
            return toUri(context, byPhone.first())
        }

        // Prioridad 2: cualquier archivo de audio en la ventana de tiempo
        val byTime = audioFiles.filter {
            it.lastModified() in windowStart..windowEnd
        }.sortedByDescending { it.lastModified() }

        if (byTime.isNotEmpty()) {
            Log.i(TAG, "Coincidencia por tiempo encontrada: ${byTime.first().absolutePath}")
            return toUri(context, byTime.first())
        }

        // Explorar subdirectorios (máximo 1 nivel)
        if (depth < 1) {
            for (subDir in allFiles.filter { it.isDirectory }) {
                val result = searchDirectory(context, subDir, windowStart, windowEnd, phoneDigits, depth + 1)
                if (result != null) return result
            }
        }

        return null
    }

    private fun toUri(context: Context, file: File): Uri {
        return try {
            FileProvider.getUriForFile(context, "${context.packageName}.fileprovider", file)
        } catch (e: Exception) {
            Uri.fromFile(file)
        }
    }

    private fun findInMediaStore(context: Context, callEndMillis: Long): Uri? {
        val collection = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            MediaStore.Audio.Media.getContentUri(MediaStore.VOLUME_EXTERNAL)
        } else {
            MediaStore.Audio.Media.EXTERNAL_CONTENT_URI
        }

        val projection = arrayOf(
            MediaStore.Audio.Media._ID,
            MediaStore.Audio.Media.DISPLAY_NAME,
            MediaStore.Audio.Media.DATE_ADDED,
            MediaStore.Audio.Media.DATA
        )

        val windowStart   = (callEndMillis / 1000) - 600 // 10 min
        val windowEnd     = (callEndMillis / 1000) + 180 // 3 min
        val selection     = "${MediaStore.Audio.Media.DATE_ADDED} BETWEEN ? AND ?"
        val selectionArgs = arrayOf(windowStart.toString(), windowEnd.toString())

        try {
            context.contentResolver.query(
                collection, projection, selection, selectionArgs,
                "${MediaStore.Audio.Media.DATE_ADDED} DESC"
            )?.use { cursor ->
                val count = cursor.count
                Log.d(TAG, "MediaStore encontró $count archivos en ventana de tiempo")

                while (cursor.moveToNext()) {
                    val id   = cursor.getLong(cursor.getColumnIndexOrThrow(MediaStore.Audio.Media._ID))
                    val name = cursor.getString(cursor.getColumnIndexOrThrow(MediaStore.Audio.Media.DISPLAY_NAME))
                    val path = cursor.getString(cursor.getColumnIndexOrThrow(MediaStore.Audio.Media.DATA))
                    Log.d(TAG, "  MediaStore: $name | $path")
                }

                if (cursor.moveToFirst()) {
                    val id  = cursor.getLong(cursor.getColumnIndexOrThrow(MediaStore.Audio.Media._ID))
                    val uri = Uri.withAppendedPath(collection, id.toString())
                    Log.i(TAG, "Usando primer resultado de MediaStore: uri=$uri")
                    return uri
                }
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error MediaStore: ${e.message}")
        }

        Log.w(TAG, "No se encontró grabación en ninguna fuente")
        return null
    }
}
