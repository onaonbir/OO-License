<?php

/**
 * PHP Example - Simple Desktop Application with License Protection
 *
 * This example shows how to protect a PHP application with OO-License
 */

require_once '../../clients/php/OOLicenseClient.php';

use OnaOnbir\OOLicenseClient\OOLicenseClient;

class LicensedApp
{
    private OOLicenseClient $licenseClient;

    private string $configFile = 'license-config.json';

    public function __construct()
    {
        $this->licenseClient = new OOLicenseClient(
            'https://license.yourdomain.com',
            'your-project-secret-key-here'
        );
    }

    public function run(): void
    {
        echo "=================================\n";
        echo "  My Awesome Application v1.0   \n";
        echo "=================================\n\n";

        // Check if license exists
        if (! file_exists($this->configFile)) {
            echo "No license found. Please activate your license.\n\n";
            $this->activateLicense();

            return;
        }

        // Validate existing license
        if (! $this->validateExistingLicense()) {
            echo "License validation failed. Please re-activate.\n\n";
            $this->activateLicense();

            return;
        }

        // License is valid - run the app
        $this->startApplication();
    }

    private function activateLicense(): void
    {
        echo 'Enter your license key: ';
        $licenseKey = trim(fgets(STDIN));

        echo 'Enter your email: ';
        $email = trim(fgets(STDIN));

        try {
            $result = $this->licenseClient->activate($licenseKey, $email);

            if ($result['success']) {
                // Save license config
                $config = [
                    'license_key' => $licenseKey,
                    'email' => $email,
                    'activated_at' => date('Y-m-d H:i:s'),
                    'features' => $result['features'],
                    'expiry_date' => $result['expiryDate'],
                ];

                file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));

                echo "\n✓ License activated successfully!\n";
                echo 'Features: '.implode(', ', $result['features'])."\n";
                echo 'Expires: '.($result['expiryDate'] ?? 'Never')."\n\n";

                $this->startApplication();
            }
        } catch (Exception $e) {
            echo "\n❌ Activation failed: ".$e->getMessage()."\n";
            exit(1);
        }
    }

    private function validateExistingLicense(): bool
    {
        $config = json_decode(file_get_contents($this->configFile), true);

        try {
            $isValid = $this->licenseClient->isValid($config['license_key'], $config['email']);

            if ($isValid) {
                echo "✓ License validated successfully!\n\n";

                // Update last check
                $config['last_check'] = date('Y-m-d H:i:s');
                file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));
            }

            return $isValid;
        } catch (Exception $e) {
            echo '❌ Validation error: '.$e->getMessage()."\n";

            return false;
        }
    }

    private function startApplication(): void
    {
        $config = json_decode(file_get_contents($this->configFile), true);

        echo "=================================\n";
        echo "  Application Running            \n";
        echo "=================================\n\n";

        echo 'Licensed to: '.$config['email']."\n";
        echo 'Activated on: '.$config['activated_at']."\n";
        echo 'Expires: '.($config['expiry_date'] ?? 'Never')."\n\n";

        // Check features
        $features = $config['features'] ?? [];

        if (in_array('premium_support', $features)) {
            echo "✓ Premium Support: Enabled\n";
        }

        if (in_array('api_access', $features)) {
            echo "✓ API Access: Enabled\n";
            $this->enableApiAccess();
        }

        if (in_array('advanced_features', $features)) {
            echo "✓ Advanced Features: Enabled\n";
            $this->enableAdvancedFeatures();
        }

        echo "\nPress Ctrl+C to exit...\n\n";

        // Your application logic here
        while (true) {
            sleep(1);
        }
    }

    private function enableApiAccess(): void
    {
        // Enable API functionality
    }

    private function enableAdvancedFeatures(): void
    {
        // Enable advanced features
    }
}

// Run the application
$app = new LicensedApp;
$app->run();
