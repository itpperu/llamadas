package com.grabacionllamada.app.utils

import android.content.Context
import androidx.work.Constraints
import androidx.work.ExistingWorkPolicy
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.OutOfQuotaPolicy
import androidx.work.WorkManager
import com.grabacionllamada.app.workers.SyncAudioWorker
import com.grabacionllamada.app.workers.SyncCallWorker

object WorkerUtils {

    private val networkConstraints = Constraints.Builder()
        .setRequiredNetworkType(NetworkType.CONNECTED)
        .build()

    fun enqueueSyncCall(context: Context) {
        WorkManager.getInstance(context).enqueueUniqueWork(
            "sync_calls",
            ExistingWorkPolicy.KEEP,
            OneTimeWorkRequestBuilder<SyncCallWorker>()
                .setConstraints(networkConstraints)
                .setExpedited(OutOfQuotaPolicy.RUN_AS_NON_EXPEDITED_WORK_REQUEST)
                .build()
        )
    }

    fun enqueueSyncAudio(context: Context) {
        WorkManager.getInstance(context).enqueueUniqueWork(
            "sync_audio",
            ExistingWorkPolicy.KEEP,
            OneTimeWorkRequestBuilder<SyncAudioWorker>()
                .setConstraints(networkConstraints)
                .setExpedited(OutOfQuotaPolicy.RUN_AS_NON_EXPEDITED_WORK_REQUEST)
                .build()
        )
    }
}
