/**
 * Flutter Example - Mobile/Desktop Application with License Protection
 *
 * Complete Flutter app example with license activation and validation
 */

import 'package:flutter/material.dart';
import 'package:oo_license_client/oo_license_client.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  runApp(MyLicensedApp());
}

class MyLicensedApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'My Licensed App',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        useMaterial3: true,
      ),
      home: SplashScreen(),
    );
  }
}

/// Splash screen - Check license on startup
class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  final client = OOLicenseClient(
    'https://license.example.com',
    'your-secret-key',
  );

  @override
  void initState() {
    super.initState();
    _checkLicense();
  }

  Future<void> _checkLicense() async {
    await Future.delayed(Duration(seconds: 1)); // Splash delay

    final prefs = await SharedPreferences.getInstance();
    final licenseKey = prefs.getString('license_key');
    final email = prefs.getString('email');

    if (licenseKey == null || email == null) {
      _navigateToActivation();
      return;
    }

    try {
      final isValid = await client.isValid(licenseKey, email);

      if (isValid) {
        _navigateToHome();
      } else {
        _navigateToActivation();
      }
    } catch (e) {
      _showError('License check failed: $e');
      _navigateToActivation();
    }
  }

  void _navigateToActivation() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => ActivationScreen()),
    );
  }

  void _navigateToHome() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => HomeScreen()),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blue,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.vpn_key, size: 100, color: Colors.white),
            SizedBox(height: 20),
            Text(
              'My Licensed App',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            SizedBox(height: 20),
            CircularProgressIndicator(color: Colors.white),
          ],
        ),
      ),
    );
  }
}

/// License activation screen
class ActivationScreen extends StatefulWidget {
  @override
  _ActivationScreenState createState() => _ActivationScreenState();
}

class _ActivationScreenState extends State<ActivationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _licenseController = TextEditingController();
  final _emailController = TextEditingController();
  final client = OOLicenseClient(
    'https://license.example.com',
    'your-secret-key',
  );

  bool _isActivating = false;

  Future<void> _activate() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isActivating = true);

    try {
      final result = await client.activate(
        _licenseController.text.trim(),
        _emailController.text.trim(),
      );

      if (result['success']) {
        // Save license
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('license_key', _licenseController.text.trim());
        await prefs.setString('email', _emailController.text.trim());
        await prefs.setString('features', jsonEncode(result['features']));

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('✓ License activated successfully!'),
            backgroundColor: Colors.green,
          ),
        );

        // Navigate to home
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => HomeScreen()),
        );
      }
    } on LicenseException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ ${e.message}'),
          backgroundColor: Colors.red,
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ Activation failed: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isActivating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Activate License'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Icon(Icons.vpn_key, size: 80, color: Colors.blue),
              SizedBox(height: 32),
              Text(
                'Activate Your License',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 8),
              Text(
                'Enter your license key and email to activate',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
              SizedBox(height: 32),
              TextFormField(
                controller: _emailController,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your email';
                  }
                  if (!value.contains('@')) {
                    return 'Please enter a valid email';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _licenseController,
                decoration: InputDecoration(
                  labelText: 'License Key',
                  prefixIcon: Icon(Icons.key),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your license key';
                  }
                  return null;
                },
              ),
              SizedBox(height: 32),
              ElevatedButton(
                onPressed: _isActivating ? null : _activate,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isActivating
                    ? SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : Text(
                        'Activate License',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _licenseController.dispose();
    _emailController.dispose();
    super.dispose();
  }
}

/// Main home screen (only accessible with valid license)
class HomeScreen extends StatefulWidget {
  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _licenseInfo;
  List<String> _features = [];

  @override
  void initState() {
    super.initState();
    _loadLicenseInfo();
  }

  Future<void> _loadLicenseInfo() async {
    final prefs = await SharedPreferences.getInstance();
    final featuresJson = prefs.getString('features');

    setState(() {
      _features = featuresJson != null
          ? List<String>.from(jsonDecode(featuresJson))
          : [];
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('My Licensed App'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(Icons.info_outline),
            onPressed: _showLicenseInfo,
          ),
        ],
      ),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'License Status',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 12),
                    Row(
                      children: [
                        Icon(Icons.check_circle, color: Colors.green),
                        SizedBox(width: 8),
                        Text('Active', style: TextStyle(color: Colors.green)),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(height: 16),
            Text(
              'Enabled Features',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _features.map((feature) {
                return Chip(
                  label: Text(feature),
                  backgroundColor: Colors.blue[100],
                );
              }).toList(),
            ),
            SizedBox(height: 24),
            // Your app content here
            Expanded(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.celebration, size: 100, color: Colors.blue),
                    SizedBox(height: 16),
                    Text(
                      'Welcome to My Licensed App!',
                      style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Your license is active and all features are enabled.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showLicenseInfo() async {
    final prefs = await SharedPreferences.getInstance();
    final email = prefs.getString('email');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('License Information'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Email: $email'),
            SizedBox(height: 8),
            Text('Features: ${_features.length}'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Close'),
          ),
        ],
      ),
    );
  }
}
