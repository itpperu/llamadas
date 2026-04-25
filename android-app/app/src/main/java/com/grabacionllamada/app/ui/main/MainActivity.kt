package com.grabacionllamada.app.ui.main

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.google.android.material.tabs.TabLayoutMediator
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.databinding.ActivityMainBinding
import com.grabacionllamada.app.ui.login.LoginActivity
import com.grabacionllamada.app.utils.SessionManager
import com.grabacionllamada.app.workers.SyncAudioWorker
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var sessionManager: SessionManager

    private val requestPermissionsLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        if (!permissions.entries.all { it.value }) {
            Toast.makeText(this, "Debe otorgar permisos para capturar llamadas.", Toast.LENGTH_LONG).show()
        }
    }

    private var pendingCallIdForAudio: Int? = null

    private val selectAudioLauncher = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        uri?.let {
            val callId = pendingCallIdForAudio ?: return@let
            try {
                contentResolver.takePersistableUriPermission(it, Intent.FLAG_GRANT_READ_URI_PERMISSION)
            } catch (_: SecurityException) {}

            CoroutineScope(Dispatchers.IO).launch {
                val dao = AppDatabase.getDatabase(this@MainActivity).callDao()
                val call = dao.getNextCallNeedingAudio()
                if (call != null && call.id == callId) {
                    dao.updateCall(call.copy(audioPath = it.toString()))
                    withContext(Dispatchers.Main) {
                        Toast.makeText(this@MainActivity, "Audio asociado. Subiendo...", Toast.LENGTH_SHORT).show()
                    }
                    WorkManager.getInstance(this@MainActivity).enqueue(
                        OneTimeWorkRequestBuilder<SyncAudioWorker>()
                            .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
                            .build()
                    )
                }
            }
        } ?: Toast.makeText(this, "Selección cancelada", Toast.LENGTH_SHORT).show()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)
        binding.tvWelcome.text = "Vendedor: ${sessionManager.getVendedorId()}"

        // ViewPager2 + Tabs
        val adapter = MainPagerAdapter(this)
        binding.viewPager.adapter = adapter
        TabLayoutMediator(binding.tabLayout, binding.viewPager) { tab, position ->
            tab.text = when (position) {
                0    -> "📋 Log de Llamadas"
                else -> "⚙️ Configuración"
            }
        }.attach()

        binding.btnLogout.setOnClickListener {
            sessionManager.clearSession()
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }

        checkAndRequestPermissions()
    }

    // Expuesto para que LogFragment pueda lanzar el selector de audio
    fun launchAudioPicker(callId: Int) {
        pendingCallIdForAudio = callId
        selectAudioLauncher.launch("audio/*")
    }

    private fun checkAndRequestPermissions() {
        val needed = listOf(Manifest.permission.READ_PHONE_STATE, Manifest.permission.READ_CALL_LOG)
            .filter { ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED }
        if (needed.isNotEmpty()) requestPermissionsLauncher.launch(needed.toTypedArray())
    }
}
