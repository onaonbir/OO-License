# OO-License Dart/Flutter Client

Cross-platform Dart/Flutter client for validating and activating license keys.

## Supported Platforms

- ✅ Android
- ✅ iOS
- ✅ Windows
- ✅ macOS
- ✅ Linux
- ✅ Web (coming soon)

## Installation

Add to your `pubspec.yaml`:

```yaml
dependencies:
  oo_license_client: ^1.0.0
```

Or use the local client:

```yaml
dependencies:
  oo_license_client:
    path: ./oo_license_client
```

Then run:

```bash
flutter pub get
```

## Quick Start

```dart
import 'package:oo_license_client/oo_license_client.dart';

void main() async {
  final client = OOLicenseClient(
    'https://your-license-server.com',
    'your-project-secret-key',
  );

  try {
    final result = await client.activate(
      'BFB2-xxxxx.yyyy',
      'user@example.com',
    );

    if (result['success']) {
      print('✓ License activated!');
      print('Expires: ${result['expiryDate']}');
      print('Features: ${result['features']}');
    }
  } catch (e) {
    print('❌ Activation failed: $e');
  }
}
```

## Complete Flutter Example

```dart
import 'package:flutter/material.dart';
import 'package:oo_license_client/oo_license_client.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Licensed App',
      home: LicenseCheckScreen(),
    );
  }
}

class LicenseCheckScreen extends StatefulWidget {
  @override
  _LicenseCheckScreenState createState() => _LicenseCheckScreenState();
}

class _LicenseCheckScreenState extends State<LicenseCheckScreen> {
  final client = OOLicenseClient(
    'https://license.example.com',
    'your-secret-key',
  );

  bool _isLoading = true;
  bool _isValid = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _checkLicense();
  }

  Future<void> _checkLicense() async {
    setState(() => _isLoading = true);

    final prefs = await SharedPreferences.getInstance();
    final licenseKey = prefs.getString('license_key');
    final email = prefs.getString('email');

    if (licenseKey == null || email == null) {
      setState(() {
        _isLoading = false;
        _isValid = false;
        _errorMessage = 'No license found';
      });
      return;
    }

    try {
      final isValid = await client.isValid(licenseKey, email);

      setState(() {
        _isLoading = false;
        _isValid = isValid;
        _errorMessage = isValid ? null : 'License invalid or expired';
      });

      if (isValid) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => MainAppScreen()),
        );
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _isValid = false;
        _errorMessage = e.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (!_isValid) {
      return LicenseActivationScreen(onActivated: _checkLicense);
    }

    return MainAppScreen();
  }
}

class LicenseActivationScreen extends StatefulWidget {
  final VoidCallback onActivated;

  LicenseActivationScreen({required this.onActivated});

  @override
  _LicenseActivationScreenState createState() => _LicenseActivationScreenState();
}

class _LicenseActivationScreenState extends State<LicenseActivationScreen> {
  final _licenseController = TextEditingController();
  final _emailController = TextEditingController();
  final client = OOLicenseClient(
    'https://license.example.com',
    'your-secret-key',
  );

  bool _isActivating = false;
  String? _errorMessage;

  Future<void> _activate() async {
    setState(() {
      _isActivating = true;
      _errorMessage = null;
    });

    try {
      final result = await client.activate(
        _licenseController.text.trim(),
        _emailController.text.trim(),
      );

      if (result['success']) {
        // Save license info
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('license_key', _licenseController.text.trim());
        await prefs.setString('email', _emailController.text.trim());

        widget.onActivated();
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });
    } finally {
      setState(() => _isActivating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Activate License')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            TextField(
              controller: _emailController,
              decoration: InputDecoration(
                labelText: 'Email',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.emailAddress,
            ),
            SizedBox(height: 16),
            TextField(
              controller: _licenseController,
              decoration: InputDecoration(
                labelText: 'License Key',
                border: OutlineInputBorder(),
              ),
            ),
            SizedBox(height: 24),
            if (_errorMessage != null)
              Padding(
                padding: EdgeInsets.only(bottom: 16),
                child: Text(
                  _errorMessage!,
                  style: TextStyle(color: Colors.red),
                ),
              ),
            ElevatedButton(
              onPressed: _isActivating ? null : _activate,
              child: _isActivating
                  ? CircularProgressIndicator(color: Colors.white)
                  : Text('Activate'),
            ),
          ],
        ),
      ),
    );
  }
}

class MainAppScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('My App')),
      body: Center(
        child: Text('Welcome to the licensed app!'),
      ),
    );
  }
}
```

## API Reference

See [main README](../../README.md) for detailed API documentation.

## Requirements

- Dart SDK >=3.0.0
- Flutter SDK (for Flutter apps)

## Support

For issues and questions: https://github.com/onaonbir/oo-license/issues
