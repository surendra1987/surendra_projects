<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @package tool_diagnostic
 */

use tool_diagnostic\provider\summary;
use totara_core\advanced_feature;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_summary_test extends \core_phpunit\testcase {

    public function test_content(): void {
        global $CFG, $DB;

        $provider = new summary(["enabled" => true]);
        $data = $provider->get_content()->get_data();

        require "$CFG->dirroot/version.php";

        $this->assertStringContainsString($version, $data);
        $this->assertStringContainsString(static::convert_maturity_to_string($maturity), $data);
        $this->assertStringContainsString($TOTARA->release, $data);

        $db_info = $DB->get_server_info();
        $this->assertMatchesRegularExpression('/DB family.*'.preg_quote($DB->get_dbfamily().'/'), $data);
        $this->assertMatchesRegularExpression('/DB version.*'.preg_quote($db_info['version'].'/'), $data);
        $this->assertMatchesRegularExpression('/DB description.*'.preg_quote($db_info['description'].'/'), $data);
        $this->assertMatchesRegularExpression('/PHP version.*'.preg_quote(phpversion().'/'), $data);
        $this->assertMatchesRegularExpression('/PHP extensions.*'.preg_quote(implode(', ', get_loaded_extensions()).'/'), $data);
        $os_details = php_uname('s') . " " . php_uname('r') . " " . php_uname('m');
        $this->assertMatchesRegularExpression('/OS.*'.preg_quote($os_details).'/', $data);
        $webserver_version = clean_param($_SERVER['SERVER_SOFTWARE'] ?? $_ENV['SERVER_SOFTWARE'] ?? 'na', PARAM_TEXT);
        $this->assertMatchesRegularExpression('/Webserver information.*'.preg_quote($webserver_version).'/', $data);

        $features = advanced_feature::get_available();
        foreach ($features as $feature) {
            $this->assertMatchesRegularExpression('/'.preg_quote($feature).'.*(Enabled|Disabled)/', $data);
        }

        $currentflavour = get_config('totara_flavour', 'currentflavour');
        $this->assertMatchesRegularExpression('/current flavour.*'.preg_quote($currentflavour).'/', $data);
        $this->assertMatchesRegularExpression('/user_count.*2/', $data);
        $this->assertMatchesRegularExpression('/active_user_count.*2/', $data);
        $this->assertMatchesRegularExpression('/multi-tenancy support.*Disabled/', $data);
        $this->assertMatchesRegularExpression('/tenant isolation.*Disabled/', $data);
        $this->assertMatchesRegularExpression('/tenant count.*0/', $data);
        $this->assertMatchesRegularExpression('/cron last run.*not run/', $data);
        $this->assertMatchesRegularExpression('/cron overdue.*1/', $data);
        $this->assertMatchesRegularExpression('/cron infrequent.*0/', $data);
        $this->assertMatchesRegularExpression('/cache warnings.*none/', $data);
    }

    public function test_content_with_some_values_adjusted(): void {
        $provider = new summary(["enabled" => true]);

        advanced_feature::disable('perform_goals');

        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/perform_goals.*Disabled/', $data);

        advanced_feature::enable('perform_goals');

        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/perform_goals.*Enabled/', $data);

        /** @var $tenant_generator \totara_tenant\testing\generator */
        $tenant_generator = \totara_tenant\testing\generator::instance();
        $tenant_generator->enable_tenants();

        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/multi-tenancy support.*Enabled/', $data);
        $this->assertMatchesRegularExpression('/tenant isolation.*Disabled/', $data);

        $tenant_generator->enable_tenant_isolation();
        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/multi-tenancy support.*Enabled/', $data);
        $this->assertMatchesRegularExpression('/tenant isolation.*Enabled/', $data);

        $tenant_generator->disable_tenants();
        $tenant_generator->disable_tenant_isolation();
        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/multi-tenancy support.*Disabled/', $data);
        $this->assertMatchesRegularExpression('/tenant isolation.*Disabled/', $data);

        $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->create_user(['suspended' => 1]);
        $this->getDataGenerator()->create_user(['deleted' => 1]);

        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/user_count.*6/', $data);
        $this->assertMatchesRegularExpression('/active_user_count.*4/', $data);

        $tenant_generator->enable_tenants();
        $tenant_generator->create_tenant();
        $tenant_generator->create_tenant();
        $tenant_generator->create_tenant();
        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/tenant count.*3/', $data);

        // Simulate a cron run
        $last_cron_run = time();
        set_config('lastcronstart', $last_cron_run, 'tool_task');
        $expected_frequency = $CFG->expectedcronfrequency ?? 200;
        $last_cron_interval = $expected_frequency / 2;
        set_config('lastcroninterval', $last_cron_interval, 'tool_task');

        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/cron last run.*'.$last_cron_run.'/', $data);
        $this->assertMatchesRegularExpression('/cron overdue.*0/', $data);
        $this->assertMatchesRegularExpression('/cron infrequent.*0/', $data);

        $last_cron_interval = $expected_frequency + 10;
        set_config('lastcroninterval', $last_cron_interval, 'tool_task');
        $data = $provider->get_content()->get_data();
        $this->assertMatchesRegularExpression('/cron last run.*'.$last_cron_run.'/', $data);
        $this->assertMatchesRegularExpression('/cron overdue.*0/', $data);
        $this->assertMatchesRegularExpression('/cron infrequent.*1/', $data);
    }

    /**
     * Maturity is defined using integer constants. For a human readable format we need to convert it.
     *
     * @param int $maturity
     * @return string
     */
    protected function convert_maturity_to_string(int $maturity): string {
        switch ($maturity) {
            case MATURITY_ALPHA:
                $maturity_string = 'alpha';
                break;
            case MATURITY_BETA:
                $maturity_string = 'beta';
                break;
            case MATURITY_RC:
                $maturity_string = 'rc';
                break;
            case MATURITY_EVERGREEN:
                $maturity_string = 'evergreen';
                break;
            case MATURITY_STABLE:
                $maturity_string = 'stable';
                break;
            case ANY_VERSION:
                $maturity_string = 'any';
                break;
            default:
                $maturity_string = 'none';
                break;
        }

        return $maturity_string;
    }
}
