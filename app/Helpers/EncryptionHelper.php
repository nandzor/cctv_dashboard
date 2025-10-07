<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper {
    public static function encrypt(string $value): string {
        $method = env('ENCRYPTION_METHOD', 'AES-256-CBC');

        if ($method === 'AES-256-CBC') {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    public static function decrypt(string $value): string {
        $method = env('ENCRYPTION_METHOD', 'AES-256-CBC');

        if ($method === 'AES-256-CBC') {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public static function shouldEncryptDeviceCredentials(): bool {
        return env('ENCRYPT_DEVICE_CREDENTIALS', false);
    }

    public static function shouldEncryptStreamCredentials(): bool {
        return env('ENCRYPT_STREAM_CREDENTIALS', false);
    }
}
