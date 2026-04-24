package com.grabacionllamada.app.utils

object PhoneNumberNormalizer {
    fun normalize(rawNumber: String, defaultCountryCode: String = "+51"): String {
        return try {
            val number = rawNumber.replace(Regex("[^0-9+]"), "")
            
            if (number.startsWith("+")) {
                return number
            }
            
            if (number.length == 9 && !number.startsWith("+")) {
                return "$defaultCountryCode$number"
            }
            
            number
        } catch (e: Exception) {
            rawNumber
        }
    }
}
