/**
 * OO-License Client SDK for JavaScript/Node.js
 *
 * Simple JavaScript client for validating and activating license keys
 *
 * @package OOLicenseClient
 * @version 1.0.0
 */

const crypto = require('crypto');
const https = require('https');
const { URL } = require('url');
const os = require('os');

class OOLicenseClient {
    /**
     * Initialize the license client
     *
     * @param {string} apiUrl - Your license server URL
     * @param {string} secretKey - Your project secret key
     */
    constructor(apiUrl, secretKey) {
        this.apiUrl = apiUrl.replace(/\/$/, '');
        this.secretKey = secretKey;
        this.deviceInfo = this.collectDeviceInfo();
    }

    /**
     * Activate a license key
     *
     * @param {string} licenseKey - The license key to activate
     * @param {string} email - User's email address
     * @returns {Promise<Object>} Response from server
     */
    async activate(licenseKey, email) {
        const encryptedDeviceInfo = this.encryptDeviceInfo(this.deviceInfo);

        return await this.makeRequest('/api/license/activate', {
            license_key: licenseKey,
            device_id: this.deviceInfo.deviceId,
            email: email,
            encrypted_device_info: encryptedDeviceInfo
        });
    }

    /**
     * Validate a license key
     *
     * @param {string} licenseKey - The license key to validate
     * @param {string} email - User's email address
     * @returns {Promise<Object>} Response from server
     */
    async validate(licenseKey, email) {
        const encryptedDeviceInfo = this.encryptDeviceInfo(this.deviceInfo);

        return await this.makeRequest('/api/license/validate', {
            license_key: licenseKey,
            device_id: this.deviceInfo.deviceId,
            email: email,
            encrypted_device_info: encryptedDeviceInfo
        });
    }

    /**
     * Check if license is valid and active
     *
     * @param {string} licenseKey
     * @param {string} email
     * @returns {Promise<boolean>}
     */
    async isValid(licenseKey, email) {
        try {
            const result = await this.validate(licenseKey, email);
            return result.success && result.isValid;
        } catch (error) {
            return false;
        }
    }

    /**
     * Get license information
     *
     * @param {string} licenseKey
     * @param {string} email
     * @returns {Promise<Object|null>}
     */
    async getLicenseInfo(licenseKey, email) {
        try {
            const result = await this.validate(licenseKey, email);
            if (result.success) {
                return {
                    isValid: result.isValid,
                    expiryDate: result.expiryDate || null,
                    features: result.features || [],
                    maxDevices: result.maxDevices || 1,
                    validationCount: result.validationCount || 0
                };
            }
            return null;
        } catch (error) {
            return null;
        }
    }

    /**
     * Collect device information
     *
     * @returns {Object}
     */
    collectDeviceInfo() {
        return {
            deviceId: this.getDeviceId(),
            hostname: os.hostname(),
            platform: os.platform(),
            arch: os.arch(),
            nodeVersion: process.version,
            timestamp: Date.now()
        };
    }

    /**
     * Generate unique device ID
     *
     * @returns {string}
     */
    getDeviceId() {
        const networkInterfaces = os.networkInterfaces();
        let macAddress = null;

        // Try to get MAC address
        for (const iface of Object.values(networkInterfaces)) {
            for (const alias of iface) {
                if (alias.mac && alias.mac !== '00:00:00:00:00:00') {
                    macAddress = alias.mac;
                    break;
                }
            }
            if (macAddress) break;
        }

        // Fallback: Use hostname + platform
        if (!macAddress) {
            macAddress = `${os.hostname()}_${os.platform()}`;
        }

        return crypto.createHash('sha256').update(macAddress).digest('hex');
    }

    /**
     * Encrypt device information
     *
     * @param {Object} deviceInfo
     * @returns {string}
     */
    encryptDeviceInfo(deviceInfo) {
        const data = JSON.stringify(deviceInfo);
        const iv = crypto.randomBytes(16);
        const key = Buffer.from(this.secretKey.padEnd(32, '0').substr(0, 32));

        const cipher = crypto.createCipheriv('aes-256-cbc', key, iv);
        let encrypted = cipher.update(data, 'utf8', 'base64');
        encrypted += cipher.final('base64');

        return Buffer.from(iv).toString('base64') + ':' + encrypted;
    }

    /**
     * Make HTTP request to license server
     *
     * @param {string} endpoint
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    makeRequest(endpoint, data) {
        return new Promise((resolve, reject) => {
            const url = new URL(this.apiUrl + endpoint);
            const postData = JSON.stringify(data);

            const options = {
                hostname: url.hostname,
                port: url.port || (url.protocol === 'https:' ? 443 : 80),
                path: url.pathname,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': Buffer.byteLength(postData),
                    'Accept': 'application/json'
                }
            };

            const protocol = url.protocol === 'https:' ? https : require('http');

            const req = protocol.request(options, (res) => {
                let body = '';

                res.on('data', (chunk) => {
                    body += chunk;
                });

                res.on('end', () => {
                    try {
                        const result = JSON.parse(body);

                        if (res.statusCode >= 400) {
                            reject(new Error(result.message || 'License validation failed'));
                        } else {
                            resolve(result);
                        }
                    } catch (error) {
                        reject(new Error('Invalid JSON response from server'));
                    }
                });
            });

            req.on('error', (error) => {
                reject(error);
            });

            req.write(postData);
            req.end();
        });
    }

    /**
     * Track usage event
     *
     * @param {string} licenseKey
     * @param {string} eventType - Event category: app_opened, feature_used, button_clicked, error_occurred, custom
     * @param {string} eventName - Descriptive name
     * @param {Object} eventData - Custom event data
     * @param {Object} metadata - Additional metadata
     * @returns {Promise<Object>}
     */
    async trackUsage(licenseKey, eventType, eventName, eventData = {}, metadata = {}) {
        return await this.makeRequest('/api/license/track', {
            license_key: licenseKey,
            event_type: eventType,
            event_name: eventName,
            event_data: eventData,
            metadata: metadata
        });
    }

    /**
     * Track multiple events at once (batch)
     *
     * @param {string} licenseKey
     * @param {Array} events - Array of events
     * @param {Object} metadata - Common metadata
     * @returns {Promise<Object>}
     */
    async trackUsageBatch(licenseKey, events, metadata = {}) {
        return await this.makeRequest('/api/license/track-batch', {
            license_key: licenseKey,
            events: events,
            metadata: metadata
        });
    }

    /**
     * Helper: Track app opened
     *
     * @param {string} licenseKey
     * @param {string} appVersion
     * @returns {Promise<Object>}
     */
    async trackAppOpened(licenseKey, appVersion = '1.0.0') {
        return await this.trackUsage(
            licenseKey,
            'app_opened',
            'Application Opened',
            {},
            { app_version: appVersion }
        );
    }

    /**
     * Helper: Track feature usage
     *
     * @param {string} licenseKey
     * @param {string} featureName
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async trackFeature(licenseKey, featureName, data = {}) {
        return await this.trackUsage(
            licenseKey,
            'feature_used',
            featureName,
            data
        );
    }

    /**
     * Helper: Track button click
     *
     * @param {string} licenseKey
     * @param {string} buttonName
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async trackButtonClick(licenseKey, buttonName, data = {}) {
        return await this.trackUsage(
            licenseKey,
            'button_clicked',
            buttonName,
            data
        );
    }

    /**
     * Helper: Track error
     *
     * @param {string} licenseKey
     * @param {string} errorMessage
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async trackError(licenseKey, errorMessage, data = {}) {
        return await this.trackUsage(
            licenseKey,
            'error_occurred',
            errorMessage,
            data
        );
    }

    /**
     * Get usage statistics
     *
     * @param {string} licenseKey
     * @param {string} period - all, today, week, month
     * @returns {Promise<Object>}
     */
    async getUsageStats(licenseKey, period = 'all') {
        const url = new URL(this.apiUrl + '/api/license/usage-stats');
        url.searchParams.append('license_key', licenseKey);
        url.searchParams.append('period', period);

        return new Promise((resolve, reject) => {
            const protocol = url.protocol === 'https:' ? https : require('http');

            const req = protocol.get(url, (res) => {
                let body = '';

                res.on('data', (chunk) => {
                    body += chunk;
                });

                res.on('end', () => {
                    try {
                        const result = JSON.parse(body);

                        if (res.statusCode >= 400) {
                            reject(new Error(result.message || 'Failed to get usage stats'));
                        } else {
                            resolve(result);
                        }
                    } catch (error) {
                        reject(new Error('Invalid JSON response'));
                    }
                });
            });

            req.on('error', reject);
            req.end();
        });
    }
}

module.exports = OOLicenseClient;
