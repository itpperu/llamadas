package com.grabacionllamada.app.utils

import android.content.Context
import android.media.MediaCodec
import android.media.MediaExtractor
import android.media.MediaFormat
import android.media.MediaMuxer
import android.util.Log
import java.io.File
import java.nio.ByteBuffer

object AudioCompressor {

    private const val TAG = "AudioCompressor"
    private const val TARGET_BITRATE    = 32_000   // 32 kbps — suficiente para voz
    private const val THRESHOLD_BYTES   = 1_048_576 // 1 MB — comprimir solo si supera esto
    private const val TIMEOUT_US        = 10_000L  // 10 ms de timeout para buffers

    /**
     * Comprime el archivo de audio si supera el umbral.
     * Devuelve la ruta del archivo comprimido (o la original si ya era pequeño o falló).
     */
    fun compress(context: Context, inputPath: String): String {
        val inputFile = File(inputPath)

        if (!inputFile.exists()) {
            Log.w(TAG, "Archivo no encontrado: $inputPath")
            return inputPath
        }

        val sizekb = inputFile.length() / 1024
        if (inputFile.length() < THRESHOLD_BYTES) {
            Log.d(TAG, "Archivo pequeño (${sizekb}KB) — sin comprimir")
            return inputPath
        }

        Log.i(TAG, "Iniciando compresión: ${inputFile.name} (${sizekb}KB)")
        val outputFile = File(context.cacheDir, "cmp_${inputFile.nameWithoutExtension}.m4a")

        return try {
            transcode(inputPath, outputFile.absolutePath)
            val compressedKb = outputFile.length() / 1024
            val ratio = if (sizekb > 0) (outputFile.length() * 100 / inputFile.length()) else 100
            Log.i(TAG, "Compresión completada: ${compressedKb}KB (${ratio}% del original)")
            outputFile.absolutePath
        } catch (e: Exception) {
            Log.e(TAG, "Error comprimiendo audio: ${e.message} — usando archivo original")
            outputFile.delete()
            inputPath
        }
    }

    private fun transcode(inputPath: String, outputPath: String) {
        val extractor = MediaExtractor()
        extractor.setDataSource(inputPath)

        // Encontrar la pista de audio
        var audioTrack = -1
        var inputFormat: MediaFormat? = null
        for (i in 0 until extractor.trackCount) {
            val fmt = extractor.getTrackFormat(i)
            val mime = fmt.getString(MediaFormat.KEY_MIME) ?: continue
            if (mime.startsWith("audio/")) {
                audioTrack = i
                inputFormat = fmt
                break
            }
        }
        requireNotNull(inputFormat) { "No se encontró pista de audio en el archivo" }
        extractor.selectTrack(audioTrack)

        val sourceMime = inputFormat.getString(MediaFormat.KEY_MIME)!!
        val sampleRate = inputFormat.getInteger(MediaFormat.KEY_SAMPLE_RATE)
        val channelCount = try { inputFormat.getInteger(MediaFormat.KEY_CHANNEL_COUNT) } catch (e: Exception) { 1 }

        // Configurar decodificador
        val decoder = MediaCodec.createDecoderByType(sourceMime)
        decoder.configure(inputFormat, null, null, 0)
        decoder.start()

        // Configurar codificador AAC — mono para reducir tamaño
        val outputFormat = MediaFormat.createAudioFormat(MediaFormat.MIMETYPE_AUDIO_AAC, sampleRate, 1)
        outputFormat.setInteger(MediaFormat.KEY_BIT_RATE, TARGET_BITRATE)
        outputFormat.setInteger(MediaFormat.KEY_AAC_PROFILE, android.media.MediaCodecInfo.CodecProfileLevel.AACObjectLC)
        outputFormat.setInteger(MediaFormat.KEY_MAX_INPUT_SIZE, 16384)

        val encoder = MediaCodec.createEncoderByType(MediaFormat.MIMETYPE_AUDIO_AAC)
        encoder.configure(outputFormat, null, null, MediaCodec.CONFIGURE_FLAG_ENCODE)
        encoder.start()

        val muxer = MediaMuxer(outputPath, MediaMuxer.OutputFormat.MUXER_OUTPUT_MPEG_4)
        var muxerTrackIndex = -1
        var muxerStarted = false

        val decoderInfo = MediaCodec.BufferInfo()
        val encoderInfo = MediaCodec.BufferInfo()

        var extractorDone  = false
        var decoderDone    = false
        var encoderDone    = false
        var pendingPts     = 0L

        try {
            while (!encoderDone) {

                // --- Alimentar decodificador desde extractor ---
                if (!extractorDone) {
                    val inBufIdx = decoder.dequeueInputBuffer(TIMEOUT_US)
                    if (inBufIdx >= 0) {
                        val buf = decoder.getInputBuffer(inBufIdx)!!
                        val sampleSize = extractor.readSampleData(buf, 0)
                        if (sampleSize < 0) {
                            decoder.queueInputBuffer(inBufIdx, 0, 0, 0, MediaCodec.BUFFER_FLAG_END_OF_STREAM)
                            extractorDone = true
                        } else {
                            decoder.queueInputBuffer(inBufIdx, 0, sampleSize, extractor.sampleTime, 0)
                            extractor.advance()
                        }
                    }
                }

                // --- Obtener PCM del decodificador y darlo al codificador ---
                if (!decoderDone) {
                    val outBufIdx = decoder.dequeueOutputBuffer(decoderInfo, TIMEOUT_US)
                    when {
                        outBufIdx >= 0 -> {
                            val pcm = decoder.getOutputBuffer(outBufIdx)!!
                            val isEos = (decoderInfo.flags and MediaCodec.BUFFER_FLAG_END_OF_STREAM) != 0

                            // Mezclar a mono si la fuente es estéreo
                            val monoData = if (channelCount > 1) mixToMono(pcm, decoderInfo.size) else null

                            // Dar PCM al codificador
                            val encInIdx = encoder.dequeueInputBuffer(TIMEOUT_US)
                            if (encInIdx >= 0) {
                                val encBuf = encoder.getInputBuffer(encInIdx)!!
                                encBuf.clear()
                                if (monoData != null) {
                                    encBuf.put(monoData)
                                } else {
                                    pcm.position(decoderInfo.offset)
                                    pcm.limit(decoderInfo.offset + decoderInfo.size)
                                    encBuf.put(pcm)
                                }
                                val size = if (monoData != null) monoData.size else decoderInfo.size
                                val flags = if (isEos) MediaCodec.BUFFER_FLAG_END_OF_STREAM else 0
                                encoder.queueInputBuffer(encInIdx, 0, size, decoderInfo.presentationTimeUs, flags)
                                pendingPts = decoderInfo.presentationTimeUs
                            }

                            decoder.releaseOutputBuffer(outBufIdx, false)
                            if (isEos) decoderDone = true
                        }
                    }
                }

                // --- Leer salida del codificador y escribir al muxer ---
                val encOutIdx = encoder.dequeueOutputBuffer(encoderInfo, TIMEOUT_US)
                when {
                    encOutIdx == MediaCodec.INFO_OUTPUT_FORMAT_CHANGED -> {
                        if (!muxerStarted) {
                            muxerTrackIndex = muxer.addTrack(encoder.outputFormat)
                            muxer.start()
                            muxerStarted = true
                        }
                    }
                    encOutIdx >= 0 -> {
                        val encOut = encoder.getOutputBuffer(encOutIdx)!!
                        if (muxerStarted && encoderInfo.size > 0 &&
                            (encoderInfo.flags and MediaCodec.BUFFER_FLAG_CODEC_CONFIG) == 0) {
                            encOut.position(encoderInfo.offset)
                            encOut.limit(encoderInfo.offset + encoderInfo.size)
                            muxer.writeSampleData(muxerTrackIndex, encOut, encoderInfo)
                        }
                        encoder.releaseOutputBuffer(encOutIdx, false)
                        if ((encoderInfo.flags and MediaCodec.BUFFER_FLAG_END_OF_STREAM) != 0) {
                            encoderDone = true
                        }
                    }
                }
            }
        } finally {
            decoder.stop(); decoder.release()
            encoder.stop(); encoder.release()
            if (muxerStarted) muxer.stop()
            muxer.release()
            extractor.release()
        }
    }

    /**
     * Mezcla audio estéreo intercalado (short 16-bit) a mono sumando los canales.
     */
    private fun mixToMono(stereo: ByteBuffer, size: Int): ByteArray {
        val shorts = size / 2
        val monoShorts = shorts / 2
        val mono = ByteArray(monoShorts * 2)
        stereo.rewind()
        for (i in 0 until monoShorts) {
            val left  = stereo.short.toInt()
            val right = stereo.short.toInt()
            val mixed = ((left + right) / 2).toShort()
            mono[i * 2]     = (mixed.toInt() and 0xFF).toByte()
            mono[i * 2 + 1] = (mixed.toInt() shr 8 and 0xFF).toByte()
        }
        return mono
    }
}
