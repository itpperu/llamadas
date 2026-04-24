package com.grabacionllamada.app.ui.login

import android.content.Intent
import android.os.Bundle
import android.view.View
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
    }

    private fun setupListeners() {
        binding.btnLogin.setOnClickListener {
            val user = binding.etUsuario.text.toString()
            val pass = binding.etPassword.text.toString()
            val device = binding.etDeviceUuid.text.toString()
            viewModel.login(user, pass, device)
        }
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
