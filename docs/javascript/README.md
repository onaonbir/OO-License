# OO-License JavaScript/Node.js Client

Simple JavaScript client for validating and activating license keys with the OO-License system.

## Installation

### Node.js

```bash
npm install oo-license-client
```

Or copy the `oo-license-client.js` file to your project:

```javascript
const OOLicenseClient = require('./oo-license-client');
```

### Browser (Coming Soon)

```html
<script src="oo-license-client.browser.js"></script>
```

## Quick Start

```javascript
const OOLicenseClient = require('oo-license-client');

// Initialize client
const client = new OOLicenseClient(
    'https://your-license-server.com',  // Your license server URL
    'your-project-secret-key'            // Your project secret key
);

// Activate license
(async () => {
    try {
        const result = await client.activate(
            'BFB2-xxxxx.yyyy',          // License key
            'user@example.com'           // User email
        );

        if (result.success) {
            console.log('✓ License activated successfully!');
            console.log('Expires:', result.expiryDate);
            console.log('Features:', result.features);
        }
    } catch (error) {
        console.error('❌ Activation failed:', error.message);
    }
})();
```

## Usage Examples

### 1. Activate License

```javascript
const client = new OOLicenseClient('https://license.example.com', 'secret-key');

async function activateLicense() {
    try {
        const result = await client.activate('BFB2-xxxxx.yyyy', 'user@example.com');

        if (result.success) {
            // Save license info to file or database
            const fs = require('fs');
            fs.writeFileSync('license.json', JSON.stringify(result, null, 2));

            console.log('✓ License activated!');
            console.log('Features:', result.features.join(', '));
            console.log('Max Devices:', result.maxDevices);
            console.log('Expires:', result.expiryDate || 'Never');
        }
    } catch (error) {
        console.error('❌ Activation failed:', error.message);
        process.exit(1);
    }
}

activateLicense();
```

### 2. Validate License

```javascript
async function validateLicense() {
    try {
        const result = await client.validate('BFB2-xxxxx.yyyy', 'user@example.com');

        if (result.isValid) {
            console.log('✓ License is valid');
            console.log('Validation count:', result.validationCount);
        } else {
            console.log('❌ License is not valid');
        }
    } catch (error) {
        console.error('Validation error:', error.message);
    }
}

validateLicense();
```

### 3. Check License Validity (Simple)

```javascript
async function checkAccess() {
    const isValid = await client.isValid('BFB2-xxxxx.yyyy', 'user@example.com');

    if (isValid) {
        console.log('Access granted!');
        // Proceed with protected features
    } else {
        console.log('Access denied. Please activate your license.');
        process.exit(1);
    }
}

checkAccess();
```

### 4. Get License Information

```javascript
async function showLicenseInfo() {
    const info = await client.getLicenseInfo('BFB2-xxxxx.yyyy', 'user@example.com');

    if (info) {
        console.log('License Status:', info.isValid ? 'Active' : 'Inactive');
        console.log('Features:', info.features.join(', '));
        console.log('Max Devices:', info.maxDevices);
        console.log('Expires:', info.expiryDate || 'Never');
    } else {
        console.log('Could not retrieve license information');
    }
}

showLicenseInfo();
```

### 5. Protect Your Application

```javascript
const OOLicenseClient = require('oo-license-client');
const fs = require('fs');

class MyApp {
    constructor() {
        this.licenseClient = new OOLicenseClient(
            'https://license.example.com',
            'your-secret-key'
        );

        // Load from config file
        const config = JSON.parse(fs.readFileSync('config.json', 'utf8'));
        this.licenseKey = config.license_key;
        this.userEmail = config.email;
    }

    async start() {
        // Validate license on startup
        if (!(await this.validateLicense())) {
            console.error('Invalid or expired license. Please contact support.');
            process.exit(1);
        }

        // Run your application
        this.run();
    }

    async validateLicense() {
        try {
            return await this.licenseClient.isValid(this.licenseKey, this.userEmail);
        } catch (error) {
            console.error('License validation error:', error.message);
            return false;
        }
    }

    run() {
        console.log('Application started successfully!');
        // Your application logic here
    }
}

// Run application
const app = new MyApp();
app.start();
```

### 6. Feature-Based Access Control

```javascript
async function checkFeatures() {
    const info = await client.getLicenseInfo('BFB2-xxxxx.yyyy', 'user@example.com');

    if (info && info.isValid) {
        const features = info.features;

        // Check if user has access to specific features
        if (features.includes('premium_support')) {
            console.log('✓ Premium support enabled');
        }

        if (features.includes('api_access')) {
            console.log('✓ API access enabled');
        }

        if (features.includes('advanced_features')) {
            console.log('✓ Advanced features enabled');
        }
    } else {
        console.log('Please upgrade your license to access these features');
    }
}

checkFeatures();
```

### 7. Express.js Middleware

```javascript
const express = require('express');
const OOLicenseClient = require('oo-license-client');

const app = express();
const client = new OOLicenseClient('https://license.example.com', 'secret-key');

// License validation middleware
async function validateLicenseMiddleware(req, res, next) {
    const { license_key, email } = req.headers;

    if (!license_key || !email) {
        return res.status(401).json({ error: 'License key and email required' });
    }

    try {
        const isValid = await client.isValid(license_key, email);

        if (isValid) {
            next();
        } else {
            res.status(403).json({ error: 'Invalid or expired license' });
        }
    } catch (error) {
        res.status(500).json({ error: 'License validation failed' });
    }
}

// Protected route
app.get('/api/protected', validateLicenseMiddleware, (req, res) => {
    res.json({ message: 'Access granted to protected resource' });
});

app.listen(3000, () => {
    console.log('Server running on port 3000');
});
```

### 8. Periodic License Check (Scheduled Task)

```javascript
const OOLicenseClient = require('oo-license-client');
const fs = require('fs');
const cron = require('node-cron');

const client = new OOLicenseClient('https://license.example.com', 'secret-key');

// Run daily at midnight
cron.schedule('0 0 * * *', async () => {
    const config = JSON.parse(fs.readFileSync('config.json', 'utf8'));

    try {
        const result = await client.validate(config.license_key, config.email);

        if (result.isValid) {
            // Update last check timestamp
            config.last_check = new Date().toISOString();
            config.status = 'active';
            fs.writeFileSync('config.json', JSON.stringify(config, null, 2));

            console.log('License check passed ✓');
        } else {
            config.status = 'invalid';
            fs.writeFileSync('config.json', JSON.stringify(config, null, 2));

            // Send notification
            console.error('License check failed ❌');
            // Send email, Slack notification, etc.
        }
    } catch (error) {
        console.error('License check error:', error.message);
    }
});
```

### 9. Electron App Integration

```javascript
// main.js (Electron Main Process)
const { app, BrowserWindow, ipcMain } = require('electron');
const OOLicenseClient = require('oo-license-client');

const client = new OOLicenseClient('https://license.example.com', 'secret-key');

let mainWindow;

app.on('ready', async () => {
    // Validate license on app startup
    const isValid = await client.isValid('BFB2-xxxxx.yyyy', 'user@example.com');

    if (!isValid) {
        // Show license activation window
        showActivationWindow();
    } else {
        // Show main app window
        createMainWindow();
    }
});

// Handle license activation from renderer
ipcMain.handle('activate-license', async (event, licenseKey, email) => {
    try {
        const result = await client.activate(licenseKey, email);
        return result;
    } catch (error) {
        throw error;
    }
});

function createMainWindow() {
    mainWindow = new BrowserWindow({
        width: 1200,
        height: 800,
        webPreferences: {
            nodeIntegration: true
        }
    });
    mainWindow.loadFile('index.html');
}

function showActivationWindow() {
    // Show activation window
}
```

## Error Handling

```javascript
async function handleLicenseOperation() {
    try {
        const result = await client.activate('BFB2-xxxxx.yyyy', 'user@example.com');

        if (result.success) {
            console.log('Success!');
        }
    } catch (error) {
        // Handle different error types
        const errorMsg = error.message;

        if (errorMsg.includes('INVALID_KEY')) {
            console.error('Invalid license key');
        } else if (errorMsg.includes('EXPIRED')) {
            console.error('License has expired');
        } else if (errorMsg.includes('MAX_DEVICES_REACHED')) {
            console.error('Maximum device limit reached');
        } else if (errorMsg.includes('NOT_ACTIVATED')) {
            console.error('Device not activated');
        } else {
            console.error('Error:', errorMsg);
        }
    }
}
```

## API Reference

### Constructor

```javascript
new OOLicenseClient(apiUrl, secretKey)
```

- `apiUrl` (string): Your license server URL
- `secretKey` (string): Your project secret key from the license dashboard

### Methods

#### activate()

```javascript
await client.activate(licenseKey, email)
```

Activate a license key on the current device.

**Returns:** Promise<Object>
```javascript
{
    success: true,
    isValid: true,
    expiryDate: '2025-12-31T23:59:59+00:00',
    features: ['feature1', 'feature2'],
    maxDevices: 3,
    activatedDevices: 1,
    message: 'License activated successfully'
}
```

#### validate()

```javascript
await client.validate(licenseKey, email)
```

Validate a license key.

**Returns:** Promise<Object>
```javascript
{
    success: true,
    isValid: true,
    expiryDate: '2025-12-31T23:59:59+00:00',
    features: ['feature1', 'feature2'],
    maxDevices: 3,
    validationCount: 42,
    message: 'License valid'
}
```

#### isValid()

```javascript
await client.isValid(licenseKey, email)
```

Simple boolean check if license is valid.

**Returns:** Promise<boolean>

#### getLicenseInfo()

```javascript
await client.getLicenseInfo(licenseKey, email)
```

Get detailed license information.

**Returns:** Promise<Object|null>
```javascript
{
    isValid: true,
    expiryDate: '2025-12-31T23:59:59+00:00',
    features: ['feature1', 'feature2'],
    maxDevices: 3,
    validationCount: 42
}
```

## Usage Analytics

### Track Events

```javascript
const client = new OOLicenseClient('https://license.example.com', 'secret-key');
const licenseKey = 'BFB2-xxxxx.yyyy';

// Track app opened
await client.trackAppOpened(licenseKey, '2.1.0');

// Track feature usage
await client.trackFeature(licenseKey, 'Export PDF', {
    format: 'pdf',
    pages: 10,
    quality: 'high'
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
    'Settings Changed',
    { theme: 'dark', language: 'en' },
    { screen: 'preferences' }
);
```

### Batch Tracking

```javascript
// Send multiple events at once (more efficient)
await client.trackUsageBatch(licenseKey, [
    { type: 'app_opened', name: 'App Started' },
    { type: 'feature_used', name: 'Import CSV', data: { rows: 1000 } },
    { type: 'button_clicked', name: 'Export Button' },
], { appVersion: '2.1.0', os: process.platform });
```

### Get Usage Statistics

```javascript
const stats = await client.getUsageStats(licenseKey, 'month');

console.log('Total events:', stats.total_events);
console.log('App opens:', stats.events_by_type.app_opened);
console.log('Features used:', stats.events_by_type.feature_used);
```

### Integration with Electron

```javascript
const { app, ipcMain } = require('electron');
const client = new OOLicenseClient('https://license.example.com', 'secret-key');

app.on('ready', async () => {
    await client.trackAppOpened(LICENSE_KEY, app.getVersion());
});

// Track from renderer process
ipcMain.handle('track-feature', async (event, featureName, data) => {
    return await client.trackFeature(LICENSE_KEY, featureName, data);
});
```

## Requirements

- Node.js 14.0.0 or higher
- Built-in crypto module
- Built-in https module

## Security Notes

1. **Never expose your secret key** in client-side browser code
2. Store license keys securely (encrypted storage, environment variables)
3. Implement rate limiting to prevent abuse
4. Use HTTPS for all API communication
5. Validate licenses periodically, not just on activation

## Support

For issues and questions: https://github.com/onaonbir/oo-license/issues
