<?php
/**
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

use core_mfa\admin\settings\manage_mfa_factors;
use core_mfa\factor;
use core_mfa\factor_factory;

/**
 * @coversDefaultClass \core_mfa\admin\settings\manage_mfa_factors
 * @group core_mfa
 */
class core_mfa_manage_mfa_factors_test extends \core_phpunit\testcase {
    /** @var \core\plugininfo\mfa[] */
    protected ?array $plugin_info;
    /** @var string[] */
    protected ?array $enabled;
    /** @var factor[] */
    protected ?array $mfa_instances;


    /** @inheritDoc */
    protected function setUp(): void {
        $plugins = ['toeprint' => 'Toe print', 'creditcard' => 'Credit card'];
        $this->plugin_info = [];
        foreach ($plugins as $name => $display) {
            $stub = $this->createStub(\core\plugininfo\mfa::class);
            $stub->name = $name;
            $stub->type = "mfa";
            $stub->method('__get')->willReturn('mfa_' . $name);
            $stub->displayname = $display;
            $this->plugin_info[$name] = $stub;
        }
        $this->enabled = ['creditcard'];
        $this->mfa_instances = [];
        foreach ($this->plugin_info as $plugin) {
            $stub = $this->createStub(factor::class);
            $stub->method('get_name')->willReturn($plugin->name);
            $stub->method('get_display_name')->willReturn($plugin->displayname);
            $stub->method('get_plugin_info')->willReturn($plugin);
            $this->mfa_instances[$plugin->name] = $stub;
        }
    }

    protected function tearDown(): void {
        $this->plugin_info = null;
        $this->enabled = null;
        $this->mfa_instances = null;

        parent::tearDown();
    }

    public function test_is_related() {
        $plugin_manager = $this->createMock(core_plugin_manager::class);
        $plugin_manager->expects($this->exactly(3))
            ->method('get_plugins_of_type')
            ->with($this->equalTo('mfa'))
            ->willReturn($this->plugin_info);

        $inst = new manage_mfa_factors($plugin_manager, $this->createMock(factor_factory::class));

        $this->assertTrue($inst->is_related('toeprint'));
        $this->assertTrue($inst->is_related('edit car'));

        $this->assertFalse($inst->is_related('jalapeno'));
    }

    public function test_output_html() {
        $plugin_manager = $this->createMock(core_plugin_manager::class);
        $plugin_manager->expects($this->once())
            ->method('get_plugins_of_type')
            ->with($this->equalTo('mfa'))
            ->willReturn($this->plugin_info);
        $plugin_manager->expects($this->once())
            ->method('get_enabled_plugins')
            ->with($this->equalTo('mfa'))
            ->willReturn($this->enabled);

        $factory = $this->createMock(factor_factory::class);
        $factory->expects($this->exactly(count($this->plugin_info)))
            ->method('get')
            ->willReturnCallback(function ($x) {
                return $this->mfa_instances[$x];
            });

        $inst = new manage_mfa_factors($plugin_manager, $factory);

        $html = $inst->output_html(null);

        $this->assertStringContainsString('Toe print', $html);
        $this->assertStringContainsString('Credit card', $html);
        $this->assertStringContainsString('enable', $html);
        $this->assertStringContainsString('disable', $html);
    }
}
