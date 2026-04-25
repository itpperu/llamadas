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
import com.grabacionllamada.app.data.local.AppDatabase
import com.grabacionllamada.app.databinding.FragmentLogBinding
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

        adapter = CallLogAdapter(emptyList())
        binding.rvCallLog.layoutManager = LinearLayoutManager(requireContext())
        binding.rvCallLog.addItemDecoration(DividerItemDecoration(requireContext(), DividerItemDecoration.VERTICAL))
        binding.rvCallLog.adapter = adapter

        loadLog()

        binding.btnDeleteLog.setOnClickListener {
            AlertDialog.Builder(requireContext())
                .setTitle("Eliminar log")
                .setMessage("¿Eliminar todos los registros locales? Esta acción no afecta los datos en el servidor.")
                .setPositiveButton("Eliminar") { _, _ -> deleteLog() }
                .setNegativeButton("Cancelar", null)
                .show()
        }
    }

    private fun loadLog() {
        lifecycleScope.launch {
            val calls = AppDatabase.getDatabase(requireContext()).callDao().getAllCalls()
            adapter.updateData(calls)
            binding.tvEmptyLog.visibility = if (calls.isEmpty()) View.VISIBLE else View.GONE
            binding.rvCallLog.visibility = if (calls.isEmpty()) View.GONE else View.VISIBLE
        }
    }

    private fun deleteLog() {
        lifecycleScope.launch {
            AppDatabase.getDatabase(requireContext()).callDao().deleteAll()
            adapter.updateData(emptyList())
            binding.tvEmptyLog.visibility = View.VISIBLE
            binding.rvCallLog.visibility = View.GONE
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
