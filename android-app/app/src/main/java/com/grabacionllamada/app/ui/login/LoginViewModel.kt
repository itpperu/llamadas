package com.grabacionllamada.app.ui.login

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.grabacionllamada.app.data.repository.AuthRepository
import kotlinx.coroutines.launch

class LoginViewModel(private val repository: AuthRepository) : ViewModel() {

    private val _loginState = MutableLiveData<LoginState>()
    val loginState: LiveData<LoginState> = _loginState

    fun login(usuario: String, pass: String, deviceUuid: String) {
        if (usuario.isBlank() || pass.isBlank() || deviceUuid.isBlank()) {
            _loginState.value = LoginState.Error("Llena todos los campos")
            return
        }
        
        _loginState.value = LoginState.Loading
        
        viewModelScope.launch {
            val result = repository.login(usuario, pass, deviceUuid)
            if (result.isSuccess) {
                _loginState.value = LoginState.Success
            } else {
                _loginState.value = LoginState.Error(result.exceptionOrNull()?.message ?: "Error de red")
            }
        }
    }

    sealed class LoginState {
        object Loading : LoginState()
        object Success : LoginState()
        data class Error(val message: String) : LoginState()
    }
}
