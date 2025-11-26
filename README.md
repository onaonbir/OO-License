# OO-License

Flexible and powerful license key management system for Laravel applications with multi-platform client SDK support.

## Platform Support

- ✅ **PHP** - Native PHP applications
- ✅ **JavaScript/Node.js** - Server-side and Electron apps
- ✅ **Dart/Flutter** - Mobile (iOS/Android) and Desktop apps
- ✅ **Laravel** - First-class Laravel integration

## Features

- 🔑 **Multiple Key Generators**: Support for different key generation algorithms (V1, V2, custom)
- 🔐 **Secure Key Generation**: HMAC-signed keys with payload encryption
- 📱 **Multi-Device Support**: Manage license activations across multiple devices
- ⏰ **Expiration Management**: Time-based license expiration
- 🎯 **Feature-Based Licensing**: Control features per license key
- 🔒 **Device Locking**: Hardware-based device identification
- 📊 **Validation Tracking**: Complete audit trail of all validations
- 📈 **Usage Analytics**: Track app usage, feature adoption, and custom events
- 🏢 **Multi-Tenant Ready**: Organization-based isolation
- 🌍 **Multi-Platform SDKs**: PHP, JavaScript/Node.js, Dart/Flutter clients

## Installation

Install via Composer:

```bash
composer require onaonbir/oo-license
```

Publish migrations:

```bash
php artisan vendor:publish --tag=oo-license-migrations
```

Run migrations:

```bash
php artisan migrate
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=oo-license-config
```

## Quick Start

### 1. Create a Project

```php
use OnaOnbir\OOLicense\Models\Project;

$project = Project::create([
    'name' => 'My Application',
    'slug' => 'my-app',
    'version' => '1.0.0',
    'encryption_key' => bin2hex(random_bytes(32)),
    'secret_key' => bin2hex(random_bytes(32)),
    'key_generator_class' => 'bfb.v2',
    'is_active' => true,
]);
```

### 2. Add a User

```php
use OnaOnbir\OOLicense\Models\ProjectUser;

$user = $project->users()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

### 3. Generate a License Key

```php
use OnaOnbir\OOLicense\Services\LicenseService;

$licenseService = app(LicenseService::class);

$result = $licenseService->generateKey($project, $user, [
    'expiry_date' => now()->addYear(),
    'max_devices' => 3,
    'features' => ['feature1', 'feature2', 'feature3'],
]);

// $result['key'] => Generated license key
// $result['license'] => ProjectUserKey model instance
```

### 4. Activate License on Device

```php
$result = $licenseService->activateKey(
    licenseKey: 'BFB2-xxxxx.yyyy',
    deviceId: 'unique-device-id',
    email: 'john@example.com',
    encryptedDeviceInfo: $encryptedInfo
);

if ($result['success']) {
    echo "License activated!";
}
```

### 5. Validate License

```php
$result = $licenseService->validateKey(
    licenseKey: 'BFB2-xxxxx.yyyy',
    deviceId: 'unique-device-id',
    email: 'john@example.com',
    encryptedDeviceInfo: $encryptedInfo
);

if ($result['isValid']) {
    echo "License is valid!";
    // Access $result['features'], $result['expiryDate']
}
```

### 6. Track Usage Analytics

```php
use OnaOnbir\OOLicense\Services\LicenseService;

$licenseService = app(LicenseService::class);

// Track feature usage
$licenseService->trackFeatureUsage('BFB2-xxxxx.yyyy', 'Export PDF', [
    'format' => 'pdf',
    'pages' => 10,
]);

// Track app opened
$licenseService->trackAppOpened('BFB2-xxxxx.yyyy', ['app_version' => '2.1.0']);

// Track custom event
$licenseService->trackUsage(
    licenseKey: 'BFB2-xxxxx.yyyy',
    eventType: 'custom',
    eventName: 'Payment Completed',
    eventData: ['amount' => 99.99],
    metadata: ['currency' => 'USD']
);

// Get usage statistics
$stats = $licenseService->getUsageStats('BFB2-xxxxx.yyyy', 'month');
```

[📖 Usage Tracking Documentation](docs/USAGE_TRACKING.md)

## Key Generators

### Built-in Generators

#### BFB Key Generator V1
Simple hash-based key generation.

**Format**: `BFB-XXXXXX-XXXXXX-XXXXXX-XXXXXX`

```php
'key_generator_class' => 'bfb.v1'
```

#### BFB Key Generator V2
Advanced HMAC-signed keys with encrypted payload.

**Format**: `BFB2-{BASE64_PAYLOAD}.{SIGNATURE}`

```php
'key_generator_class' => 'bfb.v2'
```

### Custom Key Generator

Create your own key generator:

```php
use OnaOnbir\OOLicense\Services\KeyGenerators\AbstractKeyGenerator;

class MyCustomGenerator extends AbstractKeyGenerator
{
    public function getVersion(): string
    {
        return 'v1';
    }

    public function getKeyFormat(): string
    {
        return 'CUSTOM-XXXX-XXXX';
    }

    public function generate(ProjectUser $user, array $options = []): array
    {
        // Your key generation logic
    }

    public function validate(string $key, array $deviceInfo): bool
    {
        // Your validation logic
    }

    public function decode(string $key): ?array
    {
        // Your decode logic
    }
}
```

Register in config:

```php
'custom_generators' => [
    'my-custom.v1' => \App\Services\MyCustomGenerator::class,
],
```

## Device Info Encryption

Client-side encryption example (JavaScript):

```javascript
const CryptoJS = require('crypto-js');

function encryptDeviceInfo(deviceInfo, secretKey) {
    const iv = CryptoJS.lib.WordArray.random(16);
    const key = CryptoJS.enc.Utf8.parse(secretKey.padEnd(32, '0').substr(0, 32));

    const encrypted = CryptoJS.AES.encrypt(
        JSON.stringify(deviceInfo),
        key,
        { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7 }
    );

    return CryptoJS.enc.Base64.stringify(iv) + ':' + encrypted.toString();
}

const deviceInfo = {
    deviceId: 'unique-device-id',
    hostname: 'DESKTOP-ABC123',
    macAddress: 'XX:XX:XX:XX:XX:XX',
    cpuId: 'cpu-id-here'
};

const encryptedInfo = encryptDeviceInfo(deviceInfo, 'your-project-secret-key');
```

## Configuration

See `config/oo-license.php` for all available options:

```php
return [
    'default_generator' => 'bfb.v2',
    'encryption_method' => 'AES-256-CBC',
    'custom_generators' => [],
    'validation' => [
        'check_device_info' => true,
        'strict_mode' => false,
    ],
];
```

## API Example

If you want to create API endpoints:

```php
// routes/api.php
Route::post('/license/activate', [LicenseApiController::class, 'activate']);
Route::post('/license/validate', [LicenseApiController::class, 'validate']);
```

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Credits

Developed by [OnaOnbir](https://github.com/onaonbir)

## Client SDKs

### PHP Client

```php
require_once 'clients/php/OOLicenseClient.php';

$client = new \OnaOnbir\OOLicenseClient\OOLicenseClient(
    'https://license.example.com',
    'your-secret-key'
);

$result = $client->activate('BFB2-xxxxx.yyyy', 'user@example.com');
```

[📖 PHP Documentation](docs/php/README.md) | [💻 PHP Client](clients/php/)

### JavaScript/Node.js Client

```javascript
const OOLicenseClient = require('oo-license-client');

const client = new OOLicenseClient(
    'https://license.example.com',
    'your-secret-key'
);

const result = await client.activate('BFB2-xxxxx.yyyy', 'user@example.com');
```

[📖 JavaScript Documentation](docs/javascript/README.md) | [💻 JS Client](clients/javascript/)

### Dart/Flutter Client

```dart
import 'package:oo_license_client/oo_license_client.dart';

final client = OOLicenseClient(
  'https://license.example.com',
  'your-secret-key',
);

final result = await client.activate('BFB2-xxxxx.yyyy', 'user@example.com');
```

[📖 Dart Documentation](docs/dart/README.md) | [💻 Dart Client](clients/dart/)

## Documentation

- [Server-Side Installation](README.md#installation)
- [Usage Tracking & Analytics](docs/USAGE_TRACKING.md)
- [API Endpoints Reference](docs/API_ENDPOINTS.md)
- [PHP Client Guide](docs/php/README.md)
- [JavaScript Client Guide](docs/javascript/README.md)
- [Dart/Flutter Client Guide](docs/dart/README.md)
- [Example Projects](docs/examples/)

## Support

For issues and questions: https://github.com/onaonbir/oo-license/issues
