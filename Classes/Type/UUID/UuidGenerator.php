<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

class UuidGenerator
{
    public static function generate(): string
    {
        $data = random_bytes(16);

        // Set version to 0100 (UUID v4)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // Set bits 6-7 to 10 (variant bits)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    public static function generateV7(): string
    {
        // Get current timestamp in milliseconds
        $timestamp = (int)(microtime(true) * 1000);

        // Create 48-bit timestamp (6 bytes)
        $timestampHex = str_pad(dechex($timestamp), 12, '0', STR_PAD_LEFT);
        $timestampBytes = hex2bin($timestampHex);

        // Generate 10 bytes of random data
        $randomBytes = random_bytes(10);

        // Combine timestamp and random data
        $data = $timestampBytes . $randomBytes;

        // Set version to 0111 (UUID v7)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x70);

        // Set variant bits to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }
}
