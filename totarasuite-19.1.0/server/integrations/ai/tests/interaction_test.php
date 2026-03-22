<?php
/**
 * This file is part of Totara Core
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\subsystem;
use core_phpunit\testcase;
use core_tag\ai\interaction\suggest_tags;
use fixtures\sample_interaction;

require_once(__DIR__ . '/fixtures/sample_feature.php');
require_once(__DIR__ . '/fixtures/sample_connector_feature.php');
require_once(__DIR__ . '/fixtures/sample_interaction.php');

/**
 * @group ai_integrations
 */
class core_ai_interaction_test extends testcase {

    public function test_get_name_and_description(): void {
        $this->assertEquals('sample interaction', sample_interaction::get_name());
        $this->assertEquals('sample interaction description', sample_interaction::get_description());
    }

    public function test_get_ai_feature(): void {
        global $CFG;
        $CFG->enable_ai = true;
        $this->setAdminUser();

        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugins = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugins[0];
        subsystem::set_default_plugin($plugin);

        // get an interaction to test with.
        $interaction = new suggest_tags('core_core', 'item');

        $method = new ReflectionMethod($interaction, 'get_ai_feature');
        $method->setAccessible(true);
        $feature = $method->invoke($interaction);

        $this->assertEquals(generative_prompt_feature::class, get_parent_class($feature));
    }

    public function test_get_ai_feature_without_default_plugin_set() {
        global $CFG;
        $CFG->enable_ai = true;
        // get any interaction to test with.
        $interaction_classes = subsystem::get_interactions();

        if (empty($interaction_classes)) {
            $this->markTestSkipped('No defined interactions to work with');
        }
        $interaction_class = $interaction_classes[0];
        $interaction = $this->createMock($interaction_class);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage("AI feature has not been enabled or the default AI plugin has not been selected");

        $method = new ReflectionMethod($interaction, 'get_ai_feature');
        $method->setAccessible(true);
        $method->invoke($interaction, generative_prompt_feature::class);
    }
}
