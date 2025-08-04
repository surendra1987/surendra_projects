<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\provider;

use cache_helper;
use tool_diagnostic\content\content;
use totara_core\advanced_feature;

class summary extends base {

    /** @var int */
    private $padding = 40;

    /** @var int */
    protected static $order = 10;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'summary';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->get_info());
        $content->set_data_type(content::TYPE_TEXT);

        return $content;
    }

    /**
     * @return string
     */
    private function get_info(): string {
        return $this->get_totara_version_info() . PHP_EOL
            . $this->get_features() . PHP_EOL
            . $this->get_environment_info() . PHP_EOL
            . $this->get_site_information() . PHP_EOL;
    }

    /**
     * @param string $heading
     * @return string
     */
    private function create_heading(string $heading): string {
        return str_repeat('=', 100) . PHP_EOL
            . str_pad("= {$heading}", 99) . '=' . PHP_EOL
            . str_repeat('=', 100) . PHP_EOL;
    }

    /**
     * @return string
     */
    private function get_totara_version_info(): string {
        global $CFG;

        $output = $this->create_heading('Totara version information');

        require "$CFG->dirroot/version.php";

        $output .= $this->generate_output_line('Release version', $version ?? 'na');
        $output .= $this->generate_output_line('Maturity', $this->convert_maturity_to_string($maturity ?? 'na'));
        $output .= $this->generate_output_line('Release version', $TOTARA->release ?? 'na');

        return $output;
    }

    /**
     * @return string
     */
    private function get_features(): string {
        $output = $this->create_heading('Totara features');
        foreach (advanced_feature::get_available() as $feature) {
            $value = advanced_feature::is_enabled($feature) ? 'Enabled' : 'Disabled';
            $output .= $this->generate_output_line($feature, $value);
        }

        return $output;
    }

    /**
     * @return string
     */
    private function get_environment_info(): string {
        global $DB;

        $output = $this->create_heading('Environment');

        $db_info = $DB->get_server_info();
        $output .= $this->generate_output_line('DB family', $DB->get_dbfamily());
        $output .= $this->generate_output_line('DB version', $db_info['version']);
        $output .= $this->generate_output_line('DB description', $db_info['description']);

        $output .= $this->generate_output_line('PHP version', phpversion());

        $extensions = implode(', ', get_loaded_extensions());
        $output .= $this->generate_output_line('PHP extensions', $extensions);

        $os_details = php_uname('s') . " " . php_uname('r') . " " . php_uname('m');
        $output .= $this->generate_output_line('OS', $os_details);

        $webserver_version = clean_param($_SERVER['SERVER_SOFTWARE'] ?? $_ENV['SERVER_SOFTWARE'] ?? 'na', PARAM_TEXT);
        $output .= $this->generate_output_line('Webserver information', $webserver_version);

        return $output;
    }

    /**
     * @return string
     */
    private function get_site_information(): string {
        global $DB, $CFG;

        require_once $CFG->dirroot.'/cache/classes/helper.php';

        $output = $this->create_heading('Site information');

        $last_cron_run = get_config('tool_task', 'lastcronstart');
        $is_cron_overdue = ($last_cron_run < time() - DAYSECS);
        $last_cron_interval = get_config('tool_task', 'lastcroninterval');
        $expected_frequency = $CFG->expectedcronfrequency ?? 200;
        $is_cron_infrequent = !$is_cron_overdue && ($last_cron_interval > $expected_frequency || $last_cron_run < time() - $expected_frequency);
        $cache_warnings = cache_helper::warnings();

        $values = [
            'current flavour' => get_config('totara_flavour', 'currentflavour'),
            'user_count' => $DB->count_records('user'),
            'active_user_count' => $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]),
            'multi-tenancy support' => !empty($CFG->tenantsenabled) ? 'Enabled' : 'Disabled',
            'tenant isolation' => !empty($CFG->tenantsisolated) ? 'Enabled' : 'Disabled',
            'tenant count' => $DB->count_records('tenant'),
            'cron last run' => !empty($last_cron_run) ? $last_cron_run : 'not run',
            'cron overdue' => (int) (get_config('tool_task', 'lastcronstart') < time() - DAYSECS),
            'cron infrequent' => (int) $is_cron_infrequent,
            'cache warnings' => (!empty($cache_warnings)) ? print_r($cache_warnings, true) : 'none',
        ];

        foreach ($values as $key => $value) {
            $output .= $this->generate_output_line($key, $value);
        }

        return $output;
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

    /**
     * Generates a line to add to the output, properly formatted
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    private function generate_output_line(string $name, string $value): string {
        return str_pad($name, $this->padding) . $value . PHP_EOL;
    }

}
