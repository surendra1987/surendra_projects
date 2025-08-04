<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use core\check\performance;
use core\check\result;
use core\check\security;
use core\check\status;
use core\orm\query\builder;

/**
 * Unit tests for check API
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_check_test extends \core_phpunit\testcase {

    public function test_environment_displayerrors() {
        global $OUTPUT;
        $check = new security\displayerrors();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertIsArray($result->export_for_template($OUTPUT));
        self::assertSame('core/check_result', $result->get_template_name());
        if (defined('WARN_DISPLAY_ERRORS_ENABLED')) {
            self::assertEquals(result::WARNING, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_environment_unsecuredataroot() {
        $check = new security\unsecuredataroot();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());

        $state = is_dataroot_insecure(true);
        switch ($state) {
            case INSECURE_DATAROOT_WARNING:
                self::assertEquals(result::ERROR, $result->get_status());
                break;
            case INSECURE_DATAROOT_ERROR:
                self::assertEquals(result::CRITICAL, $result->get_status());
                break;
            case false:
                self::assertEquals(result::OK, $result->get_status());
                break;
            default:
                self::fail('Unexpected data root security state');
                break;
        }
    }

    public function test_environment_publicpaths() {
        $check = new security\publicpaths();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        self::assertIsArray($check->get_pathsets());

        // Don't check result here, that is costly, and is checked when testing the table.
    }

    public function test_environment_configrw() {
        global $CFG;

        $check = new security\configrw();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (is_writable($CFG->srcroot . '/config.php')) {
            self::assertEquals(result::WARNING, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_environment_preventexecpath() {
        global $CFG;
        $check = new security\preventexecpath();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (empty($CFG->preventexecpath)) {
            self::assertEquals(result::WARNING, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_environment_nodemodules() {
        global $CFG;
        $check = new security\nodemodules();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (is_dir($CFG->dirroot.'/node_modules')) {
            self::assertEquals(result::WARNING, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_security_embed() {
        $check = new security\embed();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Embedding is not enabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_security_openprofiles() {
        global $CFG;
        $check = new security\openprofiles();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (empty($CFG->forcelogin) and empty($CFG->forceloginforprofiles)) {
            self::assertEquals(result::WARNING, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_security_crawlers() {
        $check = new security\crawlers();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Web crawlers are no permitted by default.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_passwordpolicy() {
        $check = new security\passwordpolicy();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Password policy is enabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_emailchangeconfirmation() {
        $check = new security\emailchangeconfirmation();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Email addresses my be confirmed upon change by default.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_webcron() {
        $check = new security\webcron();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Webcron is disabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_usernameenumeration() {
        $check = new security\usernameenumeration();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // This should always pass on a default site.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_persistentlogin() {
        $check = new security\persistentlogin();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Persistent login must be disabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_security_logincsrf() {
        $check = new security\logincsrf();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (!empty($CFG->allowlogincsrf)) {
            self::assertEquals(result::CRITICAL, $result->get_status());
        } else {
            self::assertEquals(result::OK, $result->get_status());
        }
    }

    public function test_security_externalentityloading() {
        $check = new security\externalentityloading();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // The environment should be secure by default.
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_security_disableconsistentcleaning() {
        global $CFG;
        $check = new security\disableconsistentcleaning();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Consistent cleaning is disabled by default.
        self::assertEquals(result::OK, $result->get_status());

        // Check the unhappy path
        $CFG->disableconsistentcleaning = true;
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());
    }

    public function test_security_devgraphql() {
        $check = new security\devgraphql();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Dev mode is disabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_http_https() {
        $check = new security\https();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        $outcomes = [
            // We can't be sure of the outcome, but it is expected to be one of these.
            result::CRITICAL,
            result::OK,
        ];
        self::assertContains($result->get_status(), $outcomes);
    }
    public function test_http_cookiehttponly() {
        $check = new security\cookiehttponly();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // This is on by default in Totara.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_http_cookiesecure() {
        $check = new security\cookiesecure();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        $outcomes = [
            // We can't be sure of the outcome, but it is expected to be one of these.
            result::ERROR,
            result::WARNING,
            result::OK,
        ];
        self::assertContains($result->get_status(), $outcomes);
    }
    public function test_access_riskadmin() {
        $check = new security\riskadmin();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // There is always one admin.
        self::assertEquals(result::INFO, $result->get_status());
    }
    public function test_access_riskbackup() {
        $check = new security\riskbackup();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // By default, Totara ships with roles that can back up user data.
        self::assertEquals(result::WARNING, $result->get_status());
        self::assertStringContainsString('Site Manager', $result->get_details());
    }
    public function test_access_defaultuserrole() {
        $check = new security\defaultuserrole();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // By default, this is always going to be fine. There are no changes to roles here!
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_security_guest() {
        global $CFG;
        $check = new security\guest();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());

        $CFG->guestloginbutton = false;
        self::assertEquals(result::OK, $result->get_status());

        // Check the unhappy path
        $CFG->guestloginbutton = true;
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());
    }

    public function test_access_guestrole() {
        $check = new security\guestrole();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // By default, this is always going to be fine. There are no changes to roles here!
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_access_frontpagerole() {
        $check = new security\frontpagerole();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // By default, this is always going to be fine. There are no changes to roles here!
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_access_noauth() {
        $check = new security\noauth();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Noauth is never installed. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_access_riskallowxss() {
        $check = new security\riskallowxss();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Allow XSS MUST be disabled by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_security_resourceallowxss() {
        $check = new security\resourceallowxss();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Allow XSS MUST be disabled by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_performance_designermode() {
        $check = new performance\designermode();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Theme designer mode is disabled by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_performance_cachejs() {
        $check = new performance\cachejs();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // JavaScript is cached by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_performance_debugging() {
        global $CFG;
        $check = new performance\debugging();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (!$CFG->debugdeveloper) {
            self::assertEquals(result::OK, $result->get_status());
        } else {
            self::assertEquals(result::WARNING, $result->get_status());
        }
    }
    public function test_performance_backups() {
        $check = new performance\backups();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Automated backups are off by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_performance_stats() {
        $check = new performance\stats();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Stats is off by default. If you're changing this someone has changed that fact.
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_environment_environment() {
        $check = new status\environment();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        $outcomes = [
            // We can't be sure of the outcome, but it is expected to be one of these.
            result::ERROR,
            result::OK,
        ];
        self::assertContains($result->get_status(), $outcomes);
    }
    public function test_environment_upgradecheck() {
        $check = new status\upgradecheck();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(result::OK, $result->get_status());
    }
    public function test_environment_antivirus() {
        global $CFG;
        $check = new status\antivirus();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        if (empty($CFG->antiviruses)) {
            self::assertEquals(result::NA, $result->get_status());
        }
    }

    public function test_access_riskxss() {
        $check = new security\riskxss();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Safe out of the box!
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_security_oauth2verify() {
        $check = new security\oauth2verify();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // Safe out of the box!
        self::assertEquals(result::OK, $result->get_status());
    }

    public function test_performance_dbschema() {
        $check = new performance\dbschema();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        // DO NOT check the result! It takes several minutes to generate!
    }

    public function test_security_allowinlineuploadedhtml() {
        global $CFG;
        $check = new security\allowinlineuploadedhtml();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNull($check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // HTML download is disabled by default.
        self::assertEquals(result::OK, $result->get_status());

        // Check the unhappy path
        $CFG->allow_inline_uploaded_html = 1;
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());
    }

    public function test_security_allowhttpcache(): void {
        global $CFG;
        $check = new security\allowhttpcache();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        self::assertNotNull($check->get_action_link());
        $CFG->allow_http_cache = 0;
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // HTTP caching is disabled by default.
        self::assertEquals(result::OK, $result->get_status());

        // Check the unhappy path
        $CFG->allow_http_cache = 1;
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());
    }

    public function test_security_wkhtmltopdf(): void {
        global $CFG;
        $check = new security\wkhtmltopdf();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());
        $CFG->pathtowkhtmltopdf = '';
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertNull($check->get_action_link());
        self::assertEquals(result::OK, $result->get_status());

        // Check the unhappy path
        $CFG->pathtowkhtmltopdf = 'set';
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        self::assertNotNull($check->get_action_link());
        self::assertEquals(result::ERROR, $result->get_status());
    }

    public function test_status_ephemeral_config_check_key() {
        $reflection = new \ReflectionClass(status\ephemeral_config::class);
        $method = $reflection->getMethod('check_key');

        self::assertNull($method->invokeArgs(null, ['key' => 'no_match']));
        self::assertEquals(42, $method->invokeArgs(null, ['key' => '00unit_test_match']));
        self::assertEquals(20, $method->invokeArgs(null, ['key' => 'revert_TL_42000_until_t20']));
    }

    public function test_security_csvexport(): void {
        $check = new security\csv();
        self::assertIsString($check->get_name());
        self::assertSame('core', $check->get_component());

        // Default setting should not contain the option of legacy csv export
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_setting_warning', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());

        // Get original export options setting
        $config = get_config('reportbuilder', 'exportoptions');

        // Set general setting to include
        set_config('exportoptions', 'csv_excel,excel', 'reportbuilder');
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_ok', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::OK, $result->get_status());

        // Create user
        $generator = $this->getDataGenerator();
        $user = $generator->create_user([
            'username' => 'test',
            'firstname' => 'first',
            'lastname' => 'last'
        ]);

        // Create the user report without overrider option enable
        $report = (object)[
            'fullname' => 'Users',
            'shortname' => 'user',
            'source' => 'user',
            'hidden' => 1,
            'embedded' => 1
        ];
        $report->id = builder::get_db()->insert_record('report_builder', $report);
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_ok', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::OK, $result->get_status());

        // Enable override setting and csv export in override setting
        $report->overrideexportoptions = 1;
        builder::get_db()->update_record('report_builder', $report);
        $report_settings = (object)[
            'reportid' => $report->id,
            'type' => 'exportoption',
            'name' => 'csv',
            'value' => 1
        ];
        $report_settings->id = builder::get_db()->insert_record('report_builder_settings', $report_settings);
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_override_setting_warning', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());

        // Disable the override setting in the report but no change on override setting
        $report->overrideexportoptions = 0;
        builder::get_db()->update_record('report_builder', $report);
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_ok', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::OK, $result->get_status());

        // Create a schedule report with enable csv export
        $schedule_report = (object)[
            'reportid' => $report->id,
            'userid' => $user->id,
            'format' => 'csv',
            'frequency' => 1,
            'schedule' => 0,
            'exporttofilesystem' => REPORT_BUILDER_EXPORT_EMAIL,
            'savedsearchid' => 0
        ];
        $schedule_report->id = builder::get_db()->insert_record('report_builder_schedule', $schedule_report);
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_schedule_warning', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::WARNING, $result->get_status());

        // Change the schedule report format to csv_excel
        $schedule_report->format = 'csv_excel';
        builder::get_db()->update_record('report_builder_schedule', $schedule_report);
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_csv_export_ok', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_csv_export_details', 'admin'), $result->get_details());
        self::assertEquals(result::OK, $result->get_status());
    }
}

