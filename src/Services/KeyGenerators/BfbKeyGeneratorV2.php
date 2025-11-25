<?php

namespace OnaOnbir\OOLicense\Services\KeyGenerators;

use OnaOnbir\OOLicense\Models\ProjectUser;

class BfbKeyGeneratorV2 extends AbstractKeyGenerator
{
    /**
     * Get the version identifier
     */
    public function getVersion(): string
    {
        return 'v2';
    }

    /**
     * Get the key format pattern
     */
    public function getKeyFormat(): string
    {
        return 'BFB2-{BASE64_PAYLOAD}.{SIGNATURE}';
    }

    /**
     * Generate a V2 license key with encoded payload
     * Format: BFB2-{BASE64_ENCODED_PAYLOAD}.{HMAC_SIGNATURE}
     */
    public function generate(ProjectUser $user, array $options = []): array
    {
        // 1. Create payload with project and user information
        $payload = [
            'project_id' => $this->project->id,
            'project_slug' => $this->project->slug,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'version' => 'v2',
            'timestamp' => now()->timestamp,
            'random' => $this->randomHex(16),
        ];

        // 2. Encode payload to base64
        $encodedPayload = base64_encode(json_encode($payload));

        // 3. Generate HMAC-SHA256 signature
        $signature = $this->hmac($encodedPayload, 'sha256');

        // Use first 16 characters of signature for shorter key
        $shortSignature = substr($signature, 0, 16);

        // 4. Format key: BFB2-{PAYLOAD}.{SIGNATURE}
        $key = "BFB2-{$encodedPayload}.{$shortSignature}";

        // 5. Build metadata
        $metadata = [
            'algorithm' => 'hmac-sha256',
            'payload' => $payload,
            'signature_length' => 16,
            'full_signature' => $signature,
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
     * Validate V2 key format and signature
     */
    public function validate(string $key, array $deviceInfo): bool
    {
        // Check pattern: BFB2-{BASE64}.{HEX16}
        $pattern = '/^BFB2-([A-Za-z0-9+\/=]+)\.([a-f0-9]{16})$/';

        if (!preg_match($pattern, $key, $matches)) {
            return false;
        }

        $encodedPayload = $matches[1];
        $providedSignature = $matches[2];

        // Verify signature
        $expectedSignature = substr(
            $this->hmac($encodedPayload, 'sha256'),
            0,
            16
        );

        // Use timing-safe comparison
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return false;
        }

        // Decode and validate payload structure
        try {
            $payload = json_decode(base64_decode($encodedPayload), true);

            if (!$payload || !isset($payload['version']) || $payload['version'] !== 'v2') {
                return false;
            }

            // Validate project_id matches current project
            if (isset($payload['project_id']) && $payload['project_id'] !== $this->project->id) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Decode V2 key and extract payload information
     */
    public function decode(string $key): ?array
    {
        $pattern = '/^BFB2-([A-Za-z0-9+\/=]+)\.([a-f0-9]{16})$/';

        if (!preg_match($pattern, $key, $matches)) {
            return null;
        }

        $encodedPayload = $matches[1];
        $signature = $matches[2];

        try {
            $payload = json_decode(base64_decode($encodedPayload), true);

            if (!$payload) {
                return null;
            }

            return [
                'version' => 'v2',
                'payload' => $payload,
                'signature' => $signature,
                'format' => $this->getKeyFormat(),
                'project_id' => $payload['project_id'] ?? null,
                'project_slug' => $payload['project_slug'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
                'user_email' => $payload['user_email'] ?? null,
                'timestamp' => $payload['timestamp'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
