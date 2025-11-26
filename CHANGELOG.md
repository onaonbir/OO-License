# Changelog

All notable changes to OO-License will be documented in this file.

## [1.0.0] - 2024-11-26

### Added

#### Core Package
- Complete Laravel package with auto-discovery
- 5 database migrations with proper indexing
- 6 Eloquent models with relationships
- Configurable settings via `config/oo-license.php`
- ServiceProvider with singleton services

#### License Management
- Project creation and management
- User management per project
- License key generation with 2 built-in generators:
  - BfbKeyGeneratorV1: Simple hash-based keys
  - BfbKeyGeneratorV2: HMAC-signed payload keys
- Custom key generator support via registry pattern
- Multi-device activation support (configurable device limits)
- Device-based license validation
- Hardware locking with AES-256-CBC encrypted device info
- Feature-based licensing (enable/disable features per key)
- Expiration management with date-based validation
- License revocation
- Device deactivation

#### Usage Tracking & Analytics
- `ProjectUserKeyUsage` model for tracking events
- Event types: app_opened, feature_used, button_clicked, error_occurred, custom
- Flexible JSON data storage for any custom event data
- Batch event tracking for performance
- Helper methods:
  - `trackAppOpened()` - Track application launches
  - `trackFeatureUsage()` - Track feature usage
  - `trackError()` - Track errors and crashes
  - `trackUsage()` - Track custom events
  - `trackUsageBatch()` - Bulk event tracking
- Usage statistics with period filtering (all, today, week, month)
- Event aggregation by type

#### Client SDKs

**PHP Client** (`clients/php/OOLicenseClient.php`)
- License activation and validation
- Usage event tracking (single and batch)
- Helper methods for common events
- Usage statistics retrieval
- Device ID generation
- AES-256-CBC encryption
- cURL-based HTTP client

**JavaScript/Node.js Client** (`clients/javascript/oo-license-client.js`)
- Full async/await support
- License activation and validation
- Usage tracking and analytics
- Batch event tracking
- Electron app integration support
- Express.js middleware examples
- Native HTTPS client
- Device info collection

**Dart/Flutter Client** (`clients/dart/lib/oo_license_client.dart`)
- Cross-platform support (iOS, Android, Windows, macOS, Linux)
- License activation and validation
- Usage tracking with all event types
- Batch tracking support
- Flutter-specific integration examples
- device_info_plus integration
- Encrypt package for AES encryption

#### Exception Handling
- `LicenseException` - Base exception class
- `InvalidKeyException` - License key not found
- `LicenseExpiredException` - Key has expired
- `KeyInactiveException` - Key is revoked/inactive
- `MaxDevicesReachedException` - Device limit reached
- `DeviceMismatchException` - Device info mismatch
- `DeviceNotActivatedException` - Device not activated

#### User Interface (Laravel Test App)
- Beautiful Blade-based dashboard at `/licenses`
- Project listing with stats cards
- Project detail page with users and keys
- Create project modal with validation
- Add user modal
- Generate license key modal with custom options
- Usage analytics dashboard (`/licenses/keys/{key}/usage-logs`)
  - Real-time stats (total, today, week, month)
  - Event type breakdown
  - Event timeline with details
  - Custom data display
- API Playground (`/licenses/playground`)
  - Interactive forms for activation, validation, revocation
  - Usage tracking test forms
  - Quick track buttons
  - Batch tracking demo
  - Real-time response display

#### Documentation
- Comprehensive main README with quick start
- Usage Tracking guide (`docs/USAGE_TRACKING.md`)
  - Event type explanations
  - Best practices
  - Privacy considerations
  - Real-world examples for all platforms
- API Endpoints reference (`docs/API_ENDPOINTS.md`)
  - Complete endpoint documentation
  - Request/response examples
  - Error codes reference
- Platform-specific guides:
  - PHP Client guide with 7 usage examples
  - JavaScript guide with Electron integration
  - Dart/Flutter guide with mobile examples
- Example projects:
  - `php-example.php` - Complete CLI app
  - `javascript-example.js` - Electron app
  - `flutter-example.dart` - Mobile app
- CHANGELOG.md for version tracking

#### Test Routes
- 10+ test endpoints in `routes/license.php`:
  - `/test-license/create-project` - Create test project
  - `/test-license/generate-key` - Generate license key
  - `/test-license/activate` - Activate license
  - `/test-license/validate` - Validate license
  - `/test-license/list-all` - List all licenses
  - `/test-license/revoke` - Revoke license
  - `/test-license/deactivate-device` - Deactivate device
  - `/test-license/track` - Track usage event
  - `/test-license/track-batch` - Batch track events
  - `/test-license/usage-stats` - Get usage statistics

#### Security Features
- AES-256-CBC encryption for device information
- HMAC-SHA256 signatures for v2 keys
- Laravel's encrypted casting for sensitive data
- Timing-safe signature comparison
- Device ID hashing (SHA-256)
- Secure random key generation

### Technical Details

**Models & Relationships:**
- Project → hasMany → ProjectUser
- ProjectUser → hasMany → ProjectUserKey
- ProjectUserKey → hasMany → ProjectUserKeyActivation
- ProjectUserKey → hasMany → ProjectUserKeyUsage
- ProjectUserKeyActivation → hasMany → ProjectUserKeyValidation

**Database Tables:**
- `projects` - 14 columns with UUID primary key
- `project_users` - 7 columns with cascading deletes
- `project_user_keys` - 15 columns with device limits
- `project_user_key_activations` - 6 columns with unique device constraint
- `project_user_key_validations` - 10 columns for audit trail
- `project_user_key_usages` - 7 columns for analytics

**Supported Platforms:**
- Laravel 10.x, 11.x, 12.x
- PHP 8.3+
- Node.js 14.0.0+
- Dart SDK 3.0.0+
- Flutter (all platforms)

### File Count
- 39+ source files
- 6 migrations
- 6 models
- 7 exceptions
- 3 client SDKs (PHP, JS, Dart)
- 10+ documentation files
- 3 complete example projects

## Future Roadmap

### [1.1.0] - Events & Commands (Planned)
- Laravel events for key lifecycle
- Artisan commands for maintenance
- Middleware for route protection
- Model observers

### [1.2.0] - Advanced Analytics (Planned)
- Charts and visualizations
- Export reports (CSV, PDF)
- Custom dashboards
- Usage predictions

### [1.3.0] - License Types (Planned)
- Trial licenses
- Lifetime licenses
- Subscription-based licenses
- Auto-renewal
- Grace periods

### [1.4.0] - Webhooks (Planned)
- Webhook endpoints
- Event notifications
- Delivery retry logic
- Webhook logs

### [1.5.0] - Security Enhancements (Planned)
- IP whitelisting
- Fraud detection
- Offline validation
- RSA encryption option

## Support

- GitHub: https://github.com/onaonbir/oo-license
- Issues: https://github.com/onaonbir/oo-license/issues
- Email: berkaybariskan0208@gmail.com
