package com.grabacionllamada.app.utils

import android.content.Context
import android.provider.CallLog
import android.util.Log
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

object CallLogReader {

    data class CallMetadata(
        val number: String,
        val type: String,
        val dateStartIso: String,
        val dateEndIso: String,
        val durationSeconds: Int
    )

    fun getLastCall(context: Context): CallMetadata? {
        try {
            val projection = arrayOf(
                CallLog.Calls.NUMBER,
                CallLog.Calls.TYPE,
                CallLog.Calls.DATE,
                CallLog.Calls.DURATION
            )
            val cursor = context.contentResolver.query(
                CallLog.Calls.CONTENT_URI,
                projection,
                null,
                null,
                CallLog.Calls.DATE + " DESC LIMIT 1"
            )

            cursor?.use {
                if (it.moveToFirst()) {
                    val numIndex = it.getColumnIndex(CallLog.Calls.NUMBER)
                    val typeIndex = it.getColumnIndex(CallLog.Calls.TYPE)
                    val dateIndex = it.getColumnIndex(CallLog.Calls.DATE)
                    val durIndex = it.getColumnIndex(CallLog.Calls.DURATION)

                    val number = it.getString(numIndex)
                    val typeCode = it.getInt(typeIndex)
                    val dateMillis = it.getLong(dateIndex)
                    val duration = it.getInt(durIndex)

                    val typeStr = when (typeCode) {
                        CallLog.Calls.INCOMING_TYPE -> "entrante"
                        CallLog.Calls.OUTGOING_TYPE -> "saliente"
                        CallLog.Calls.MISSED_TYPE -> "perdida"
                        CallLog.Calls.REJECTED_TYPE -> "perdida"
                        else -> "entrante"
                    }

                    val startDate = Date(dateMillis)
                    val endDate = Date(dateMillis + (duration * 1000L))
                    val sdf = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                    val dateStartIso = sdf.format(startDate)
                    val dateEndIso = sdf.format(endDate)

                    Log.d("CallLogReader", "Llamada Reciente: $number / $typeStr / $duration seg")
                    return CallMetadata(number, typeStr, dateStartIso, dateEndIso, duration)
                }
            }
        } catch (e: SecurityException) {
            Log.e("CallLogReader", "Permiso CallLog denegado: ${e.message}")
        } catch (e: Exception) {
            Log.e("CallLogReader", "Error leyendo CallLog: ${e.message}")
        }
        return null
    }
}
