# OO-License PHP Client

Simple PHP client for validating and activating license keys with the OO-License system.

## Installation

### Option 1: Copy the Client File

Download and include `OOLicenseClient.php` in your project:

```php
require_once 'path/to/OOLicenseClient.php';

use OnaOnbir\OOLicenseClient\OOLicenseClient;
```

### Option 2: Use Composer (if published)

```bash
composer require onaonbir/oo-license-client-php
```

## Quick Start

```php
<?php

require_once 'OOLicenseClient.php';

use OnaOnbir\OOLicenseClient\OOLicenseClient;

// Initialize client
$client = new OOLicenseClient(
    'https://your-license-server.com',  // Your license server URL
    'your-project-secret-key'            // Your project secret key
);

// Activate license
try {
    $result = $client->activate(
        'BFB2-xxxxx.yyyy',              // License key
        'user@example.com'               // User email
    );

    if ($result['success']) {
        echo "License activated successfully!\n";
        echo "Expires: " . $result['expiryDate'] . "\n";
        echo "Features: " . implode(', ', $result['features']) . "\n";
    }
} catch (Exception $e) {
    echo "Activation failed: " . $e->getMessage() . "\n";
}
```

## Usage Examples

### 1. Activate License

```php
$client = new OOLicenseClient('https://license.example.com', 'secret-key');

try {
    $result = $client->activate('BFB2-xxxxx.yyyy', 'user@example.com');

    if ($result['success']) {
        // Save license info to database or config file
        file_put_contents('license.json', json_encode($result));

        echo "✓ License activated!\n";
        echo "Features: " . implode(', ', $result['features']) . "\n";
        echo "Max Devices: " . $result['maxDevices'] . "\n";
        echo "Expires: " . ($result['expiryDate'] ?? 'Never') . "\n";
    }
} catch (Exception $e) {
    die("❌ Activation failed: " . $e->getMessage());
}
```

### 2. Validate License

```php
try {
    $result = $client->validate('BFB2-xxxxx.yyyy', 'user@example.com');

    if ($result['isValid']) {
        echo "✓ License is valid\n";
        echo "Validation count: " . $result['validationCount'] . "\n";
    } else {
        echo "❌ License is not valid\n";
    }
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}
```

### 3. Check License Validity (Simple)

```php
if ($client->isValid('BFB2-xxxxx.yyyy', 'user@example.com')) {
    // License is valid - proceed with protected features
    echo "Access granted!\n";
} else {
    // License is invalid or expired
    echo "Access denied. Please activate your license.\n";
    exit(1);
}
```

### 4. Get License Information

```php
$info = $client->getLicenseInfo('BFB2-xxxxx.yyyy', 'user@example.com');

if ($info) {
    echo "License Status: " . ($info['isValid'] ? 'Active' : 'Inactive') . "\n";
    echo "Features: " . implode(', ', $info['features']) . "\n";
    echo "Max Devices: " . $info['maxDevices'] . "\n";
    echo "Expires: " . ($info['expiryDate'] ?? 'Never') . "\n";
} else {
    echo "Could not retrieve license information\n";
}
```

### 5. Protect Your Application

```php
<?php

require_once 'OOLicenseClient.php';

class MyApp
{
    private $licenseClient;
    private $licenseKey;
    private $userEmail;

    public function __construct()
    {
        $this->licenseClient = new OOLicenseClient(
            'https://license.example.com',
            'your-secret-key'
        );

        // Load from config file
        $config = json_decode(file_get_contents('config.json'), true);
        $this->licenseKey = $config['license_key'];
        $this->userEmail = $config['email'];
    }

    public function start()
    {
        // Validate license on startup
        if (!$this->validateLicense()) {
            die("Invalid or expired license. Please contact support.\n");
        }

        // Run your application
        $this->run();
    }

    private function validateLicense(): bool
    {
        try {
            return $this->licenseClient->isValid($this->licenseKey, $this->userEmail);
        } catch (Exception $e) {
            error_log("License validation error: " . $e->getMessage());
            return false;
        }
    }

    private function run()
    {
        echo "Application started successfully!\n";
        // Your application logic here
    }
}

// Run application
$app = new MyApp();
$app->start();
```

### 6. Feature-Based Access Control

```php
$info = $client->getLicenseInfo('BFB2-xxxxx.yyyy', 'user@example.com');

if ($info && $info['isValid']) {
    $features = $info['features'];

    // Check if user has access to specific features
    if (in_array('premium_support', $features)) {
        echo "✓ Premium support enabled\n";
    }

    if (in_array('api_access', $features)) {
        echo "✓ API access enabled\n";
    }

    if (in_array('advanced_features', $features)) {
        echo "✓ Advanced features enabled\n";
    }
} else {
    echo "Please upgrade your license to access these features\n";
}
```

### 7. Periodic License Check (Cron Job)

```php
<?php

// periodic_check.php - Run this daily via cron

require_once 'OOLicenseClient.php';

$client = new OOLicenseClient('https://license.example.com', 'secret-key');
$config = json_decode(file_get_contents('config.json'), true);

try {
    $result = $client->validate($config['license_key'], $config['email']);

    if ($result['isValid']) {
        // Update last check timestamp
        $config['last_check'] = date('Y-m-d H:i:s');
        $config['status'] = 'active';
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));

        echo "License check passed ✓\n";
    } else {
        $config['status'] = 'invalid';
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));

        // Send notification email
        mail('admin@example.com', 'License Invalid', 'License validation failed');

        echo "License check failed ❌\n";
    }
} catch (Exception $e) {
    error_log("License check error: " . $e->getMessage());
}
```

## Error Handling

```php
try {
    $result = $client->activate('BFB2-xxxxx.yyyy', 'user@example.com');

    if ($result['success']) {
        echo "Success!";
    }
} catch (Exception $e) {
    // Handle different error types
    $error = $e->getMessage();

    if (strpos($error, 'INVALID_KEY') !== false) {
        echo "Invalid license key";
    } elseif (strpos($error, 'EXPIRED') !== false) {
        echo "License has expired";
    } elseif (strpos($error, 'MAX_DEVICES_REACHED') !== false) {
        echo "Maximum device limit reached";
    } elseif (strpos($error, 'DEVICE_ALREADY_ACTIVATED') !== false) {
        echo "This device is already activated";
    } elseif (strpos($error, 'NOT_ACTIVATED') !== false) {
        echo "Device not activated";
    } else {
        echo "Error: " . $error;
    }
}
```

## API Reference

### Constructor

```php
__construct(string $apiUrl, string $secretKey)
```

- `$apiUrl`: Your license server URL
- `$secretKey`: Your project secret key from the license dashboard

### Methods

#### activate()

```php
activate(string $licenseKey, string $email): array
```

Activate a license key on the current device.

**Returns:**
```php
[
    'success' => true,
    'isValid' => true,
    'expiryDate' => '2025-12-31T23:59:59+00:00',
    'features' => ['feature1', 'feature2'],
    'maxDevices' => 3,
    'activatedDevices' => 1,
    'message' => 'License activated successfully'
]
```

#### validate()

```php
validate(string $licenseKey, string $email): array
```

Validate a license key.

**Returns:**
```php
[
    'success' => true,
    'isValid' => true,
    'expiryDate' => '2025-12-31T23:59:59+00:00',
    'features' => ['feature1', 'feature2'],
    'maxDevices' => 3,
    'validationCount' => 42,
    'message' => 'License valid'
]
```

#### isValid()

```php
isValid(string $licenseKey, string $email): bool
```

Simple boolean check if license is valid.

#### getLicenseInfo()

```php
getLicenseInfo(string $licenseKey, string $email): ?array
```

Get detailed license information.

**Returns:**
```php
[
    'isValid' => true,
    'expiryDate' => '2025-12-31T23:59:59+00:00',
    'features' => ['feature1', 'feature2'],
    'maxDevices' => 3,
    'validationCount' => 42
]
```

## Usage Analytics

### Track Events

```php
$client = new OOLicenseClient('https://license.example.com', 'secret-key');
$licenseKey = 'BFB2-xxxxx.yyyy';

// Track app opened
$client->trackAppOpened($licenseKey, '2.1.0');

// Track feature usage
$client->trackFeature($licenseKey, 'Export PDF', [
    'format' => 'pdf',
    'pages' => 10,
    'quality' => 'high',
]);

// Track button click
$client->trackButtonClick($licenseKey, 'Save Button', [
    'screen' => 'editor',
    'has_changes' => true,
]);

// Track error
$client->trackError($licenseKey, 'File Save Failed', [
    'error_code' => 'FS_001',
    'file_path' => '/tmp/document.pdf',
]);

// Custom event
$client->trackUsage(
    $licenseKey,
    'custom',
    'Settings Changed',
    ['theme' => 'dark', 'language' => 'en'],
    ['screen' => 'preferences']
);
```

### Batch Tracking

```php
// Send multiple events at once (more efficient)
$client->trackUsageBatch($licenseKey, [
    ['type' => 'app_opened', 'name' => 'App Started'],
    ['type' => 'feature_used', 'name' => 'Import CSV', 'data' => ['rows' => 1000]],
    ['type' => 'button_clicked', 'name' => 'Export Button'],
], ['app_version' => '2.1.0', 'os' => PHP_OS]);
```

### Get Usage Statistics

```php
$stats = $client->getUsageStats($licenseKey, 'month');

echo "Total events: " . $stats['total_events'] . "\n";
echo "App opens: " . $stats['events_by_type']['app_opened'] . "\n";
echo "Features used: " . $stats['events_by_type']['feature_used'] . "\n";
```

## Requirements

- PHP 7.4 or higher
- cURL extension
- OpenSSL extension

## Security Notes

1. **Never expose your secret key** in client-side code
2. Store license keys securely (encrypted database, config files with proper permissions)
3. Implement rate limiting to prevent abuse
4. Use HTTPS for all API communication
5. Validate licenses periodically, not just on activation

## Support

For issues and questions: https://github.com/onaonbir/oo-license/issues
