package com.grabacionllamada.app.ui.main

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.grabacionllamada.app.databinding.FragmentConfigBinding
import com.grabacionllamada.app.utils.SessionManager

class ConfigFragment : Fragment() {

    private var _binding: FragmentConfigBinding? = null
    private val binding get() = _binding!!

    private lateinit var sessionManager: SessionManager

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentConfigBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        sessionManager = SessionManager(requireContext())

        val currentUrl = sessionManager.getServerUrl()
        binding.etServerUrl.setText(currentUrl)
        binding.tvCurrentUrl.text = "URL activa: $currentUrl"

        binding.tvVendedor.text = "Vendedor ID: ${sessionManager.getVendedorId()}"
        binding.tvDevice.text = "Dispositivo: ${sessionManager.getDeviceUuid() ?: "No registrado"}"

        binding.btnSaveUrl.setOnClickListener {
            val newUrl = binding.etServerUrl.text.toString().trim()
            if (newUrl.isEmpty()) {
                Toast.makeText(requireContext(), "La URL no puede estar vacía", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (!newUrl.startsWith("http://") && !newUrl.startsWith("https://")) {
                Toast.makeText(requireContext(), "La URL debe comenzar con http:// o https://", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            sessionManager.saveServerUrl(newUrl)
            binding.tvCurrentUrl.text = "URL activa: ${sessionManager.getServerUrl()}"
            Toast.makeText(requireContext(), "URL guardada correctamente", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
