# Usage Tracking & Analytics

Track how users interact with your licensed application and gather valuable analytics.

## Overview

The usage tracking system allows you to:
- 📊 Track app opens, feature usage, button clicks, and custom events
- 📈 View analytics dashboard with real-time metrics
- 🔍 Monitor user behavior and feature adoption
- 🐛 Track errors and crashes
- 📱 Understand how users interact with your app

## Features

- **Event Types**: Pre-defined categories (app_opened, feature_used, button_clicked, error_occurred, custom)
- **Flexible Data**: Send any JSON-serializable data
- **Batch Tracking**: Send multiple events in one request
- **Real-time Analytics**: View stats in dashboard
- **Period Filtering**: Today, this week, this month, all time

## Server-Side Usage

### Track Single Event

```php
use OnaOnbir\OOLicense\Services\LicenseService;

$licenseService = app(LicenseService::class);

$licenseService->trackUsage(
    licenseKey: 'BFB2-xxxxx.yyyy',
    eventType: 'feature_used',
    eventName: 'Export PDF',
    eventData: [
        'format' => 'pdf',
        'pages' => 10,
        'file_size' => '2.5MB',
    ],
    metadata: [
        'app_version' => '2.1.0',
        'os' => 'Windows 11',
    ]
);
```

### Track Multiple Events (Batch)

```php
$licenseService->trackUsageBatch(
    licenseKey: 'BFB2-xxxxx.yyyy',
    events: [
        [
            'type' => 'app_opened',
            'name' => 'Application Started',
            'data' => ['screen' => 'dashboard'],
        ],
        [
            'type' => 'feature_used',
            'name' => 'Dark Mode Enabled',
            'data' => ['theme' => 'dark'],
        ],
        [
            'type' => 'button_clicked',
            'name' => 'Export Button',
            'data' => ['format' => 'pdf'],
        ],
    ],
    metadata: ['app_version' => '2.1.0']
);
```

### Helper Methods

```php
// Track app opened
$licenseService->trackAppOpened('BFB2-xxxxx.yyyy', ['app_version' => '1.0.0']);

// Track feature usage
$licenseService->trackFeatureUsage('BFB2-xxxxx.yyyy', 'Premium Export', [
    'format' => 'pdf',
    'quality' => 'high',
]);

// Track error
$licenseService->trackError('BFB2-xxxxx.yyyy', 'Database Connection Failed', [
    'error_code' => 'DB_001',
    'details' => $exception->getMessage(),
]);
```

### Get Usage Statistics

```php
$stats = $licenseService->getUsageStats('BFB2-xxxxx.yyyy', 'month');

// Returns:
[
    'total_events' => 1234,
    'events_by_type' => [
        'app_opened' => 45,
        'feature_used' => 892,
        'button_clicked' => 267,
        'error_occurred' => 12,
    ],
    'period' => 'month',
]
```

## Client-Side Usage

### PHP Client

```php
require_once 'OOLicenseClient.php';

$client = new OOLicenseClient('https://license.example.com', 'secret-key');
$licenseKey = 'BFB2-xxxxx.yyyy';

// Track app opened
$client->trackAppOpened($licenseKey, '2.1.0');

// Track feature usage
$client->trackFeature($licenseKey, 'Export PDF', [
    'format' => 'pdf',
    'pages' => 10,
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
    'User Preferences Changed',
    ['theme' => 'dark', 'language' => 'en'],
    ['screen' => 'settings']
);

// Batch tracking
$client->trackUsageBatch($licenseKey, [
    ['type' => 'app_opened', 'name' => 'App Started'],
    ['type' => 'feature_used', 'name' => 'Export', 'data' => ['format' => 'pdf']],
], ['app_version' => '2.1.0']);
```

### JavaScript Client

```javascript
const client = new OOLicenseClient('https://license.example.com', 'secret-key');
const licenseKey = 'BFB2-xxxxx.yyyy';

// Track app opened
await client.trackAppOpened(licenseKey, '2.1.0');

// Track feature usage
await client.trackFeature(licenseKey, 'Export PDF', {
    format: 'pdf',
    pages: 10
});

// Track button click
await client.trackButtonClick(licenseKey, 'Save Button', {
    screen: 'editor',
    hasChanges: true
});

// Track error
await client.trackError(licenseKey, 'File Save Failed', {
    errorCode: 'FS_001',
    filePath: '/tmp/document.pdf'
});

// Custom event
await client.trackUsage(
    licenseKey,
    'custom',
    'User Preferences Changed',
    { theme: 'dark', language: 'en' },
    { screen: 'settings' }
);

// Batch tracking
await client.trackUsageBatch(licenseKey, [
    { type: 'app_opened', name: 'App Started' },
    { type: 'feature_used', name: 'Export', data: { format: 'pdf' } },
], { appVersion: '2.1.0' });
```

### Dart/Flutter Client

```dart
final client = OOLicenseClient('https://license.example.com', 'secret-key');
const licenseKey = 'BFB2-xxxxx.yyyy';

// Track app opened
await client.trackAppOpened(licenseKey, appVersion: '2.1.0');

// Track feature usage
await client.trackFeature(licenseKey, 'Export PDF', data: {
  'format': 'pdf',
  'pages': 10,
});

// Track button click
await client.trackButtonClick(licenseKey, 'Save Button', data: {
  'screen': 'editor',
  'hasChanges': true,
});

// Track error
await client.trackError(licenseKey, 'File Save Failed', data: {
  'errorCode': 'FS_001',
  'filePath': '/tmp/document.pdf',
});

// Custom event
await client.trackUsage(
  licenseKey,
  'custom',
  'User Preferences Changed',
  eventData: {'theme': 'dark', 'language': 'en'},
  metadata: {'screen': 'settings'},
);

// Batch tracking
await client.trackUsageBatch(licenseKey, [
  {'type': 'app_opened', 'name': 'App Started'},
  {'type': 'feature_used', 'name': 'Export', 'data': {'format': 'pdf'}},
], metadata: {'appVersion': '2.1.0'});
```

## Event Types

### 1. app_opened
Track when users open your application.

```php
$client->trackAppOpened($licenseKey, '2.1.0');
```

**Common Data:**
- `app_version`: Application version
- `screen`: Initial screen shown
- `cold_start`: Whether it's a cold start

### 2. feature_used
Track when users use specific features.

```php
$client->trackFeature($licenseKey, 'Export PDF', [
    'format' => 'pdf',
    'quality' => 'high',
    'pages' => 10,
]);
```

**Common Data:**
- `feature_name`: Name of the feature
- `duration`: Time taken
- `success`: Whether operation succeeded

### 3. button_clicked
Track button clicks and user interactions.

```php
$client->trackButtonClick($licenseKey, 'Export Button', [
    'screen' => 'editor',
    'context' => 'toolbar',
]);
```

**Common Data:**
- `screen`: Which screen
- `context`: Button context
- `count`: Click count

### 4. error_occurred
Track errors and exceptions.

```php
$client->trackError($licenseKey, 'Database Connection Failed', [
    'error_code' => 'DB_001',
    'stack_trace' => $e->getTraceAsString(),
]);
```

**Common Data:**
- `error_code`: Your error code
- `stack_trace`: Stack trace
- `user_action`: What user was doing

### 5. custom
Track any custom event.

```php
$client->trackUsage($licenseKey, 'custom', 'Payment Completed', [
    'amount' => 99.99,
    'currency' => 'USD',
    'plan' => 'premium',
]);
```

## Real-World Examples

### Example 1: Electron App

```javascript
const { app } = require('electron');
const client = new OOLicenseClient('https://license.example.com', 'secret-key');

app.on('ready', async () => {
    // Track app opened
    await client.trackAppOpened(LICENSE_KEY, app.getVersion());
});

ipcMain.on('feature-used', async (event, featureName, data) => {
    await client.trackFeature(LICENSE_KEY, featureName, data);
});

process.on('uncaughtException', async (error) => {
    await client.trackError(LICENSE_KEY, error.message, {
        stack: error.stack,
    });
});
```

### Example 2: Flutter App

```dart
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> with WidgetsBindingObserver {
  final client = OOLicenseClient('https://license.example.com', 'secret-key');
  final licenseKey = 'BFB2-xxxxx.yyyy';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _trackAppOpened();
  }

  Future<void> _trackAppOpened() async {
    await client.trackAppOpened(licenseKey, appVersion: '2.1.0');
  }

  void onExportButtonPressed() async {
    await client.trackButtonClick(licenseKey, 'Export Button', data: {
      'format': 'pdf',
      'screen': 'editor',
    });

    // Your export logic
  }

  void onPremiumFeatureUsed(String featureName) async {
    await client.trackFeature(licenseKey, featureName, data: {
      'timestamp': DateTime.now().toIso8601String(),
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      client.trackUsage(licenseKey, 'app_resumed', 'App Resumed');
    }
  }
}
```

### Example 3: PHP Desktop App

```php
class MyApp {
    private $client;
    private $licenseKey;

    public function __construct() {
        $this->client = new OOLicenseClient('https://license.example.com', 'secret-key');
        $this->licenseKey = 'BFB2-xxxxx.yyyy';

        // Track app opened
        $this->client->trackAppOpened($this->licenseKey, '2.1.0');
    }

    public function exportToPDF($data) {
        $startTime = microtime(true);

        try {
            // Export logic
            $result = $this->generatePDF($data);

            // Track successful export
            $this->client->trackFeature($this->licenseKey, 'Export PDF', [
                'pages' => $data['pages'],
                'duration' => microtime(true) - $startTime,
                'success' => true,
            ]);

            return $result;
        } catch (Exception $e) {
            // Track error
            $this->client->trackError($this->licenseKey, 'PDF Export Failed', [
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime,
            ]);

            throw $e;
        }
    }
}
```

## Analytics Dashboard

Access usage analytics:

```
http://localhost:8000/licenses/keys/{key_id}/usage-logs
```

The dashboard shows:
- 📊 Total events (all time, today, this week, this month)
- 📈 Events breakdown by type
- 📋 Timeline of last 100 events
- 🔍 Event details with custom data

## Best Practices

### 1. Track Meaningful Events

```php
// ✓ Good - Meaningful events
$client->trackFeature('BFB2-xxx', 'Premium Export Used');
$client->trackButtonClick('BFB2-xxx', 'Checkout Button');

// ✗ Bad - Too granular
$client->trackButtonClick('BFB2-xxx', 'Mouse Moved');
```

### 2. Include Useful Data

```php
// ✓ Good - Actionable data
$client->trackFeature('BFB2-xxx', 'Export', [
    'format' => 'pdf',
    'quality' => 'high',
    'pages' => 10,
    'duration_ms' => 1250,
]);

// ✗ Bad - Not useful
$client->trackFeature('BFB2-xxx', 'Export', ['clicked' => true]);
```

### 3. Don't Overtrack

```php
// ✓ Good - Once per session
$client->trackAppOpened($licenseKey);

// ✗ Bad - Every second
setInterval(() => {
    client.trackUsage(...); // Don't do this!
}, 1000);
```

### 4. Batch Events for Performance

```php
// ✓ Good - Batch multiple events
$events = [];
foreach ($actions as $action) {
    $events[] = ['type' => 'feature_used', 'name' => $action];
}
$client->trackUsageBatch($licenseKey, $events);

// ✗ Bad - Individual requests
foreach ($actions as $action) {
    $client->trackFeature($licenseKey, $action); // Multiple HTTP requests
}
```

### 5. Handle Failures Gracefully

```php
try {
    $client->trackFeature($licenseKey, 'Export');
} catch (Exception $e) {
    // Don't block user if tracking fails
    error_log('Tracking failed: ' . $e->getMessage());
}
```

## Privacy Considerations

1. **Don't track PII** (Personally Identifiable Information) without consent
2. **Be transparent** - Tell users what you track
3. **Allow opt-out** - Respect user privacy
4. **Anonymize data** - Hash sensitive information

```php
// ✓ Good - No PII
$client->trackFeature($licenseKey, 'Document Saved', [
    'document_type' => 'contract',
    'size_kb' => 150,
]);

// ✗ Bad - Contains PII
$client->trackFeature($licenseKey, 'Document Saved', [
    'filename' => 'john_doe_ssn_123456789.pdf', // Don't track!
]);
```

## API Reference

### trackUsage()

```php
trackUsage(
    string $licenseKey,
    string $eventType,
    string $eventName,
    array $eventData = [],
    array $metadata = []
): array
```

### trackUsageBatch()

```php
trackUsageBatch(
    string $licenseKey,
    array $events,
    array $metadata = []
): array
```

### Helper Methods

- `trackAppOpened(string $licenseKey, string $appVersion)`
- `trackFeature(string $licenseKey, string $featureName, array $data)`
- `trackButtonClick(string $licenseKey, string $buttonName, array $data)`
- `trackError(string $licenseKey, string $errorMessage, array $data)`

### getUsageStats()

```php
getUsageStats(string $licenseKey, string $period = 'all'): array
```

**Periods:** `all`, `today`, `week`, `month`

## Testing

```bash
# Track test event
curl -X POST http://localhost:8000/test-license/track \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "BFB2-xxxxx.yyyy",
    "event_type": "feature_used",
    "event_name": "Test Feature",
    "event_data": {"test": true},
    "app_version": "1.0.0"
  }'

# Get usage stats
curl "http://localhost:8000/test-license/usage-stats?license_key=BFB2-xxxxx.yyyy&period=month"
```

## Support

For questions: https://github.com/onaonbir/oo-license/issues
