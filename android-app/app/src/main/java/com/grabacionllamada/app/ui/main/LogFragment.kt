package com.grabacionllamada.app.ui.main

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.DividerItemDecoration
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.work.Constraints
import androidx.work.NetworkType
import androidx.work.OneTimeWorkRequestBuilder
import androidx.work.WorkManager
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.databinding.FragmentLogBinding
import com.grabacionllamada.app.utils.WorkerUtils
import com.grabacionllamada.app.workers.SyncCallWorker
import com.grabacionllamada.app.workers.SyncAudioWorker
import kotlinx.coroutines.launch

class LogFragment : Fragment() {

    private var _binding: FragmentLogBinding? = null
    private val binding get() = _binding!!
    private lateinit var adapter: CallLogAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLogBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = CallLogAdapter(emptyList()) { call ->
            // Botón "Asociar" de una fila específica
            (activity as? MainActivity)?.launchAudioPicker(call.id)
        }
        binding.rvCallLog.layoutManager = LinearLayoutManager(requireContext())
        binding.rvCallLog.addItemDecoration(
            DividerItemDecoration(requireContext(), DividerItemDecoration.VERTICAL)
        )
        binding.rvCallLog.adapter = adapter

        loadLog()

        // Sincronizar llamadas pendientes manualmente
        binding.btnSyncNow.setOnClickListener {
            WorkerUtils.enqueueSyncCall(requireContext())
            WorkerUtils.enqueueSyncAudio(requireContext())

            binding.tvSyncStatus.text = "Sincronización iniciada..."
            binding.btnSyncNow.isEnabled = false

            binding.root.postDelayed({
                binding.btnSyncNow.isEnabled = true
                binding.tvSyncStatus.text = ""
                loadLog()
            }, 3000)
        }

        // El botón "Asociar" ahora está en cada fila individualmente
        binding.btnAttachAudio.visibility = View.GONE

        // Eliminar log local
        binding.btnDeleteLog.setOnClickListener {
            AlertDialog.Builder(requireContext())
                .setTitle("Eliminar log")
                .setMessage("¿Eliminar todos los registros locales? No afecta los datos en el servidor.")
                .setPositiveButton("Eliminar") { _, _ -> deleteLog() }
                .setNegativeButton("Cancelar", null)
                .show()
        }
    }

    private fun loadLog() {
        lifecycleScope.launch {
            val calls = AppDatabase.getDatabase(requireContext()).callDao().getAllCalls()
            adapter.updateData(calls)

            val pendingCount = calls.count { !it.isMetadataSynced }
            val audioCount = calls.count { it.isMetadataSynced && !it.isAudioSynced && it.audioPath == null }

            binding.tvSyncStatus.text = when {
                calls.isEmpty() -> ""
                pendingCount > 0 -> "⚠ $pendingCount llamada(s) pendiente(s) de sincronizar"
                audioCount > 0  -> "🎙 $audioCount llamada(s) necesitan audio"
                else            -> "✓ Todo sincronizado"
            }

            binding.tvEmptyLog.visibility = if (calls.isEmpty()) View.VISIBLE else View.GONE
            binding.rvCallLog.visibility  = if (calls.isEmpty()) View.GONE  else View.VISIBLE
        }
    }

    private fun deleteLog() {
        lifecycleScope.launch {
            AppDatabase.getDatabase(requireContext()).callDao().deleteAll()
            adapter.updateData(emptyList())
            binding.tvEmptyLog.visibility = View.VISIBLE
            binding.rvCallLog.visibility  = View.GONE
            binding.tvSyncStatus.text     = ""
            Toast.makeText(requireContext(), "Log eliminado", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onResume() {
        super.onResume()
        loadLog()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
