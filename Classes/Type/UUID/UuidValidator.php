<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

final class UuidValidator
{
    // Pre-compiled regex patterns for maximum performance
    private const string PATTERN_STRICT = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    private const string PATTERN_LOOSE = '/^[0-9a-f]{32}$/i';
    private const string NIL_UUID = '00000000-0000-0000-0000-000000000000';
    private const string MAX_UUID = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    /**
     * Validates a UUID string with comprehensive checks
     */
    public static function validate(string $uuid, bool $strict = false): bool
    {
        return match ($strict) {
            true => self::validateStrict($uuid),
            false => self::validateLoose($uuid)
        };
    }

    /**
     * Validates and returns detailed UUID information
     */
    public static function analyze(string $uuid): UuidInfo
    {
        $normalizedUuid = self::normalize($uuid);

        if (!self::validateStrict($normalizedUuid)) {
            throw new UuidValidationException("Invalid UUID format", $uuid);
        }

        return new UuidInfo(
            version: self::extractVersion($normalizedUuid),
            variant: self::extractVariant($normalizedUuid),
            uuid: $normalizedUuid,
            isNil: $normalizedUuid === self::NIL_UUID
        );
    }

    /**
     * Fast validation for strict RFC 4122 format
     */
    public static function validateStrict(string $uuid): bool
    {
        if (strlen($uuid) !== 36) {
            return false;
        }

        if ($uuid[8] !== '-' || $uuid[13] !== '-' || $uuid[18] !== '-' || $uuid[23] !== '-') {
            return false;
        }

        return (bool) preg_match(self::PATTERN_STRICT, $uuid);
    }

    /**
     * Validates UUID without hyphens (32 hex characters)
     */
    public static function validateLoose(string $uuid): bool
    {
        return strlen($uuid) === 32 && (bool) preg_match(self::PATTERN_LOOSE, $uuid);
    }

    /**
     * Validates specific UUID version
     */
    public static function validateVersion(string $uuid, UuidVersion $version): bool
    {
        if (!self::validateStrict($uuid)) {
            return false;
        }

        return self::extractVersion($uuid) === $version;
    }

    /**
     * Checks if UUID is the nil UUID (all zeros)
     */
    public static function isNil(string $uuid): bool
    {
        return self::normalize($uuid) === self::NIL_UUID;
    }

    /**
     * Checks if UUID is the max UUID (all f's)
     */
    public static function isMax(string $uuid): bool
    {
        return strtolower(self::normalize($uuid)) === self::MAX_UUID;
    }

    /**
     * Normalizes UUID to standard format with hyphens
     */
    public static function normalize(string $uuid): string
    {
        $uuid = strtolower(str_replace('-', '', $uuid));

        if (strlen($uuid) !== 32) {
            throw new UuidValidationException("Invalid UUID length", $uuid);
        }

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 12, 4),
            substr($uuid, 16, 4),
            substr($uuid, 20, 12)
        );
    }

    /**
     * Extracts UUID version from the version nibble
     */
    private static function extractVersion(string $uuid): UuidVersion
    {
        $versionHex = $uuid[14];
        $version = hexdec($versionHex);

        return UuidVersion::tryFrom($version)
            ?? throw new UuidValidationException("Unknown UUID version: $version", $uuid);
    }

    /**
     * Extracts UUID variant from the variant bits
     */
    private static function extractVariant(string $uuid): UuidVariant
    {
        $variantHex = $uuid[19];
        $variantBits = hexdec($variantHex);

        return match (true) {
            ($variantBits & 0x8) === 0 => UuidVariant::NCS,
            ($variantBits & 0xC) === 0x8 => UuidVariant::RFC4122,
            ($variantBits & 0xE) === 0xC => UuidVariant::MICROSOFT,
            default => UuidVariant::RESERVED
        };
    }

    /**
     * Batch validation for multiple UUIDs (optimized for performance)
     */
    public static function validateBatch(array $uuids, bool $strict = true): array
    {
        $results = [];
        $pattern = $strict ? self::PATTERN_STRICT : self::PATTERN_LOOSE;
        $expectedLength = $strict ? 36 : 32;

        foreach ($uuids as $key => $uuid) {
            if (!is_string($uuid) || strlen($uuid) !== $expectedLength) {
                $results[$key] = false;
                continue;
            }

            if ($strict && ($uuid[8] !== '-' || $uuid[13] !== '-' || $uuid[18] !== '-' || $uuid[23] !== '-')) {
                $results[$key] = false;
                continue;
            }

            $results[$key] = (bool) preg_match($pattern, $uuid);
        }

        return $results;
    }

    /**
     * Filters array to only valid UUIDs
     */
    public static function filterValid(array $uuids, bool $strict = true): array
    {
        return array_filter($uuids, fn(string $uuid): bool => self::validate($uuid, $strict));
    }
}
