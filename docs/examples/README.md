# OO-License Usage Examples

This directory contains complete working examples for different platforms.

## Available Examples

### 1. PHP Desktop Application
**File:** `php-example.php`

A complete CLI application that:
- Checks for existing license on startup
- Prompts for activation if needed
- Validates license periodically
- Shows feature-based access control

**Run:**
```bash
php php-example.php
```

### 2. JavaScript/Node.js Application
**File:** `javascript-example.js`

An Electron-ready application that:
- Validates license on startup
- Interactive CLI activation
- Config file management
- Feature checking

**Run:**
```bash
node javascript-example.js
```

### 3. Flutter Mobile/Desktop App
**File:** `flutter-example.dart`

A complete Flutter app with:
- Splash screen with license check
- Beautiful activation UI
- SharedPreferences storage
- Feature display
- Works on iOS, Android, Windows, macOS, Linux

**Run:**
```bash
flutter run flutter-example.dart
```

## Common Use Cases

### Use Case 1: Desktop Application

Protect your desktop application (Electron, PHP-GTK, Flutter Desktop):

1. User downloads your app
2. On first launch, app prompts for license key
3. License is activated and saved locally
4. On subsequent launches, license is validated
5. Periodic validation (daily/weekly)

### Use Case 2: Mobile Application

Protect your mobile app (Flutter, React Native):

1. User installs app from App Store/Play Store
2. App requires license activation
3. License is tied to device ID
4. Support for license transfer (max devices limit)

### Use Case 3: SaaS Backend

Protect your SaaS backend API:

1. Users get license keys from your dashboard
2. API requests include license key in headers
3. Middleware validates each request
4. Feature access based on license features

### Use Case 4: WordPress Plugin

Protect your WordPress plugin:

1. User purchases plugin
2. Plugin activation requires license key
3. Updates are locked behind license validation
4. Premium features enabled based on license

## Integration Patterns

### Pattern 1: Online Validation (Recommended)

```
[Client App] ---> [Your License Server] ---> [OO-License Package]
     |                                              |
     +------ Validate on every launch -------------+
```

**Pros:**
- Real-time validation
- Can revoke licenses instantly
- Accurate usage tracking

**Cons:**
- Requires internet connection

### Pattern 2: Offline Validation (Advanced)

```
[Client App] ---> [Cached License Info]
     |
     +------ Periodic online check (weekly)
```

**Pros:**
- Works offline
- Better user experience

**Cons:**
- Delay in license revocation
- Needs offline key generator (Phase 6 feature)

### Pattern 3: Hybrid Validation

```
[Client App] ---> [Try Online] ---> [Fallback to Cache]
```

**Pros:**
- Best of both worlds
- Graceful degradation

## Security Best Practices

1. **Never hardcode secret keys** in your client app
   - Use environment variables
   - Obfuscate keys in production builds

2. **Use HTTPS** for all API communication

3. **Implement rate limiting** on your server

4. **Hash device IDs** before sending to server

5. **Encrypt license storage** on client side

6. **Validate periodically**, not just once

## Testing Your Integration

1. Test successful activation
2. Test invalid license key
3. Test expired license
4. Test max devices reached
5. Test offline mode (if applicable)
6. Test license revocation
7. Test device transfer

## Support

Need help? Check:
- [Server Documentation](../../README.md)
- [API Reference](../API.md)
- [GitHub Issues](https://github.com/onaonbir/oo-license/issues)
