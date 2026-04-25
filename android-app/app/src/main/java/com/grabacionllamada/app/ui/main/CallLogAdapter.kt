package com.grabacionllamada.app.ui.main

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.grabacionllamada.app.data.local.CallEntity
import com.grabacionllamada.app.databinding.ItemCallLogBinding

class CallLogAdapter(
    private var calls: List<CallEntity>,
    private val onAsociarClick: (CallEntity) -> Unit
) : RecyclerView.Adapter<CallLogAdapter.ViewHolder>() {

    class ViewHolder(val binding: ItemCallLogBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemCallLogBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val call = calls[position]
        holder.binding.apply {

            tvPhone.text = call.telefonoCliente

            tvType.text = when (call.tipo) {
                "saliente" -> "↑ Saliente"
                "entrante" -> "↓ Entrante"
                "perdida"  -> "✗ Perdida"
                else       -> call.tipo
            }

            tvTypeIcon.text = when (call.tipo) {
                "saliente" -> "📤"
                "entrante" -> "📥"
                "perdida"  -> "📵"
                else       -> "📞"
            }

            tvDate.text = call.fechaInicio.take(16).replace("T", " ")

            // Estado de sincronización
            val needsAudio = call.isMetadataSynced && !call.isAudioSynced && call.audioPath == null

            when {
                call.isMetadataSynced && call.isAudioSynced -> {
                    tvStatus.text = "✓ OK"
                    tvStatus.setTextColor(0xFF2E7D32.toInt())
                    tvStatus.setBackgroundResource(com.grabacionllamada.app.R.drawable.ic_status_bg)
                }
                needsAudio -> {
                    tvStatus.text = "Sin audio"
                    tvStatus.setTextColor(0xFFE65100.toInt())
                    tvStatus.setBackgroundColor(0xFFFFF3E0.toInt())
                }
                call.audioPath != null && !call.isAudioSynced -> {
                    tvStatus.text = "Subiendo..."
                    tvStatus.setTextColor(0xFF1565C0.toInt())
                    tvStatus.setBackgroundColor(0xFFE3F2FD.toInt())
                }
                else -> {
                    tvStatus.text = "Pendiente"
                    tvStatus.setTextColor(0xFF666666.toInt())
                    tvStatus.setBackgroundColor(0xFFEEEEEE.toInt())
                }
            }

            // Mostrar botón "Asociar" solo en llamadas sin audio
            btnAsociar.visibility = if (needsAudio) View.VISIBLE else View.GONE
            btnAsociar.setOnClickListener { onAsociarClick(call) }
        }
    }

    override fun getItemCount() = calls.size

    fun updateData(newCalls: List<CallEntity>) {
        calls = newCalls
        notifyDataSetChanged()
    }
}
