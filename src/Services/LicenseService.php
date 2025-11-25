<?php

namespace OnaOnbir\OOLicense\Services;

use Exception;
use OnaOnbir\OOLicense\Exceptions\DeviceMismatchException;
use OnaOnbir\OOLicense\Exceptions\DeviceNotActivatedException;
use OnaOnbir\OOLicense\Exceptions\InvalidKeyException;
use OnaOnbir\OOLicense\Exceptions\KeyInactiveException;
use OnaOnbir\OOLicense\Exceptions\LicenseExpiredException;
use OnaOnbir\OOLicense\Exceptions\MaxDevicesReachedException;
use OnaOnbir\OOLicense\Models\Project;
use OnaOnbir\OOLicense\Models\ProjectUser;
use OnaOnbir\OOLicense\Models\ProjectUserKey;

class LicenseService
{
    public function __construct(
        protected KeyGeneratorRegistry $registry
    ) {}

    /**
     * Generate a new license key
     */
    public function generateKey(Project $project, ProjectUser $user, array $options = []): array
    {
        // Get the key generator for this project
        $generator = $this->registry->make($project->key_generator_class, $project);

        // Generate license key
        $keyData = $generator->generate($user, $options);

        // Create license key record
        $licenseKey = $user->keys()->create([
            'key' => $keyData['key'],
            'key_version' => $keyData['version'],
            'key_format' => $keyData['format'],
            'key_metadata' => $keyData['metadata'],
            'start_date' => $options['start_date'] ?? null,
            'expiry_date' => $options['expiry_date'] ?? null,
            'max_devices' => $options['max_devices'] ?? $project->max_devices,
            'features' => $options['features'] ?? $project->features,
            'is_active' => true,
        ]);

        return [
            'key' => $keyData['key'],
            'license' => $licenseKey,
            'metadata' => $keyData['metadata'],
        ];
    }

    /**
     * Activate a license key on a device
     */
    public function activateKey(
        string $licenseKey,
        string $deviceId,
        string $email,
        string $encryptedDeviceInfo,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        // Find license key
        $key = ProjectUserKey::where('key', $licenseKey)->first();

        if (!$key) {
            throw new InvalidKeyException('License key not found');
        }

        // Get project
        $project = $key->projectUser->project;

        // Decrypt device info
        try {
            $deviceInfo = $this->decryptDeviceInfo($encryptedDeviceInfo, $project->secret_key);
        } catch (Exception $e) {
            throw new DeviceMismatchException('Failed to decrypt device information: ' . $e->getMessage());
        }

        // Verify device ID matches
        if (!isset($deviceInfo['deviceId']) || $deviceInfo['deviceId'] !== $deviceId) {
            throw new DeviceMismatchException('Device ID mismatch');
        }

        // Verify email matches
        if ($key->projectUser->email !== $email) {
            throw new DeviceMismatchException('Email does not match license');
        }

        // Check if key is active
        if (!$key->is_active) {
            throw new KeyInactiveException();
        }

        // Check if expired
        if ($key->isExpired()) {
            throw new LicenseExpiredException();
        }

        // Validate key format
        $generator = $this->registry->make($project->key_generator_class, $project);
        if (!$generator->validate($licenseKey, $deviceInfo)) {
            throw new InvalidKeyException('Invalid key format');
        }

        // Check if device already activated
        $existingActivation = $key->activations()
            ->where('device_id', $deviceId)
            ->first();

        if ($existingActivation) {
            // Log re-activation
            $existingActivation->validations()->create([
                'validation_type' => 'activate',
                'device_info' => $deviceInfo,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'request_data' => ['email' => $email],
                'response_status' => 'success',
                'validated_at' => now(),
            ]);

            return [
                'success' => true,
                'isValid' => true,
                'expiryDate' => $key->expiry_date?->toIso8601String(),
                'features' => $key->features ?? [],
                'maxDevices' => $key->max_devices,
                'message' => 'License re-activated successfully',
            ];
        }

        // Check device limit
        if ($key->hasReachedMaxDevices()) {
            throw new MaxDevicesReachedException($key->max_devices);
        }

        // Create new activation
        $activation = $key->activations()->create([
            'device_id' => $deviceId,
            'device_info' => $deviceInfo,
            'activated_at' => now(),
            'is_active' => true,
        ]);

        // Log activation
        $activation->validations()->create([
            'validation_type' => 'activate',
            'device_info' => $deviceInfo,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_data' => ['email' => $email],
            'response_status' => 'success',
            'validated_at' => now(),
        ]);

        return [
            'success' => true,
            'isValid' => true,
            'expiryDate' => $key->expiry_date?->toIso8601String(),
            'features' => $key->features ?? [],
            'maxDevices' => $key->max_devices,
            'activatedDevices' => $key->active_devices_count,
            'message' => 'License activated successfully',
        ];
    }

    /**
     * Validate a license key
     */
    public function validateKey(
        string $licenseKey,
        string $deviceId,
        string $email,
        string $encryptedDeviceInfo,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        // Find license key
        $key = ProjectUserKey::where('key', $licenseKey)->first();

        if (!$key) {
            throw new InvalidKeyException('License key not found');
        }

        // Get project
        $project = $key->projectUser->project;

        // Decrypt device info
        try {
            $deviceInfo = $this->decryptDeviceInfo($encryptedDeviceInfo, $project->secret_key);
        } catch (Exception $e) {
            throw new DeviceMismatchException('Failed to decrypt device information: ' . $e->getMessage());
        }

        // Verify device ID matches
        if (!isset($deviceInfo['deviceId']) || $deviceInfo['deviceId'] !== $deviceId) {
            throw new DeviceMismatchException('Device ID mismatch');
        }

        // Verify email matches
        if ($key->projectUser->email !== $email) {
            throw new DeviceMismatchException('Email does not match license');
        }

        // Check if key is active
        if (!$key->is_active) {
            throw new KeyInactiveException();
        }

        // Check if expired
        if ($key->isExpired()) {
            throw new LicenseExpiredException();
        }

        // Validate key format
        $generator = $this->registry->make($project->key_generator_class, $project);
        if (!$generator->validate($licenseKey, $deviceInfo)) {
            throw new InvalidKeyException('Invalid key format');
        }

        // Check if device is activated
        $activation = $key->activations()
            ->where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$activation) {
            throw new DeviceNotActivatedException();
        }

        // Log validation
        $activation->validations()->create([
            'validation_type' => 'validate',
            'device_info' => $deviceInfo,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_data' => ['email' => $email],
            'response_status' => 'success',
            'validated_at' => now(),
        ]);

        // Update key validation count
        $key->increment('validation_count');
        $key->update(['last_validated_at' => now()]);

        return [
            'success' => true,
            'isValid' => true,
            'expiryDate' => $key->expiry_date?->toIso8601String(),
            'features' => $key->features ?? [],
            'maxDevices' => $key->max_devices,
            'validationCount' => $key->validation_count,
            'message' => 'License valid',
        ];
    }

    /**
     * Decrypt device info using AES-256-CBC
     */
    protected function decryptDeviceInfo(string $encryptedData, string $secretKey): array
    {
        // Parse format: {IV}:{ENCRYPTED_DATA}
        $parts = explode(':', $encryptedData);

        if (count($parts) !== 2) {
            throw new Exception('Invalid encrypted data format');
        }

        [$ivBase64, $encryptedBase64] = $parts;

        // Decode from base64
        $iv = base64_decode($ivBase64);
        $encrypted = base64_decode($encryptedBase64);

        // Prepare secret key (32 bytes for AES-256)
        $key = substr(str_pad($secretKey, 32, '0'), 0, 32);

        // Decrypt
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new Exception('Decryption failed - check secret key');
        }

        $deviceInfo = json_decode($decrypted, true);

        if (!$deviceInfo) {
            throw new Exception('Invalid device info JSON');
        }

        return $deviceInfo;
    }

    /**
     * Revoke a license key
     */
    public function revokeKey(string $licenseKey): bool
    {
        $key = ProjectUserKey::where('key', $licenseKey)->first();

        if (!$key) {
            throw new InvalidKeyException('License key not found');
        }

        $key->update(['is_active' => false]);

        // Deactivate all devices
        $key->activations()->update(['is_active' => false]);

        return true;
    }

    /**
     * Deactivate a specific device
     */
    public function deactivateDevice(string $licenseKey, string $deviceId): bool
    {
        $key = ProjectUserKey::where('key', $licenseKey)->first();

        if (!$key) {
            throw new InvalidKeyException('License key not found');
        }

        $activation = $key->activations()
            ->where('device_id', $deviceId)
            ->first();

        if (!$activation) {
            return false;
        }

        $activation->update(['is_active' => false]);

        return true;
    }
}
