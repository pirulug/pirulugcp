<?php

namespace Pirulu\Core;

class Totp {
  private static $base32Chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

  public static function generateSecret($length = 16) {
    $secret = "";
    $charsLen = strlen(self::$base32Chars);
    for ($i = 0; $i < $length; $i++) {
      $secret .= self::$base32Chars[random_int(0, $charsLen - 1)];
    }
    return $secret;
  }

  public static function getCode($secret, $timeSlice = null) {
    if ($timeSlice === null) {
      $timeSlice = floor(time() / 30);
    }

    $secretKey = self::base32Decode($secret);
    $time = chr(0) . chr(0) . chr(0) . chr(0) . pack("N*", $timeSlice);
    $hmac = hash_hmac("sha1", $time, $secretKey, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hashPart = substr($hmac, $offset, 4);

    $value = unpack("N", $hashPart);
    $value = $value[1] & 0x7FFFFFFF;

    $modulo = 1000000;
    return str_pad((string)($value % $modulo), 6, "0", STR_PAD_LEFT);
  }

  public static function verifyCode($secret, $code, $discrepancy = 1) {
    $code = trim((string)$code);
    if (strlen($code) !== 6 || !ctype_digit($code)) {
      return false;
    }

    $currentTimeSlice = floor(time() / 30);
    for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
      $calculated = self::getCode($secret, $currentTimeSlice + $i);
      if (hash_equals($calculated, $code)) {
        return true;
      }
    }

    return false;
  }

  public static function getOtpAuthUrl($username, $secret, $issuer = "PiruluGCP") {
    return "otpauth://totp/" . rawurlencode($issuer) . ":" . rawurlencode($username) . "?secret=" . rawurlencode($secret) . "&issuer=" . rawurlencode($issuer);
  }

  private static function base32Decode($b32) {
    $b32 = strtoupper($b32);
    if (empty($b32)) {
      return "";
    }

    $b32 = preg_replace("/[^A-Z2-7]/", "", $b32);
    $binary = "";
    $buffer = 0;
    $bufferLength = 0;

    for ($i = 0; $i < strlen($b32); $i++) {
      $char = $b32[$i];
      $val = strpos(self::$base32Chars, $char);
      if ($val === false) {
        continue;
      }

      $buffer = ($buffer << 5) | $val;
      $bufferLength += 5;

      if ($bufferLength >= 8) {
        $bufferLength -= 8;
        $binary .= chr(($buffer >> $bufferLength) & 0xFF);
      }
    }

    return $binary;
  }
}
