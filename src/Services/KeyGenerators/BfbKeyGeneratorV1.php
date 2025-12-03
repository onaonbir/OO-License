<?php

namespace OnaOnbir\OOLicense\Services\KeyGenerators;

use OnaOnbir\OOLicense\Models\ProjectUser;

class BfbKeyGeneratorV1 extends AbstractKeyGenerator
{
    /**
     * Get the version identifier
     */
    public function getVersion(): string
    {
        return 'v1';
    }

    /**
     * Get the key format pattern
     */
    public function getKeyFormat(): string
    {
        return 'BFB-XXXX-XXXX-XXXX-XXXX';
    }

    /**
     * Generate a V1 license key
     * Format: BFB-XXXXXX-XXXXXX-XXXXXX-XXXXXX
     */
    public function generate(ProjectUser $user, array $options = []): array
    {
        // 1. Generate 32 random bytes
        $randomBytes = random_bytes(32);

        // 2. Create SHA256 hash
        $hash = $this->hash($randomBytes, 'sha256');

        // 3. Extract segments (6 chars each)
        $segments = [
            strtoupper(substr($hash, 0, 6)),
            strtoupper(substr($hash, 6, 6)),
            strtoupper(substr($hash, 12, 6)),
            strtoupper(substr($hash, 18, 6)),
        ];

        // 4. Format key
        $key = 'BFB-'.implode('-', $segments);

        // 5. Build metadata
        $metadata = [
            'algorithm' => 'sha256',
            'segments' => 4,
            'segment_length' => 6,
            'hash' => $hash,
            'generated_at' => now()->toIso8601String(),
        ];

        return [
            'key' => $key,
            'version' => $this->getVersion(),
            'format' => $this->getKeyFormat(),
            'metadata' => $metadata,
        ];
    }

    /**
     * Validate V1 key format
     */
    public function validate(string $key, array $deviceInfo): bool
    {
        // Check pattern: BFB-XXXXXX-XXXXXX-XXXXXX-XXXXXX
        $pattern = '/^BFB-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/';

        if (! preg_match($pattern, $key)) {
            return false;
        }

        return true;
    }

    /**
     * Decode V1 key and extract information
     */
    public function decode(string $key): ?array
    {
        if (! $this->validate($key, [])) {
            return null;
        }

        // Split key into parts
        $parts = explode('-', $key);

        return [
            'version' => 'v1',
            'prefix' => $parts[0], // BFB
            'segments' => [
                $parts[1],
                $parts[2],
                $parts[3],
                $parts[4],
            ],
            'format' => $this->getKeyFormat(),
        ];
    }
}
