import 'dart:convert';
import 'dart:io';
import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;
import 'package:device_info_plus/device_info_plus.dart';
import 'package:encrypt/encrypt.dart' as encrypt;

/// OO-License Client SDK for Dart/Flutter
///
/// Simple Dart/Flutter client for validating and activating license keys
///
/// ```dart
/// final client = OOLicenseClient(
///   'https://license.example.com',
///   'your-secret-key',
/// );
///
/// final result = await client.activate('BFB2-xxxxx.yyyy', 'user@example.com');
/// if (result['success']) {
///   print('License activated!');
/// }
/// ```
class OOLicenseClient {
  final String apiUrl;
  final String secretKey;
  Map<String, dynamic>? _deviceInfo;

  OOLicenseClient(this.apiUrl, this.secretKey);

  /// Activate a license key
  Future<Map<String, dynamic>> activate(String licenseKey, String email) async {
    final deviceInfo = await _getDeviceInfo();
    final encryptedDeviceInfo = _encryptDeviceInfo(deviceInfo);

    return await _makeRequest('/api/license/activate', {
      'license_key': licenseKey,
      'device_id': deviceInfo['deviceId'],
      'email': email,
      'encrypted_device_info': encryptedDeviceInfo,
    });
  }

  /// Validate a license key
  Future<Map<String, dynamic>> validate(String licenseKey, String email) async {
    final deviceInfo = await _getDeviceInfo();
    final encryptedDeviceInfo = _encryptDeviceInfo(deviceInfo);

    return await _makeRequest('/api/license/validate', {
      'license_key': licenseKey,
      'device_id': deviceInfo['deviceId'],
      'email': email,
      'encrypted_device_info': encryptedDeviceInfo,
    });
  }

  /// Check if license is valid (simple boolean check)
  Future<bool> isValid(String licenseKey, String email) async {
    try {
      final result = await validate(licenseKey, email);
      return result['success'] == true && result['isValid'] == true;
    } catch (e) {
      return false;
    }
  }

  /// Get detailed license information
  Future<Map<String, dynamic>?> getLicenseInfo(
      String licenseKey, String email) async {
    try {
      final result = await validate(licenseKey, email);
      if (result['success'] == true) {
        return {
          'isValid': result['isValid'],
          'expiryDate': result['expiryDate'],
          'features': result['features'] ?? [],
          'maxDevices': result['maxDevices'] ?? 1,
          'validationCount': result['validationCount'] ?? 0,
        };
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  /// Collect device information
  Future<Map<String, dynamic>> _getDeviceInfo() async {
    if (_deviceInfo != null) return _deviceInfo!;

    final deviceInfoPlugin = DeviceInfoPlugin();
    String deviceId;
    String platform;
    String? model;
    String? version;

    if (Platform.isAndroid) {
      final androidInfo = await deviceInfoPlugin.androidInfo;
      deviceId = androidInfo.id;
      platform = 'Android';
      model = androidInfo.model;
      version = androidInfo.version.release;
    } else if (Platform.isIOS) {
      final iosInfo = await deviceInfoPlugin.iosInfo;
      deviceId = iosInfo.identifierForVendor ?? 'unknown';
      platform = 'iOS';
      model = iosInfo.model;
      version = iosInfo.systemVersion;
    } else if (Platform.isWindows) {
      final windowsInfo = await deviceInfoPlugin.windowsInfo;
      deviceId = windowsInfo.deviceId;
      platform = 'Windows';
      model = windowsInfo.computerName;
      version = windowsInfo.buildNumber.toString();
    } else if (Platform.isMacOS) {
      final macInfo = await deviceInfoPlugin.macOsInfo;
      deviceId = macInfo.systemGUID ?? 'unknown';
      platform = 'macOS';
      model = macInfo.model;
      version = macInfo.osRelease;
    } else if (Platform.isLinux) {
      final linuxInfo = await deviceInfoPlugin.linuxInfo;
      deviceId = linuxInfo.machineId ?? 'unknown';
      platform = 'Linux';
      model = linuxInfo.prettyName;
      version = linuxInfo.version;
    } else {
      deviceId = 'unknown';
      platform = Platform.operatingSystem;
    }

    // Hash device ID for privacy
    final hashedDeviceId = sha256.convert(utf8.encode(deviceId)).toString();

    _deviceInfo = {
      'deviceId': hashedDeviceId,
      'platform': platform,
      'model': model,
      'version': version,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    };

    return _deviceInfo!;
  }

  /// Encrypt device information using AES-256-CBC
  String _encryptDeviceInfo(Map<String, dynamic> deviceInfo) {
    final data = jsonEncode(deviceInfo);
    final key = encrypt.Key.fromUtf8(secretKey.padRight(32, '0').substring(0, 32));
    final iv = encrypt.IV.fromSecureRandom(16);

    final encrypter = encrypt.Encrypter(encrypt.AES(key, mode: encrypt.AESMode.cbc));
    final encrypted = encrypter.encrypt(data, iv: iv);

    return '${base64.encode(iv.bytes)}:${encrypted.base64}';
  }

  /// Make HTTP request to license server
  Future<Map<String, dynamic>> _makeRequest(
      String endpoint, Map<String, dynamic> data) async {
    final url = Uri.parse('${apiUrl.replaceAll(RegExp(r'/$'), '')}$endpoint');

    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(data),
      );

      final result = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode >= 400) {
        throw LicenseException(
          result['message'] ?? 'License validation failed',
          result['error'] ?? 'UNKNOWN_ERROR',
        );
      }

      return result;
    } catch (e) {
      if (e is LicenseException) rethrow;
      throw LicenseException('Network error: ${e.toString()}', 'NETWORK_ERROR');
    }
  }
}

  /// Track usage event
  Future<Map<String, dynamic>> trackUsage(
    String licenseKey,
    String eventType,
    String eventName, {
    Map<String, dynamic>? eventData,
    Map<String, dynamic>? metadata,
  }) async {
    return await _makeRequest('/api/license/track', {
      'license_key': licenseKey,
      'event_type': eventType,
      'event_name': eventName,
      'event_data': eventData ?? {},
      'metadata': metadata ?? {},
    });
  }

  /// Track multiple events at once (batch)
  Future<Map<String, dynamic>> trackUsageBatch(
    String licenseKey,
    List<Map<String, dynamic>> events, {
    Map<String, dynamic>? metadata,
  }) async {
    return await _makeRequest('/api/license/track-batch', {
      'license_key': licenseKey,
      'events': events,
      'metadata': metadata ?? {},
    });
  }

  /// Helper: Track app opened
  Future<Map<String, dynamic>> trackAppOpened(
    String licenseKey, {
    String appVersion = '1.0.0',
  }) async {
    return await trackUsage(
      licenseKey,
      'app_opened',
      'Application Opened',
      metadata: {'app_version': appVersion},
    );
  }

  /// Helper: Track feature usage
  Future<Map<String, dynamic>> trackFeature(
    String licenseKey,
    String featureName, {
    Map<String, dynamic>? data,
  }) async {
    return await trackUsage(
      licenseKey,
      'feature_used',
      featureName,
      eventData: data,
    );
  }

  /// Helper: Track button click
  Future<Map<String, dynamic>> trackButtonClick(
    String licenseKey,
    String buttonName, {
    Map<String, dynamic>? data,
  }) async {
    return await trackUsage(
      licenseKey,
      'button_clicked',
      buttonName,
      eventData: data,
    );
  }

  /// Helper: Track error
  Future<Map<String, dynamic>> trackError(
    String licenseKey,
    String errorMessage, {
    Map<String, dynamic>? data,
  }) async {
    return await trackUsage(
      licenseKey,
      'error_occurred',
      errorMessage,
      eventData: data,
    );
  }

  /// Get usage statistics
  Future<Map<String, dynamic>> getUsageStats(
    String licenseKey, {
    String period = 'all',
  }) async {
    final url = Uri.parse(
      '${apiUrl.replaceAll(RegExp(r'/$'), '')}/api/license/usage-stats'
          '?license_key=$licenseKey&period=$period',
    );

    try {
      final response = await http.get(
        url,
        headers: {'Accept': 'application/json'},
      );

      final result = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode >= 400) {
        throw LicenseException(
          result['message'] ?? 'Failed to get usage stats',
          'STATS_ERROR',
        );
      }

      return result;
    } catch (e) {
      if (e is LicenseException) rethrow;
      throw LicenseException('Network error: ${e.toString()}', 'NETWORK_ERROR');
    }
  }
}

/// Custom exception for license errors
class LicenseException implements Exception {
  final String message;
  final String errorCode;

  LicenseException(this.message, this.errorCode);

  @override
  String toString() => 'LicenseException: $message ($errorCode)';
}
