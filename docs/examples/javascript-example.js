/**
 * JavaScript/Node.js Example - Electron Desktop Application
 *
 * This example shows how to protect an Electron app with OO-License
 */

const OOLicenseClient = require('../../clients/javascript/oo-license-client');
const fs = require('fs').promises;
const path = require('path');

class LicensedApp {
    constructor() {
        this.licenseClient = new OOLicenseClient(
            'https://license.yourdomain.com',
            'your-project-secret-key-here'
        );
        this.configFile = path.join(__dirname, 'license-config.json');
    }

    async run() {
        console.log('=================================');
        console.log('  My Awesome Application v1.0   ');
        console.log('=================================\n');

        // Check if license exists
        try {
            await fs.access(this.configFile);
        } catch {
            console.log('No license found. Please activate your license.\n');
            await this.activateLicense();
            return;
        }

        // Validate existing license
        if (!(await this.validateExistingLicense())) {
            console.log('License validation failed. Please re-activate.\n');
            await this.activateLicense();
            return;
        }

        // License is valid - run the app
        this.startApplication();
    }

    async activateLicense() {
        const readline = require('readline').createInterface({
            input: process.stdin,
            output: process.stdout
        });

        const question = (prompt) => new Promise((resolve) => {
            readline.question(prompt, resolve);
        });

        try {
            const licenseKey = await question('Enter your license key: ');
            const email = await question('Enter your email: ');

            const result = await this.licenseClient.activate(
                licenseKey.trim(),
                email.trim()
            );

            if (result.success) {
                // Save license config
                const config = {
                    license_key: licenseKey.trim(),
                    email: email.trim(),
                    activated_at: new Date().toISOString(),
                    features: result.features,
                    expiry_date: result.expiryDate,
                };

                await fs.writeFile(
                    this.configFile,
                    JSON.stringify(config, null, 2)
                );

                console.log('\n✓ License activated successfully!');
                console.log('Features:', result.features.join(', '));
                console.log('Expires:', result.expiryDate || 'Never');
                console.log('');

                readline.close();
                this.startApplication();
            }
        } catch (error) {
            console.error('\n❌ Activation failed:', error.message);
            readline.close();
            process.exit(1);
        }
    }

    async validateExistingLicense() {
        const configData = await fs.readFile(this.configFile, 'utf8');
        const config = JSON.parse(configData);

        try {
            const isValid = await this.licenseClient.isValid(
                config.license_key,
                config.email
            );

            if (isValid) {
                console.log('✓ License validated successfully!\n');

                // Update last check
                config.last_check = new Date().toISOString();
                await fs.writeFile(
                    this.configFile,
                    JSON.stringify(config, null, 2)
                );
            }

            return isValid;
        } catch (error) {
            console.error('❌ Validation error:', error.message);
            return false;
        }
    }

    async startApplication() {
        const configData = await fs.readFile(this.configFile, 'utf8');
        const config = JSON.parse(configData);

        console.log('=================================');
        console.log('  Application Running            ');
        console.log('=================================\n');

        console.log('Licensed to:', config.email);
        console.log('Activated on:', config.activated_at);
        console.log('Expires:', config.expiry_date || 'Never');
        console.log('');

        // Check features
        const features = config.features || [];

        if (features.includes('premium_support')) {
            console.log('✓ Premium Support: Enabled');
        }

        if (features.includes('api_access')) {
            console.log('✓ API Access: Enabled');
            this.enableApiAccess();
        }

        if (features.includes('advanced_features')) {
            console.log('✓ Advanced Features: Enabled');
            this.enableAdvancedFeatures();
        }

        console.log('\nPress Ctrl+C to exit...\n');

        // Your application logic here
        setInterval(() => {
            // App loop
        }, 1000);
    }

    enableApiAccess() {
        // Enable API functionality
    }

    enableAdvancedFeatures() {
        // Enable advanced features
    }
}

// Run the application
const app = new LicensedApp();
app.run().catch(console.error);
