# OO-License API Endpoints

This document describes the API endpoints you need to implement in your Laravel application to work with the client SDKs.

## Creating API Controllers

Since OO-License focuses on the core licensing system, you'll need to create your own API controllers. Here's a reference implementation:

### API Controller Example

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OnaOnbir\OOLicense\Services\LicenseService;

class LicenseApiController extends Controller
{
    public function __construct(
        protected LicenseService $licenseService
    ) {}

    /**
     * Activate a license key
     *
     * POST /api/license/activate
     */
    public function activate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'device_id' => ['required', 'string'],
            'email' => ['required', 'email'],
            'encrypted_device_info' => ['required', 'string'],
        ]);

        try {
            $result = $this->licenseService->activateKey(
                licenseKey: $validated['license_key'],
                deviceId: $validated['device_id'],
                email: $validated['email'],
                encryptedDeviceInfo: $validated['encrypted_device_info'],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return response()->json($result);
        } catch (\OnaOnbir\OOLicense\Exceptions\LicenseException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getErrorCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Validate a license key
     *
     * POST /api/license/validate
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'device_id' => ['required', 'string'],
            'email' => ['required', 'email'],
            'encrypted_device_info' => ['required', 'string'],
        ]);

        try {
            $result = $this->licenseService->validateKey(
                licenseKey: $validated['license_key'],
                deviceId: $validated['device_id'],
                email: $validated['email'],
                encryptedDeviceInfo: $validated['encrypted_device_info'],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return response()->json($result);
        } catch (\OnaOnbir\OOLicense\Exceptions\LicenseException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getErrorCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
```

### Routes Setup

Add to your `routes/api.php`:

```php
use App\Http\Controllers\Api\LicenseApiController;

Route::prefix('license')->group(function () {
    Route::post('/activate', [LicenseApiController::class, 'activate']);
    Route::post('/validate', [LicenseApiController::class, 'validate']);
});
```

## API Endpoints

### 1. POST /api/license/activate

Activate a license key on a device.

**Request:**
```json
{
    "license_key": "BFB2-xxxxx.yyyy",
    "device_id": "unique-device-id",
    "email": "user@example.com",
    "encrypted_device_info": "base64_iv:base64_encrypted_data"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "isValid": true,
    "expiryDate": "2025-12-31T23:59:59+00:00",
    "features": ["feature1", "feature2", "premium_support"],
    "maxDevices": 3,
    "activatedDevices": 1,
    "message": "License activated successfully"
}
```

**Error Responses:**

```json
// 404 - License not found
{
    "success": false,
    "error": "INVALID_KEY",
    "message": "License key not found"
}

// 403 - Expired
{
    "success": false,
    "error": "EXPIRED",
    "message": "License key has expired"
}

// 403 - Max devices reached
{
    "success": false,
    "error": "MAX_DEVICES_REACHED",
    "message": "Maximum device limit (3) reached"
}

// 400 - Device mismatch
{
    "success": false,
    "error": "DEVICE_MISMATCH",
    "message": "Device ID mismatch"
}

// 409 - Device already activated
{
    "success": false,
    "error": "DEVICE_ALREADY_ACTIVATED",
    "message": "Device 'DEVICE-12345' is already activated for this license key"
}
```

### 2. POST /api/license/validate

Validate a license key.

**Request:**
```json
{
    "license_key": "BFB2-xxxxx.yyyy",
    "device_id": "unique-device-id",
    "email": "user@example.com",
    "encrypted_device_info": "base64_iv:base64_encrypted_data"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "isValid": true,
    "expiryDate": "2025-12-31T23:59:59+00:00",
    "features": ["feature1", "feature2"],
    "maxDevices": 3,
    "validationCount": 42,
    "message": "License valid"
}
```

**Error Responses:**

```json
// 403 - Not activated
{
    "success": false,
    "error": "NOT_ACTIVATED",
    "message": "Device not activated. Please activate first."
}

// 403 - Inactive key
{
    "success": false,
    "error": "KEY_INACTIVE",
    "message": "License key is inactive"
}
```

## Error Codes Reference

| Code | Description | HTTP Status |
|------|-------------|-------------|
| `INVALID_KEY` | License key not found | 404 |
| `EXPIRED` | License has expired | 403 |
| `KEY_INACTIVE` | License key is inactive/revoked | 403 |
| `MAX_DEVICES_REACHED` | Device limit reached | 403 |
| `NOT_ACTIVATED` | Device not activated | 403 |
| `DEVICE_ALREADY_ACTIVATED` | Device already activated | 409 |
| `DEVICE_MISMATCH` | Device info mismatch | 400 |
| `EMAIL_MISMATCH` | Email doesn't match | 403 |
| `DECRYPTION_FAILED` | Failed to decrypt device info | 400 |

## Rate Limiting (Recommended)

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/api/license/activate', [LicenseApiController::class, 'activate']);
    Route::post('/api/license/validate', [LicenseApiController::class, 'validate']);
});
```

## Authentication (Optional)

For additional security, you can add API token authentication:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/license/activate', [LicenseApiController::class, 'activate']);
});
```

## CORS Configuration

If your client apps are hosted on different domains, configure CORS in `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => ['*'], // Or specific domains
'allowed_methods' => ['POST'],
```

## Testing

Use the included test routes to verify your setup:

```bash
# Test activation
curl -X POST http://localhost:8000/test-license/activate \
  -H "Content-Type: application/json" \
  -d '{"license_key":"BFB2-xxxxx.yyyy","device_id":"DEVICE-123"}'

# Test validation
curl -X POST http://localhost:8000/test-license/validate \
  -H "Content-Type: application/json" \
  -d '{"license_key":"BFB2-xxxxx.yyyy","device_id":"DEVICE-123"}'
```
