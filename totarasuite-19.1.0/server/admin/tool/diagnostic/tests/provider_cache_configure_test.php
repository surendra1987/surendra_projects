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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package tool_diagnostic
 */

use tool_diagnostic\provider\cache_information;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_cache_configure_test extends \core_phpunit\testcase {
    public function test_content(): void {
        global $CFG;
        require_once $CFG->dirroot.'/cache/locallib.php';

        $definitions = cache_administration_helper::get_definition_summaries();

        $application_count = 0;
        $request_count = 0;
        $store_count = 0;
        foreach ($definitions as $definition) {
            if (!empty($definition['mappings'])) {
                if (implode(', ', $definition['mappings']) === get_string('store_default_application', 'cache')) {
                    ++$application_count;
                    continue;
                }

                if (implode(', ', $definition['mappings']) === get_string('store_default_request', 'cache')) {
                    ++$request_count;
                    continue;
                }

                if (implode(', ', $definition['mappings']) === get_string('store_default_session', 'cache')) {
                    ++$store_count;
                }
            }
        }

        $provider = new cache_information(["enabled" => true]);
        $data = html_to_text($provider->get_content()->get_data());

        $this->assertStringContainsString(get_string('store_default_application', 'cache'), $data);
        $this->assertStringContainsString(get_string('store_default_request', 'cache'), $data);
        $this->assertStringContainsString(get_string('store_default_session', 'cache'), $data);
        $this->assertStringContainsString($application_count, $data);
        $this->assertStringContainsString($request_count, $data);
        $this->assertStringContainsString($store_count, $data);

    }
}
