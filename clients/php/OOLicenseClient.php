<?php

namespace OnaOnbir\OOLicenseClient;

/**
 * OO-License Client SDK for PHP
 *
 * Simple PHP client for validating and activating license keys
 *
 * @package OnaOnbir\OOLicenseClient
 * @version 1.0.0
 */
class OOLicenseClient
{
    private string $apiUrl;
    private string $secretKey;
    private array $deviceInfo;

    /**
     * Initialize the license client
     *
     * @param string $apiUrl Your license server URL (e.g., https://license.yourdomain.com)
     * @param string $secretKey Your project secret key
     */
    public function __construct(string $apiUrl, string $secretKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->secretKey = $secretKey;
        $this->deviceInfo = $this->collectDeviceInfo();
    }

    /**
     * Activate a license key
     *
     * @param string $licenseKey The license key to activate
     * @param string $email User's email address
     * @return array Response from server
     * @throws \Exception
     */
    public function activate(string $licenseKey, string $email): array
    {
        $encryptedDeviceInfo = $this->encryptDeviceInfo($this->deviceInfo);

        $response = $this->makeRequest('/api/license/activate', [
            'license_key' => $licenseKey,
            'device_id' => $this->deviceInfo['deviceId'],
            'email' => $email,
            'encrypted_device_info' => $encryptedDeviceInfo,
        ]);

        return $response;
    }

    /**
     * Validate a license key
     *
     * @param string $licenseKey The license key to validate
     * @param string $email User's email address
     * @return array Response from server
     * @throws \Exception
     */
    public function validate(string $licenseKey, string $email): array
    {
        $encryptedDeviceInfo = $this->encryptDeviceInfo($this->deviceInfo);

        $response = $this->makeRequest('/api/license/validate', [
            'license_key' => $licenseKey,
            'device_id' => $this->deviceInfo['deviceId'],
            'email' => $email,
            'encrypted_device_info' => $encryptedDeviceInfo,
        ]);

        return $response;
    }

    /**
     * Check if license is valid and active
     *
     * @param string $licenseKey
     * @param string $email
     * @return bool
     */
    public function isValid(string $licenseKey, string $email): bool
    {
        try {
            $result = $this->validate($licenseKey, $email);
            return $result['success'] && $result['isValid'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get license information
     *
     * @param string $licenseKey
     * @param string $email
     * @return array|null
     */
    public function getLicenseInfo(string $licenseKey, string $email): ?array
    {
        try {
            $result = $this->validate($licenseKey, $email);
            if ($result['success']) {
                return [
                    'isValid' => $result['isValid'],
                    'expiryDate' => $result['expiryDate'] ?? null,
                    'features' => $result['features'] ?? [],
                    'maxDevices' => $result['maxDevices'] ?? 1,
                    'validationCount' => $result['validationCount'] ?? 0,
                ];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Collect device information
     *
     * @return array
     */
    private function collectDeviceInfo(): array
    {
        return [
            'deviceId' => $this->getDeviceId(),
            'hostname' => gethostname(),
            'os' => PHP_OS,
            'phpVersion' => PHP_VERSION,
            'timestamp' => time(),
        ];
    }

    /**
     * Generate unique device ID
     *
     * @return string
     */
    private function getDeviceId(): string
    {
        // Try to get MAC address
        $macAddress = null;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = shell_exec('getmac');
            if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $output, $matches)) {
                $macAddress = $matches[0];
            }
        } else {
            // Unix/Linux/Mac
            $output = shell_exec('ifconfig -a');
            if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $output, $matches)) {
                $macAddress = $matches[0];
            }
        }

        // Fallback: Use hostname + OS
        if (!$macAddress) {
            $macAddress = gethostname() . '_' . PHP_OS;
        }

        return hash('sha256', $macAddress);
    }

    /**
     * Encrypt device information
     *
     * @param array $deviceInfo
     * @return string
     */
    private function encryptDeviceInfo(array $deviceInfo): string
    {
        $data = json_encode($deviceInfo);
        $iv = random_bytes(16);
        $key = substr(str_pad($this->secretKey, 32, '0'), 0, 32);

        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv) . ':' . base64_encode($encrypted);
    }

    /**
     * Make HTTP request to license server
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL error: {$error}");
        }

        $result = json_decode($response, true);

        if (!$result) {
            throw new \Exception("Invalid JSON response from server");
        }

        if ($httpCode >= 400) {
            throw new \Exception($result['message'] ?? 'License validation failed');
        }

        return $result;
    }

    /**
     * Track usage event
     *
     * @param string $licenseKey
     * @param string $eventType Event category: app_opened, feature_used, button_clicked, error_occurred, custom
     * @param string $eventName Descriptive name
     * @param array $eventData Custom event data
     * @param array $metadata Additional metadata
     * @return array
     * @throws \Exception
     */
    public function trackUsage(
        string $licenseKey,
        string $eventType,
        string $eventName,
        array $eventData = [],
        array $metadata = []
    ): array {
        return $this->makeRequest('/api/license/track', [
            'license_key' => $licenseKey,
            'event_type' => $eventType,
            'event_name' => $eventName,
            'event_data' => $eventData,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Track multiple events at once (batch)
     *
     * @param string $licenseKey
     * @param array $events Array of events
     * @param array $metadata Common metadata
     * @return array
     * @throws \Exception
     */
    public function trackUsageBatch(string $licenseKey, array $events, array $metadata = []): array
    {
        return $this->makeRequest('/api/license/track-batch', [
            'license_key' => $licenseKey,
            'events' => $events,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Helper: Track app opened
     */
    public function trackAppOpened(string $licenseKey, string $appVersion = '1.0.0'): array
    {
        return $this->trackUsage(
            $licenseKey,
            'app_opened',
            'Application Opened',
            [],
            ['app_version' => $appVersion]
        );
    }

    /**
     * Helper: Track feature usage
     */
    public function trackFeature(string $licenseKey, string $featureName, array $data = []): array
    {
        return $this->trackUsage(
            $licenseKey,
            'feature_used',
            $featureName,
            $data
        );
    }

    /**
     * Helper: Track button click
     */
    public function trackButtonClick(string $licenseKey, string $buttonName, array $data = []): array
    {
        return $this->trackUsage(
            $licenseKey,
            'button_clicked',
            $buttonName,
            $data
        );
    }

    /**
     * Helper: Track error
     */
    public function trackError(string $licenseKey, string $errorMessage, array $data = []): array
    {
        return $this->trackUsage(
            $licenseKey,
            'error_occurred',
            $errorMessage,
            $data
        );
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(string $licenseKey, string $period = 'all'): array
    {
        $url = $this->apiUrl . '/api/license/usage-stats?license_key=' . urlencode($licenseKey) . '&period=' . $period;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \Exception($result['message'] ?? 'Failed to get usage stats');
        }

        return $result;
    }
}
