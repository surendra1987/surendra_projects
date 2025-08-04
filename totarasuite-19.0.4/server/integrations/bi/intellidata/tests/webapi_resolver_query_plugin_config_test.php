<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */


use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\testing\setup_helper;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query validate credentials test case.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_query_plugin_config_test extends testcase {

    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_plugin_config';

    /**
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        SettingsHelper::set_setting('enabled', '1');
    }

    /**
     * @return void
     */
    public function test_query_plugin(): void {
        global $CFG, $DB;
        setup_helper::enable_exportfilesduringmigration();

        // Assign the role 'intelliboardapiuser' with the capability 'bi/intellidata:viewconfig' to a test user.
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        $req_capability = 'bi/intellidata:viewconfig';
        $system_context = context_system::instance();
        $role_capabilities = $DB->get_record('role_capabilities', ['capability' => $req_capability,
            'contextid' => $system_context->id, 'roleid' => $role->id], '*');
        if (!$role_capabilities) {
            assign_capability($req_capability, CAP_ALLOW, $role->id, $system_context);
        }
        $user = self::getDataGenerator()->create_user();
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);
        $result = $this->resolve_graphql_query(self::QUERY);

        self::assertArrayHasKey('moodleconfig', $result);

        $moodle_config = $result['moodleconfig'];
        self::assertEquals($CFG->totara_version, $moodle_config['totaraversion']);
        self::assertEquals($CFG->release, $moodle_config['release']);
        self::assertEquals($CFG->version, $moodle_config['version']);
        self::assertEquals($CFG->totara_version, $moodle_config['totaraversion']);

        self::assertArrayHasKey('pluginversion', $result);
        self::assertNotEmpty($result['pluginversion']);

        self::assertArrayHasKey('cronconfig', $result);
        $cron_configs = (array)$result['cronconfig'];
        foreach ($cron_configs as $cron_config) {
            $cron_config = (array)$cron_config;
            self::assertArrayHasKey('id', $cron_config);
            self::assertArrayHasKey('component', $cron_config);
            self::assertArrayHasKey('classname', $cron_config);
            self::assertArrayHasKey('lastruntime', $cron_config);
            self::assertArrayHasKey('nextruntime', $cron_config);
            self::assertArrayHasKey('blocking', $cron_config);
            self::assertArrayHasKey('minute', $cron_config);
            self::assertArrayHasKey('hour', $cron_config);
            self::assertArrayHasKey('day', $cron_config);
            self::assertArrayHasKey('month', $cron_config);
            self::assertArrayHasKey('dayofweek', $cron_config);
            self::assertArrayHasKey('faildelay', $cron_config);
            self::assertArrayHasKey('customised', $cron_config);
            self::assertArrayHasKey('disabled', $cron_config);
        }

        self::assertArrayHasKey('pluginconfig', $result);
        $plugin_config = $result['pluginconfig'];
        self::assertArrayHasKey('title', $plugin_config);
        self::assertArrayHasKey('items', $plugin_config);

        $settings = $plugin_config['items'];
        foreach ($settings as $setting) {
            self::assertArrayHasKey('type', $setting);
            self::assertArrayHasKey('subtype', $setting);
            self::assertArrayHasKey('title', $setting);
            self::assertArrayHasKey('name', $setting);
            self::assertArrayHasKey('value', $setting);
            self::assertFalse(in_array($setting['name'], SettingsHelper::SENSITIVE_SETTINGS));
        }
    }

    /**
     * @return void
     */
    public function test_plugin_config_without_capability(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::expectExceptionMessage('Sorry, but you do not currently have permissions to do that (IntelliData view configuration)');
        self::expectException(required_capability_exception::class);
        $this->resolve_graphql_query(self::QUERY);
    }

    /**
     * @return void
     */
    public function test_plugin_config_as_admin_user(): void {
        self::setAdminUser();
        setup_helper::enable_exportfilesduringmigration();

        $result = $this->resolve_graphql_query(self::QUERY);
        self::assertArrayHasKey('pluginversion', $result);
        self::assertArrayHasKey('cronconfig', $result);
        self::assertArrayHasKey('moodleconfig', $result);
        self::assertArrayHasKey('pluginconfig', $result);
    }

}