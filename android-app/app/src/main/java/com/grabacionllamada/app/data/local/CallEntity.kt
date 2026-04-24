package com.grabacionllamada.app.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "calls")
data class CallEntity(
    @PrimaryKey(autoGenerate = true) val id: Int = 0,
    val telefonoCliente: String,
    val tipo: String, // "entrante", "saliente", "perdida"
    val fechaInicio: String, // ISO 8601
    val fechaFin: String, // ISO 8601
    val duracionSegundos: Int,
    val isMetadataSynced: Boolean = false,
    val isAudioSynced: Boolean = false,
    val audioPath: String? = null, // Ruta local del archivo asociado
    val backendCallId: Int? = null // ID remoto que retorna Laravel al sincronizar
)
