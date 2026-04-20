<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Generates biometric device setup documentation as a Word document.
 */
class GenerateBiometricDocs extends Command
{
    protected $signature = 'docs:generate-biometric';
    protected $description = 'Generate biometric device setup documentation (.docx)';

    public function handle(): int
    {
        $phpWord = new PhpWord();

        // Define styles
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 24, 'color' => '1e40af'], ['spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 18, 'color' => '374151'], ['spaceAfter' => 120, 'spaceBefore' => 240]);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 14, 'color' => '4b5563'], ['spaceAfter' => 80, 'spaceBefore' => 160]);

        $phpWord->addParagraphStyle('normal', ['spaceAfter' => 120]);
        $phpWord->addParagraphStyle('listItem', ['spaceAfter' => 60]);
        $phpWord->addParagraphStyle('note', ['spaceAfter' => 120, 'indent' => 0.5]);

        $phpWord->addFontStyle('bold', ['bold' => true]);
        $phpWord->addFontStyle('code', ['name' => 'Courier New', 'size' => 10, 'bgColor' => 'f3f4f6']);
        $phpWord->addFontStyle('important', ['bold' => true, 'color' => 'dc2626']);

        // =====================================================================
        // TITLE PAGE
        // =====================================================================
        $section = $phpWord->addSection();

        $section->addText('Biometric Device Integration Guide', ['bold' => true, 'size' => 28, 'color' => '1e40af'], ['alignment' => Jc::CENTER, 'spaceBefore' => 2000]);
        $section->addText('Cloud-Based Staff Attendance System', ['size' => 16, 'color' => '6b7280'], ['alignment' => Jc::CENTER, 'spaceAfter' => 500]);
        $section->addText('Complete Setup Instructions for ZKTeco and Hikvision Devices', ['size' => 12, 'italic' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 1000]);

        $section->addText('Document Version: 1.0', ['size' => 10], ['alignment' => Jc::CENTER]);
        $section->addText('Generated: ' . now()->format('F j, Y'), ['size' => 10], ['alignment' => Jc::CENTER]);

        // =====================================================================
        // TABLE OF CONTENTS
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('Table of Contents', 1);
        $section->addTOC(['size' => 11]);

        // =====================================================================
        // SECTION 1: INTRODUCTION
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('1. Introduction', 1);

        $section->addText('This document provides comprehensive instructions for integrating biometric attendance devices with the cloud-based Staff Attendance System. It covers device configuration, network setup, and system integration for both ZKTeco and Hikvision devices.', 'normal');

        $section->addTitle('1.1 Supported Devices', 2);
        $section->addListItem('ZKTeco fingerprint/face recognition terminals (K-series, F-series, etc.)', 0);
        $section->addListItem('Hikvision access control terminals with ISAPI support', 0);

        $section->addTitle('1.2 Connectivity Modes', 2);
        $section->addText('The system supports three connectivity modes to accommodate different network environments:', 'normal');

        $section->addText('Pull Mode', 'bold');
        $section->addText('The cloud server connects directly to the device to fetch attendance events. Requires network access from the server to the device (VPN or port forwarding).', 'normal');

        $section->addText('Push Mode (Recommended for Hikvision)', 'bold');
        $section->addText('The device pushes events to a webhook URL on the cloud server. Real-time updates, no port forwarding needed. Device must support HTTP webhooks.', 'normal');

        $section->addText('Agent Mode (Recommended for ZKTeco)', 'bold');
        $section->addText('A local sync agent runs at the school, polls devices on the local network, and pushes events to the cloud API. Most flexible option for cloud deployments.', 'normal');

        // =====================================================================
        // SECTION 2: NETWORK REQUIREMENTS
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('2. Network Requirements', 1);

        $section->addTitle('2.1 Pull Mode Requirements', 2);
        $section->addListItem('Device must have a static IP address on the local network', 0);
        $section->addListItem('Server must be able to reach the device IP (same network, VPN, or port forwarding)', 0);
        $section->addListItem('ZKTeco: Port 4370 (UDP) must be open', 0);
        $section->addListItem('Hikvision: Port 80 or configured HTTP port (TCP) must be open', 0);

        $section->addTitle('2.2 Push Mode Requirements', 2);
        $section->addListItem('Device must be able to reach the cloud server URL via HTTPS', 0);
        $section->addListItem('Outbound internet access from the device network', 0);
        $section->addListItem('SSL certificate on the cloud server (HTTPS required)', 0);

        $section->addTitle('2.3 Agent Mode Requirements', 2);
        $section->addListItem('Computer or Raspberry Pi on the school network', 0);
        $section->addListItem('PHP 8.0+ or Python 3.8+ installed on the agent machine', 0);
        $section->addListItem('Outbound HTTPS access to the cloud server', 0);
        $section->addListItem('Network access from agent to all biometric devices', 0);

        // =====================================================================
        // SECTION 3: ZKTECO DEVICE SETUP
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('3. ZKTeco Device Setup', 1);

        $section->addTitle('3.1 Device Physical Setup', 2);
        $section->addListItem('Mount the device at an appropriate height (face level for face recognition)', 0);
        $section->addListItem('Connect power adapter to the device', 0);
        $section->addListItem('Connect Ethernet cable to the device and network switch', 0);
        $section->addListItem('Power on the device and wait for initialization', 0);

        $section->addTitle('3.2 Device Network Configuration', 2);
        $section->addText('Access the device menu by pressing MENU or using the touch screen.', 'normal');

        $section->addListItem('Navigate to: COMM. > Ethernet', 0);
        $section->addListItem('Set DHCP to OFF (disable automatic IP)', 0);
        $section->addListItem('Enter a static IP address (e.g., 192.168.1.201)', 0);
        $section->addListItem('Enter subnet mask (typically 255.255.255.0)', 0);
        $section->addListItem('Enter gateway (your router IP, e.g., 192.168.1.1)', 0);
        $section->addListItem('Save settings and restart the device', 0);

        $section->addText('Note the following information:', ['bold' => true, 'color' => '1e40af']);
        $section->addListItem('IP Address: ___________________', 0);
        $section->addListItem('Port: 4370 (default)', 0);
        $section->addListItem('Device Serial Number: ___________________', 0);

        $section->addTitle('3.3 Enroll Employees on Device', 2);
        $section->addText('Each staff member must be enrolled on the device with a unique employee number that matches their ID number in the school system.', 'normal');

        $section->addListItem('On the device, go to: User Mgt > New User', 0);
        $section->addListItem('Enter User ID: Use the staff member\'s ID number from the school system', 0);
        $section->addListItem('Enter Name: Staff member\'s name', 0);
        $section->addListItem('Enroll fingerprint or face as required', 0);
        $section->addListItem('Save the user', 0);
        $section->addListItem('Repeat for all staff members', 0);

        $section->addText('IMPORTANT: The User ID on the device MUST match the staff\'s ID Number in the school system for automatic mapping to work.', ['bold' => true, 'color' => 'dc2626']);

        $section->addTitle('3.4 Configure in School System (Agent Mode - Recommended)', 2);
        $section->addText('Since ZKTeco devices cannot push events directly to a cloud server, Agent mode is recommended.', 'normal');

        $section->addListItem('Log in to the school system as an administrator', 0);
        $section->addListItem('Navigate to: Staff Attendance > Settings > Devices', 0);
        $section->addListItem('Click "Add Device"', 0);
        $section->addListItem('Enter the following:', 0);

        $section->addText('Device Name: (e.g., "Main Entrance ZKTeco")', null, ['indent' => 0.5]);
        $section->addText('Device Type: ZKTeco', null, ['indent' => 0.5]);
        $section->addText('Connectivity Mode: Agent', null, ['indent' => 0.5]);
        $section->addText('IP Address: The device\'s static IP (for agent reference)', null, ['indent' => 0.5]);
        $section->addText('Port: 4370', null, ['indent' => 0.5]);
        $section->addText('Username: (for agent to connect - check device settings)', null, ['indent' => 0.5]);
        $section->addText('Password: (device password if set)', null, ['indent' => 0.5]);
        $section->addText('Timezone: Africa/Gaborone', null, ['indent' => 0.5]);

        $section->addListItem('Save the device', 0);
        $section->addListItem('Note the Agent API URL displayed after saving', 0);

        $section->addTitle('3.5 Setting Up the Sync Agent', 2);
        $section->addText('The sync agent is a small program that runs on a computer at the school.', 'normal');

        $section->addText('Option A: Using the Provided Agent Script', 'bold');
        $section->addListItem('Download the sync agent from the system downloads page', 0);
        $section->addListItem('Extract to a folder on the local computer', 0);
        $section->addListItem('Edit config.json with your settings:', 0);

        $section->addText('{', 'code', ['indent' => 0.5]);
        $section->addText('  "api_url": "https://your-school.com/api/attendance/webhook/agent/1",', 'code', ['indent' => 0.5]);
        $section->addText('  "api_token": "your-sanctum-api-token",', 'code', ['indent' => 0.5]);
        $section->addText('  "devices": [', 'code', ['indent' => 0.5]);
        $section->addText('    {"ip": "192.168.1.201", "port": 4370, "type": "zkteco"}', 'code', ['indent' => 0.5]);
        $section->addText('  ],', 'code', ['indent' => 0.5]);
        $section->addText('  "sync_interval_minutes": 5', 'code', ['indent' => 0.5]);
        $section->addText('}', 'code', ['indent' => 0.5]);

        $section->addListItem('Run the agent: php agent.php or double-click start.bat', 0);
        $section->addListItem('Configure to run on Windows startup (optional)', 0);

        $section->addText('Option B: Using Windows Task Scheduler', 'bold');
        $section->addListItem('Open Task Scheduler', 0);
        $section->addListItem('Create a new task that runs the agent script every 5 minutes', 0);
        $section->addListItem('Set to run whether user is logged in or not', 0);

        // =====================================================================
        // SECTION 4: HIKVISION DEVICE SETUP
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('4. Hikvision Device Setup', 1);

        $section->addTitle('4.1 Device Physical Setup', 2);
        $section->addListItem('Mount the device at an appropriate location', 0);
        $section->addListItem('Connect power (PoE or DC adapter)', 0);
        $section->addListItem('Connect Ethernet cable to the network', 0);
        $section->addListItem('Wait for the device to fully boot (LED indicators stable)', 0);

        $section->addTitle('4.2 Access Device Web Interface', 2);
        $section->addListItem('Connect a computer to the same network as the device', 0);
        $section->addListItem('Use Hikvision SADP Tool to discover the device IP', 0);
        $section->addListItem('Open a web browser and go to http://[device-ip]', 0);
        $section->addListItem('Log in with default credentials (admin / admin or as configured)', 0);
        $section->addListItem('Set a strong password if prompted', 0);

        $section->addTitle('4.3 Configure Network Settings', 2);
        $section->addText('In the web interface:', 'normal');
        $section->addListItem('Go to: Configuration > Network > Basic Settings', 0);
        $section->addListItem('Set to Manual (static IP)', 0);
        $section->addListItem('Enter IP Address (e.g., 192.168.1.202)', 0);
        $section->addListItem('Enter Subnet Mask (255.255.255.0)', 0);
        $section->addListItem('Enter Gateway (router IP)', 0);
        $section->addListItem('Enter DNS Server (8.8.8.8 or your DNS)', 0);
        $section->addListItem('Click Save', 0);

        $section->addText('Record the following:', ['bold' => true, 'color' => '1e40af']);
        $section->addListItem('IP Address: ___________________', 0);
        $section->addListItem('HTTP Port: ___________ (default 80)', 0);
        $section->addListItem('Admin Username: ___________________', 0);
        $section->addListItem('Admin Password: ___________________', 0);

        $section->addTitle('4.4 Enable ISAPI Protocol', 2);
        $section->addListItem('Go to: Configuration > Network > Advanced Settings > Integration Protocol', 0);
        $section->addListItem('Enable ISAPI', 0);
        $section->addListItem('Enable HTTP Listening (if available)', 0);
        $section->addListItem('Click Save', 0);

        $section->addTitle('4.5 Enroll Employees', 2);
        $section->addText('In the web interface:', 'normal');
        $section->addListItem('Go to: Person Management', 0);
        $section->addListItem('Click "Add" to add a new person', 0);
        $section->addListItem('Enter Employee No.: MUST match staff ID number in school system', 0);
        $section->addListItem('Enter Name: Staff member\'s name', 0);
        $section->addListItem('Add fingerprint or face credentials', 0);
        $section->addListItem('Set access permissions as needed', 0);
        $section->addListItem('Click OK to save', 0);

        $section->addTitle('4.6 Configure Push Mode (Recommended for Cloud)', 2);
        $section->addText('Push mode allows the device to send events directly to the cloud server in real-time.', 'normal');

        $section->addText('Step 1: Add Device in School System', 'bold');
        $section->addListItem('Navigate to: Staff Attendance > Settings > Devices', 0);
        $section->addListItem('Click "Add Device"', 0);
        $section->addListItem('Enter:', 0);

        $section->addText('Device Name: (e.g., "Reception Hikvision")', null, ['indent' => 0.5]);
        $section->addText('Device Type: Hikvision', null, ['indent' => 0.5]);
        $section->addText('Connectivity Mode: Push', null, ['indent' => 0.5]);
        $section->addText('IP Address: (optional - for reference)', null, ['indent' => 0.5]);
        $section->addText('Timezone: Africa/Gaborone', null, ['indent' => 0.5]);

        $section->addListItem('Save the device', 0);
        $section->addListItem('COPY the Webhook URL displayed (e.g., https://your-school.com/api/attendance/webhook/hikvision/2)', 0);
        $section->addListItem('Note the Webhook Secret if displayed', 0);

        $section->addText('Step 2: Configure Device to Push Events', 'bold');
        $section->addText('In the Hikvision web interface:', 'normal');
        $section->addListItem('Go to: Configuration > Network > Advanced Settings > HTTP Listening', 0);
        $section->addListItem('Enable HTTP Listening', 0);
        $section->addListItem('Enter the webhook URL from the school system', 0);
        $section->addListItem('Set Protocol to HTTPS', 0);
        $section->addListItem('Click Save and Test', 0);

        $section->addText('Alternative - ISUP Configuration:', 'bold');
        $section->addListItem('Go to: Configuration > Network > Advanced Settings > Platform Access', 0);
        $section->addListItem('Enable Platform Access', 0);
        $section->addListItem('Enter Server Address: your-school.com', 0);
        $section->addListItem('Enter Server Port: 443', 0);
        $section->addListItem('Select Protocol: HTTPS', 0);
        $section->addListItem('Save settings', 0);

        $section->addTitle('4.7 Configure Pull Mode (Alternative)', 2);
        $section->addText('Use pull mode if push mode is not available or for on-premise deployments.', 'normal');

        $section->addListItem('In School System: Staff Attendance > Settings > Devices', 0);
        $section->addListItem('Add Device with Connectivity Mode: Pull', 0);
        $section->addListItem('Enter full connection details:', 0);

        $section->addText('IP Address: Device static IP (required)', null, ['indent' => 0.5]);
        $section->addText('Port: 80 (or configured HTTP port)', null, ['indent' => 0.5]);
        $section->addText('Username: admin (device admin username)', null, ['indent' => 0.5]);
        $section->addText('Password: Device admin password', null, ['indent' => 0.5]);

        $section->addListItem('Click "Test Connection" to verify', 0);
        $section->addListItem('Save the device', 0);

        // =====================================================================
        // SECTION 5: SYSTEM CONFIGURATION
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('5. System Configuration', 1);

        $section->addTitle('5.1 Scheduled Synchronization', 2);
        $section->addText('The system runs scheduled tasks to process attendance data:', 'normal');

        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'cccccc', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(3000)->addText('Task', 'bold');
        $table->addCell(2500)->addText('Schedule', 'bold');
        $table->addCell(4000)->addText('Description', 'bold');

        $table->addRow();
        $table->addCell(3000)->addText('attendance:sync-biometric');
        $table->addCell(2500)->addText('Every 10 minutes');
        $table->addCell(4000)->addText('Pulls events from pull-mode devices');

        $table->addRow();
        $table->addCell(3000)->addText('attendance:process-events');
        $table->addCell(2500)->addText('Every 15 minutes');
        $table->addCell(4000)->addText('Converts raw events to attendance records');

        $table->addRow();
        $table->addCell(3000)->addText('attendance:create-daily-records');
        $table->addCell(2500)->addText('Daily 6 PM');
        $table->addCell(4000)->addText('Marks absent staff who never clocked in');

        $section->addTitle('5.2 Employee ID Mapping', 2);
        $section->addText('The system attempts to automatically link device employee numbers to staff records:', 'normal');

        $section->addListItem('Exact Match: Employee number matches staff ID number exactly', 0);
        $section->addListItem('Normalized Match: Tries trimming zeros and whitespace', 0);
        $section->addListItem('Manual Mapping: Admin can manually link unmapped IDs', 0);

        $section->addText('To manage mappings:', 'normal');
        $section->addListItem('Go to: Staff Attendance > Settings > ID Mappings', 0);
        $section->addListItem('View unmapped IDs that need manual linking', 0);
        $section->addListItem('Click on an unmapped ID to assign it to a staff member', 0);

        $section->addTitle('5.3 Attendance Codes', 2);
        $section->addText('Configure attendance codes to classify attendance types:', 'normal');
        $section->addListItem('Go to: Staff Attendance > Settings > Attendance Codes', 0);
        $section->addListItem('Default codes include: Present, Absent, Late, Leave, etc.', 0);
        $section->addListItem('Customize codes as needed for your school', 0);

        // =====================================================================
        // SECTION 6: TESTING & VERIFICATION
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('6. Testing & Verification', 1);

        $section->addTitle('6.1 Test Device Connection', 2);
        $section->addListItem('Go to: Staff Attendance > Settings > Devices', 0);
        $section->addListItem('Find the device in the list', 0);
        $section->addListItem('Click the "Test Connection" button (wifi icon)', 0);
        $section->addListItem('Verify "Connection successful" message appears', 0);

        $section->addText('If connection fails, check:', 'normal');
        $section->addListItem('Device is powered on and network connected', 0);
        $section->addListItem('IP address is correct', 0);
        $section->addListItem('Port is not blocked by firewall', 0);
        $section->addListItem('Username and password are correct (Hikvision)', 0);

        $section->addTitle('6.2 Test Attendance Capture', 2);
        $section->addListItem('Have a test staff member clock in on the device', 0);
        $section->addListItem('Wait for sync cycle (or trigger manual sync)', 0);
        $section->addListItem('Check raw events: Run php artisan tinker, then BiometricRawEvent::latest()->first()', 0);
        $section->addListItem('Verify the event appears with correct employee number', 0);

        $section->addTitle('6.3 Manual Sync', 2);
        $section->addText('To manually trigger a sync (pull mode devices):', 'normal');
        $section->addText('php artisan attendance:sync-biometric', 'code');

        $section->addText('To sync a specific device:', 'normal');
        $section->addText('php artisan attendance:sync-biometric --device=1', 'code');

        $section->addTitle('6.4 Process Events', 2);
        $section->addText('To manually process raw events into attendance records:', 'normal');
        $section->addText('php artisan attendance:process-events', 'code');

        $section->addTitle('6.5 View Attendance', 2);
        $section->addListItem('Go to: Staff Attendance > Manual Register', 0);
        $section->addListItem('Select the date', 0);
        $section->addListItem('Verify attendance records appear for clocked staff', 0);
        $section->addListItem('Source should show as "Biometric"', 0);

        // =====================================================================
        // SECTION 7: TROUBLESHOOTING
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('7. Troubleshooting', 1);

        $section->addTitle('7.1 Connection Issues', 2);

        $section->addText('Problem: "Connection failed" when testing device', 'bold');
        $section->addListItem('Verify device IP address is correct', 0);
        $section->addListItem('Ping the device: ping 192.168.x.x', 0);
        $section->addListItem('Check firewall rules on both device and network', 0);
        $section->addListItem('For ZKTeco: Ensure UDP port 4370 is open', 0);
        $section->addListItem('For Hikvision: Ensure HTTP port (80) is accessible', 0);

        $section->addText('Problem: Webhook not receiving events (Push mode)', 'bold');
        $section->addListItem('Verify device can reach the internet', 0);
        $section->addListItem('Check SSL certificate is valid on cloud server', 0);
        $section->addListItem('Verify webhook URL is correctly configured on device', 0);
        $section->addListItem('Check device logs for HTTP errors', 0);

        $section->addTitle('7.2 Attendance Not Recording', 2);

        $section->addText('Problem: Clock-ins not appearing in system', 'bold');
        $section->addListItem('Check raw events table for incoming events', 0);
        $section->addListItem('Verify employee number matches staff ID number', 0);
        $section->addListItem('Check ID Mappings for unmapped employee numbers', 0);
        $section->addListItem('Run process-events command manually', 0);

        $section->addText('Problem: Staff showing as "Absent" despite clocking in', 'bold');
        $section->addListItem('Check device time is correctly synchronized', 0);
        $section->addListItem('Verify timezone settings match', 0);
        $section->addListItem('Check that processing job ran after the clock-in', 0);

        $section->addTitle('7.3 Duplicate Records', 2);
        $section->addText('The system prevents duplicates using a composite key of device_id + employee_number + timestamp. If duplicates appear:', 'normal');
        $section->addListItem('Check if multiple devices have the same employee enrolled', 0);
        $section->addListItem('Verify device times are synchronized', 0);

        $section->addTitle('7.4 Getting Help', 2);
        $section->addText('For additional support:', 'normal');
        $section->addListItem('Check system logs: storage/logs/laravel.log', 0);
        $section->addListItem('Review sync history: Staff Attendance > Sync History', 0);
        $section->addListItem('Contact system administrator with error messages', 0);

        // =====================================================================
        // APPENDIX
        // =====================================================================
        $section->addPageBreak();
        $section->addTitle('Appendix A: Quick Reference', 1);

        $section->addTitle('Device Ports', 2);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'cccccc', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(3000)->addText('Device Type', 'bold');
        $table->addCell(2000)->addText('Protocol', 'bold');
        $table->addCell(2000)->addText('Default Port', 'bold');
        $table->addRow();
        $table->addCell(3000)->addText('ZKTeco');
        $table->addCell(2000)->addText('UDP');
        $table->addCell(2000)->addText('4370');
        $table->addRow();
        $table->addCell(3000)->addText('Hikvision');
        $table->addCell(2000)->addText('HTTP/HTTPS');
        $table->addCell(2000)->addText('80 / 443');

        $section->addTitle('API Endpoints', 2);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'cccccc', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(2000)->addText('Mode', 'bold');
        $table->addCell(6000)->addText('Endpoint', 'bold');
        $table->addRow();
        $table->addCell(2000)->addText('Hikvision Push');
        $table->addCell(6000)->addText('/api/attendance/webhook/hikvision/{device_id}');
        $table->addRow();
        $table->addCell(2000)->addText('Agent');
        $table->addCell(6000)->addText('/api/attendance/webhook/agent/{device_id}');

        $section->addTitle('Artisan Commands', 2);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'cccccc', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(4500)->addText('Command', 'bold');
        $table->addCell(4500)->addText('Description', 'bold');
        $table->addRow();
        $table->addCell(4500)->addText('attendance:sync-biometric');
        $table->addCell(4500)->addText('Pull events from devices');
        $table->addRow();
        $table->addCell(4500)->addText('attendance:sync-biometric --device=1');
        $table->addCell(4500)->addText('Sync specific device');
        $table->addRow();
        $table->addCell(4500)->addText('attendance:process-events');
        $table->addCell(4500)->addText('Process raw events');

        // =====================================================================
        // Save Document
        // =====================================================================
        $outputPath = base_path('docs/Biometric_Device_Integration_Guide.docx');
        $phpWord->save($outputPath, 'Word2007');

        $this->info("Document generated successfully: {$outputPath}");

        return Command::SUCCESS;
    }
}
