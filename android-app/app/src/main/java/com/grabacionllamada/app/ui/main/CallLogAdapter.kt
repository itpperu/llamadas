package com.grabacionllamada.app.ui.main

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.grabacionllamada.app.data.local.CallEntity
import com.grabacionllamada.app.databinding.ItemCallLogBinding

class CallLogAdapter(private var calls: List<CallEntity>) :
    RecyclerView.Adapter<CallLogAdapter.ViewHolder>() {

    class ViewHolder(val binding: ItemCallLogBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemCallLogBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val call = calls[position]
        holder.binding.apply {

            tvPhone.text = call.telefonoCliente

            val tipoLabel = when (call.tipo) {
                "saliente" -> "↑ Saliente"
                "entrante" -> "↓ Entrante"
                "perdida"  -> "✗ Perdida"
                else       -> call.tipo
            }
            tvType.text = tipoLabel

            val typeIcon = when (call.tipo) {
                "saliente" -> "📤"
                "entrante" -> "📥"
                "perdida"  -> "📵"
                else       -> "📞"
            }
            tvTypeIcon.text = typeIcon

            // Mostrar fecha legible
            tvDate.text = call.fechaInicio.take(10)

            // Estado de sincronización
            when {
                call.isMetadataSynced && call.isAudioSynced -> {
                    tvStatus.text = "✓ OK"
                    tvStatus.setTextColor(0xFF2E7D32.toInt())
                    tvStatus.setBackgroundResource(com.grabacionllamada.app.R.drawable.ic_status_bg)
                }
                call.isMetadataSynced -> {
                    tvStatus.text = "Audio pendiente"
                    tvStatus.setTextColor(0xFFE65100.toInt())
                    tvStatus.setBackgroundColor(0xFFFFF3E0.toInt())
                }
                else -> {
                    tvStatus.text = "Pendiente"
                    tvStatus.setTextColor(0xFF666666.toInt())
                    tvStatus.setBackgroundColor(0xFFEEEEEE.toInt())
                }
            }
        }
    }

    override fun getItemCount() = calls.size

    fun updateData(newCalls: List<CallEntity>) {
        calls = newCalls
        notifyDataSetChanged()
    }
}
