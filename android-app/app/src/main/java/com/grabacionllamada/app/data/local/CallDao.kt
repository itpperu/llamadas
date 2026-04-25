package com.grabacionllamada.app.data.local

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.Query
import androidx.room.Update

@Dao
interface CallDao {
    @Insert
    suspend fun insertCall(call: CallEntity): Long

    @Update
    suspend fun updateCall(call: CallEntity)

    @Query("SELECT * FROM calls WHERE isMetadataSynced = 0")
    suspend fun getUnsyncedMetadataCalls(): List<CallEntity>

    @Query("SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NOT NULL")
    suspend fun getUnsyncedAudioCalls(): List<CallEntity>

    @Query("SELECT * FROM calls WHERE isMetadataSynced = 1 AND isAudioSynced = 0 AND audioPath IS NULL LIMIT 1")
    suspend fun getNextCallNeedingAudio(): CallEntity?

    @Query("SELECT * FROM calls WHERE id = :id LIMIT 1")
    suspend fun getCallById(id: Int): CallEntity?

    @Query("SELECT * FROM calls ORDER BY id DESC")
    suspend fun getAllCalls(): List<CallEntity>

    @Query("DELETE FROM calls")
    suspend fun deleteAll()
}
