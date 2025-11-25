<?php

namespace OnaOnbir\OOLicense\Services\KeyGenerators;

use OnaOnbir\OOLicense\Models\Project;
use OnaOnbir\OOLicense\Models\ProjectUser;

abstract class AbstractKeyGenerator
{
    protected Project $project;
    protected array $options;

    public function __construct(Project $project, array $options = [])
    {
        $this->project = $project;
        $this->options = $options;
    }

    /**
     * Get the version identifier of this generator
     */
    abstract public function getVersion(): string;

    /**
     * Get the human-readable key format
     */
    abstract public function getKeyFormat(): string;

    /**
     * Generate a new license key
     *
     * @param ProjectUser $user
     * @param array $options
     * @return array ['key' => string, 'version' => string, 'format' => string, 'metadata' => array]
     */
    abstract public function generate(ProjectUser $user, array $options = []): array;

    /**
     * Validate key format and signature
     *
     * @param string $key
     * @param array $deviceInfo
     * @return bool
     */
    abstract public function validate(string $key, array $deviceInfo): bool;

    /**
     * Decode and extract information from key
     *
     * @param string $key
     * @return array|null
     */
    abstract public function decode(string $key): ?array;

    /**
     * Get the project's secret key
     */
    protected function getSecretKey(): string
    {
        return $this->project->secret_key;
    }

    /**
     * Get project encryption key
     */
    protected function getEncryptionKey(): string
    {
        return $this->project->encryption_key;
    }

    /**
     * Hash data using specified algorithm
     */
    protected function hash(string $data, string $algorithm = 'sha256'): string
    {
        return hash($algorithm, $data);
    }

    /**
     * Generate HMAC signature
     */
    protected function hmac(string $data, string $algorithm = 'sha256'): string
    {
        return hash_hmac($algorithm, $data, $this->getSecretKey());
    }

    /**
     * Generate random bytes as hex string
     */
    protected function randomHex(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Get option value with default
     */
    protected function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Check if key is expired based on expiry date
     */
    protected function isExpired(?string $expiryDate): bool
    {
        if (!$expiryDate) {
            return false;
        }

        return now()->isAfter($expiryDate);
    }
}
