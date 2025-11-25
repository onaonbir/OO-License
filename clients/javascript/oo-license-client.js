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
}

module.exports = OOLicenseClient;
