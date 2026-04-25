package com.grabacionllamada.app.ui.login

import android.app.AlertDialog
import android.content.Intent
import android.os.Bundle
import android.provider.Settings
import android.view.View
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import com.grabacionllamada.app.databinding.ActivityLoginBinding
import com.grabacionllamada.app.data.api.RetrofitClient
import com.grabacionllamada.app.data.repository.AuthRepository
import com.grabacionllamada.app.ui.main.MainActivity
import com.grabacionllamada.app.utils.SessionManager

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var viewModel: LoginViewModel
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        if (sessionManager.isLoggedIn()) {
            startMainActivity()
            return
        }

        val apiService = RetrofitClient.create(sessionManager)
        val authRepository = AuthRepository(apiService, sessionManager)

        viewModel = ViewModelProvider(this, object: ViewModelProvider.Factory {
            override fun <T : ViewModel> create(modelClass: Class<T>): T {
                return LoginViewModel(authRepository) as T
            }
        })[LoginViewModel::class.java]

        setupObservers()
        setupListeners()
        updateServerLabel()
    }

    private fun setupListeners() {
        binding.btnLogin.setOnClickListener {
            val user   = binding.etUsuario.text.toString().trim()
            val pass   = binding.etPassword.text.toString()
            val device = Settings.Secure.getString(contentResolver, Settings.Secure.ANDROID_ID)
            viewModel.login(user, pass, device)
        }

        binding.tvConfigServer.setOnClickListener {
            showServerDialog()
        }

        binding.tvVerUuid.setOnClickListener {
            val uuid = Settings.Secure.getString(contentResolver, Settings.Secure.ANDROID_ID)
            AlertDialog.Builder(this)
                .setTitle("UUID de este dispositivo")
                .setMessage("Entrega este código al administrador para que lo registre al crear tu usuario:\n\n$uuid")
                .setPositiveButton("Copiar") { _, _ ->
                    val clipboard = getSystemService(CLIPBOARD_SERVICE) as android.content.ClipboardManager
                    clipboard.setPrimaryClip(android.content.ClipData.newPlainText("UUID", uuid))
                    Toast.makeText(this, "UUID copiado al portapapeles", Toast.LENGTH_SHORT).show()
                }
                .setNegativeButton("Cerrar", null)
                .show()
        }
    }

    private fun updateServerLabel() {
        binding.tvServerUrl.text = "Servidor: ${sessionManager.getServerUrl()}"
    }

    private fun showServerDialog() {
        val input = EditText(this).apply {
            setText(sessionManager.getServerUrl())
            hint = "https://llamadas.tudominio.com/api/"
            setPadding(48, 32, 48, 32)
        }
        AlertDialog.Builder(this)
            .setTitle("Configurar servidor")
            .setView(input)
            .setPositiveButton("Guardar") { _, _ ->
                val url = input.text.toString().trim()
                if (url.startsWith("http")) {
                    sessionManager.saveServerUrl(url)
                    // Reiniciar la actividad para que Retrofit use la nueva URL
                    recreate()
                } else {
                    Toast.makeText(this, "URL inválida. Debe comenzar con http:// o https://", Toast.LENGTH_LONG).show()
                }
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun setupObservers() {
        viewModel.loginState.observe(this) { state ->
            when (state) {
                is LoginViewModel.LoginState.Loading -> {
                    binding.progressBar.visibility = View.VISIBLE
                    binding.btnLogin.isEnabled = false
                }
                is LoginViewModel.LoginState.Success -> {
                    binding.progressBar.visibility = View.GONE
                    binding.btnLogin.isEnabled = true
                    startMainActivity()
                }
                is LoginViewModel.LoginState.Error -> {
                    binding.progressBar.visibility = View.GONE
                    binding.btnLogin.isEnabled = true
                    Toast.makeText(this, state.message, Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    private fun startMainActivity() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }
}
