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

use core_phpunit\testcase;
use core_ai\feature;
use core_ai\interaction;
use core_ai\subsystem;

/**
 * @group ai_integrations
 */
class core_ai_subsystem_test extends testcase {

    public function test_get_ai_plugins(): void {
        $ai_plugins = subsystem::get_ai_plugins();
        foreach ($ai_plugins as $ai_plugin) {
            $this->assertEquals(subsystem::PLUGIN_TYPE, $ai_plugin->type);
            $this->assertStringContainsString("integrations" . DIRECTORY_SEPARATOR . "ai", $ai_plugin->rootdir);
        }
    }

    public function test_get_features(): void {
        /** @var feature[]|string $features*/
        $features = subsystem::get_features();

        // list the features
        foreach ($features as $feature) {
            $this->assertStringContainsString('feature\\', $feature);
        }
    }

    public function test_get_interactions(): void {
        /** @var interaction[]|string $interactions*/
        $interactions = subsystem::get_interactions();

        // list the names of interactions
        foreach ($interactions as $interaction) {
            $this->assertStringContainsString('ai\interaction', $interaction);
        }
    }

    public function test_disable_plugin(): void {
        $this->setAdminUser();

        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugins = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugins[0];
        $disabled = subsystem::disable_plugin($plugin);
        $this->assertTrue($disabled);

        // enable plugin without capabilities
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        subsystem::enable_plugin($plugin);
    }

    public function test_enable_plugin(): void {
        $this->setAdminUser();

        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugins = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugins[0];
        $enabled = subsystem::enable_plugin($plugin);
        $this->assertTrue($enabled);

        // Confirm plugin is set as default if only one plugin is available
        if (count($ai_plugins) < 2) {
            $default_plugin = subsystem::get_default_plugin();
            $this->assertNotNull($default_plugin);
            $this->assertEquals($plugin, $default_plugin->name);
        }

        // enable plugin without capabilities
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        subsystem::enable_plugin($plugin);
    }

    public function test_set_default_plugin(): void {
        $this->setAdminUser();

        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugins = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugins[0];
        $set_default_ai = subsystem::set_default_plugin($plugin);
        $this->assertTrue($set_default_ai);

        // setting active ai without capabilities
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        subsystem::set_default_plugin($plugin);
    }

    public function test_get_default_plugin(): void {
        $this->setAdminUser();

        $this->assertNull(subsystem::get_default_plugin());

        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugin_names = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugin_names[0];

        // set default plugin.
        subsystem::set_default_plugin($plugin);

        $default_plugin = subsystem::get_default_plugin();
        $this->assertNotNull($default_plugin);

        $this->assertInstanceOf(get_class($ai_plugins[$plugin]), $default_plugin);
    }

    public function test_is_ready(): void {
        global $CFG;
        $this->setAdminUser();
        $this->assertFalse(subsystem::is_ready());

        // enable ai
        $CFG->enable_ai = true;
        $this->assertFalse(subsystem::is_ready());

        // select default plugin
        $ai_plugins = subsystem::get_ai_plugins();
        $ai_plugin_names = array_keys($ai_plugins);

        if (empty($ai_plugins)) {
            $this->markTestSkipped('No ai plugins available to test with');
        }
        $plugin = $ai_plugin_names[0];

        // set default plugin.
        subsystem::set_default_plugin($plugin);
        $this->assertTrue(subsystem::is_ready());
    }
}
