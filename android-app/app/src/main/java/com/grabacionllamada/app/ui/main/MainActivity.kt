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
import com.grabacionllamada.app.services.CallMonitorService
import com.grabacionllamada.app.ui.login.LoginActivity
import com.grabacionllamada.app.utils.SessionManager
import com.grabacionllamada.app.utils.WorkerUtils
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

            CoroutineScope(Dispatchers.IO).launch {
                try {
                    // Copiar al cache de la app AHORA — los permisos SAF no llegan al WorkManager
                    val ext = contentResolver.getType(it)?.substringAfterLast('/') ?: "mp3"
                    val cacheFile = java.io.File(cacheDir, "manual_audio_${callId}.$ext")

                    contentResolver.openInputStream(it)?.use { input ->
                        java.io.FileOutputStream(cacheFile).use { output ->
                            input.copyTo(output)
                        }
                    }

                    if (!cacheFile.exists() || cacheFile.length() == 0L) {
                        withContext(Dispatchers.Main) {
                            Toast.makeText(this@MainActivity, "No se pudo leer el archivo seleccionado", Toast.LENGTH_LONG).show()
                        }
                        return@launch
                    }

                    // Guardar la ruta del cache (no el URI SAF)
                    val dao  = AppDatabase.getDatabase(this@MainActivity).callDao()
                    val call = dao.getCallById(callId)
                    if (call != null) {
                        dao.updateCall(call.copy(audioPath = "file://${cacheFile.absolutePath}"))
                        withContext(Dispatchers.Main) {
                            Toast.makeText(this@MainActivity, "Audio asociado. Subiendo...", Toast.LENGTH_SHORT).show()
                        }
                        WorkerUtils.enqueueSyncAudio(this@MainActivity)
                    }
                } catch (e: Exception) {
                    withContext(Dispatchers.Main) {
                        Toast.makeText(this@MainActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
                    }
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

        // Iniciar servicio de monitoreo en primer plano
        CallMonitorService.start(this)

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
        val permissions = mutableListOf(
            Manifest.permission.READ_PHONE_STATE,
            Manifest.permission.READ_CALL_LOG
        )
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.TIRAMISU) {
            permissions.add(Manifest.permission.READ_MEDIA_AUDIO)
            permissions.add(Manifest.permission.POST_NOTIFICATIONS)
        } else {
            permissions.add(Manifest.permission.READ_EXTERNAL_STORAGE)
        }
        val needed = permissions.filter {
            ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED
        }
        if (needed.isNotEmpty()) requestPermissionsLauncher.launch(needed.toTypedArray())
    }
}
