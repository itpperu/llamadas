package com.grabacionllamada.app.utils

import android.Manifest
import android.content.Context
import android.content.pm.PackageManager
import android.provider.CallLog
import android.util.Log
import androidx.core.content.ContextCompat
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

object CallLogReader {

    private const val TAG = "CallLogReader"

    data class CallMetadata(
        val number: String,
        val type: String,
        val dateStartIso: String,
        val dateEndIso: String,
        val durationSeconds: Int
    )

    fun getLastCall(context: Context): CallMetadata? {

        // Verificar permiso antes de consultar
        if (ContextCompat.checkSelfPermission(context, Manifest.permission.READ_CALL_LOG)
            != PackageManager.PERMISSION_GRANTED) {
            Log.e(TAG, "Permiso READ_CALL_LOG no otorgado")
            return null
        }

        return try {
            val projection = arrayOf(
                CallLog.Calls.NUMBER,
                CallLog.Calls.TYPE,
                CallLog.Calls.DATE,
                CallLog.Calls.DURATION
            )

            context.contentResolver.query(
                CallLog.Calls.CONTENT_URI,
                projection,
                null,
                null,
                "${CallLog.Calls.DATE} DESC"   // Sin LIMIT — moveToFirst() toma el primero
            )?.use { cursor ->
                if (!cursor.moveToFirst()) {
                    Log.w(TAG, "CallLog vacío")
                    return null
                }

                val number   = cursor.getString(cursor.getColumnIndexOrThrow(CallLog.Calls.NUMBER)) ?: ""
                val typeCode = cursor.getInt(cursor.getColumnIndexOrThrow(CallLog.Calls.TYPE))
                val dateMs   = cursor.getLong(cursor.getColumnIndexOrThrow(CallLog.Calls.DATE))
                val duration = cursor.getInt(cursor.getColumnIndexOrThrow(CallLog.Calls.DURATION))

                val typeStr = when (typeCode) {
                    CallLog.Calls.INCOMING_TYPE  -> "entrante"
                    CallLog.Calls.OUTGOING_TYPE  -> "saliente"
                    CallLog.Calls.MISSED_TYPE    -> "perdida"
                    CallLog.Calls.REJECTED_TYPE  -> "perdida"
                    else                         -> "entrante"
                }

                val sdf        = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                val startDate  = Date(dateMs)
                val endDate    = Date(dateMs + duration * 1000L)

                Log.i(TAG, "Última llamada: $number / $typeStr / ${duration}s")

                CallMetadata(
                    number          = number,
                    type            = typeStr,
                    dateStartIso    = sdf.format(startDate),
                    dateEndIso      = sdf.format(endDate),
                    durationSeconds = duration
                )
            }
        } catch (e: SecurityException) {
            Log.e(TAG, "Permiso denegado: ${e.message}")
            null
        } catch (e: Exception) {
            Log.e(TAG, "Error leyendo CallLog: ${e.message}")
            null
        }
    }
}
