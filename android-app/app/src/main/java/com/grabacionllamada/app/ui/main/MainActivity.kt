package com.grabacionllamada.app.ui.main

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.grabacionllamada.app.databinding.ActivityMainBinding
import com.grabacionllamada.app.ui.login.LoginActivity
import com.grabacionllamada.app.utils.SessionManager
import com.grabacionllamada.app.data.local.AppDatabase
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.grabacionllamada.app.workers.SyncAudioWorker

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var sessionManager: SessionManager

    private val requestPermissionsLauncher = registerForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permissions ->
        val grantedAll = permissions.entries.all { it.value }
        if (grantedAll) {
            Toast.makeText(this, "Permisos otorgados. Escuchando eventos IDLE...", Toast.LENGTH_SHORT).show()
        } else {
            Toast.makeText(this, "Debe otorgar permisos para capturar la llamada MVP.", Toast.LENGTH_LONG).show()
        }
    }

    private var pendingCallIdForAudio: Int? = null

    private val selectAudioLauncher = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        uri?.let {
            val callId = pendingCallIdForAudio ?: return@let
            
            try {
                contentResolver.takePersistableUriPermission(it, Intent.FLAG_GRANT_READ_URI_PERMISSION)
            } catch (e: SecurityException) {
                // Si el file provider no lo permite, obvia el error.
            }

            CoroutineScope(Dispatchers.IO).launch {
                val db = AppDatabase.getDatabase(this@MainActivity)
                val dao = db.callDao()
                val call = dao.getNextCallNeedingAudio()
                
                if (call != null && call.id == callId) {
                    val updated = call.copy(audioPath = it.toString())
                    dao.updateCall(updated)
                    
                    withContext(Dispatchers.Main) {
                        Toast.makeText(this@MainActivity, "Audio asociado exitosamente. Subiendo...", Toast.LENGTH_SHORT).show()
                    }

                    // Encolar subida
                    val req = OneTimeWorkRequestBuilder<SyncAudioWorker>()
                        .setConstraints(Constraints.Builder().setRequiredNetworkType(NetworkType.CONNECTED).build())
                        .build()
                    WorkManager.getInstance(this@MainActivity).enqueue(req)
                }
            }
        } ?: run {
            Toast.makeText(this, "Selección cancelada", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        binding.tvWelcome.text = "Vendedor ID Activo: ${sessionManager.getVendedorId()}"

        binding.btnAttachAudio.setOnClickListener {
            CoroutineScope(Dispatchers.IO).launch {
                val call = AppDatabase.getDatabase(this@MainActivity).callDao().getNextCallNeedingAudio()
                withContext(Dispatchers.Main) {
                    if (call != null) {
                        pendingCallIdForAudio = call.id
                        Toast.makeText(this@MainActivity, "Selecciona el audio para la llamada al ${call.telefonoCliente}", Toast.LENGTH_LONG).show()
                        selectAudioLauncher.launch("audio/*")
                    } else {
                        Toast.makeText(this@MainActivity, "No hay llamadas pendientes de asociar audio", Toast.LENGTH_SHORT).show()
                    }
                }
            }
        }

        binding.btnLogout.setOnClickListener {
            sessionManager.clearSession()
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }

        checkAndRequestPermissions()
    }

    private fun checkAndRequestPermissions() {
        val permissions = mutableListOf(
            Manifest.permission.READ_PHONE_STATE,
            Manifest.permission.READ_CALL_LOG
        )
        
        val permissionsToRequest = permissions.filter {
            ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED
        }

        if (permissionsToRequest.isNotEmpty()) {
            requestPermissionsLauncher.launch(permissionsToRequest.toTypedArray())
        }
    }
}
