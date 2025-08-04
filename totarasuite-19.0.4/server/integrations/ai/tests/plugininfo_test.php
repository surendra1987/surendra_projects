<?php
/*
 * This file is part of Totara Talent Experience Platform
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

use core\plugininfo\ai as ai_plugininfo;
use core_ai\feature;
use core_ai\subsystem;
use core_phpunit\testcase;

/**
 * @group ai_integrations
 */
class core_ai_plugininfo_test extends testcase {

    public function test_get_manage_url(): void {
        $this->assertEquals(
            (new \moodle_url('/integrations/ai/index.php'))->out(),
            ai_plugininfo::get_manage_url()->out()
        );
    }

    public function test_get_supported_features(): void {
        $ai_plugins = subsystem::get_ai_plugins();

        if (empty($ai_plugins)) {
            $this->markTestSkipped("No AI plugins to test with");
        }
        $ai_plugin_names = array_keys($ai_plugins);
        $ai_plugin = $ai_plugins[$ai_plugin_names[0]];

        $supported_features = $ai_plugin->get_supported_features();
        // test the ai plugin supports at least one feature.
        $this->assertNotEmpty($supported_features);
    }

    public function test_get_feature_for_interaction(): void {
        $ai_plugins = subsystem::get_ai_plugins();

        if (empty($ai_plugins)) {
            $this->markTestSkipped("No AI plugins to test with");
        }
        $ai_plugin_names = array_keys($ai_plugins);
        $ai_plugin = $ai_plugins[$ai_plugin_names[0]];

        $features = subsystem::get_features();

        if (empty($features)) {
            $this->markTestSkipped("No features to test with");
        }
        $feature_class = $features[0];

        // test getting a feature from the ai plugin.
        $feature = $ai_plugin->get_feature_for_interaction($feature_class, 'test_interaction');

        // Assert the feature returned is the one requested for
        $this->assertInstanceOf(feature::class, $feature);
        $this->assertEquals($feature_class, get_parent_class($feature));

        // confirm the feature class is from the feature namespace
        $this->assertStringContainsString($ai_plugin->component, get_class($feature));
    }
}
