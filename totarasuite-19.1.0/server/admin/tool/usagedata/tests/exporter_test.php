<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\exporter;
use tool_usagedata\local\datacollection;

class tool_usagedata_exporter_test extends testcase {

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_generate_standard_plugins(): void {
        $exporter = new exporter();
        $mock_plugin_manager = $this->createMock(core_plugin_manager::class);
        $installed_plugins = [];

        $tool_plugin_info = new core\plugininfo\tool();
        $tool_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;
        $tool_plugin_info->type = 'tool';
        $tool_plugin_info->name = 'usagedata';

        // this cohort plugin should be filtered out because the source is not standard
        $coht_plugin_info = new core\plugininfo\totara();
        $coht_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_EXTENSION;
        $coht_plugin_info->type = 'totara';
        $coht_plugin_info->name = 'cohort';

        $installed_plugins[] = [$tool_plugin_info, $coht_plugin_info];
        $mock_plugin_manager->method('get_plugins')->willReturn($installed_plugins);
        $standard_plugins = $this->callInternalMethod($exporter, 'generate_standard_plugins', [$mock_plugin_manager]);
        $this->assertEqualsCanonicalizing(['tool_usagedata' => 1], $standard_plugins);
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_get_export_class_namespaces(): void {
        $exporter = new exporter();
        $mock_plugin_manager = $this->createMock(core_plugin_manager::class);
        $installed_plugins = [];

        // add one for totara_certification
        $cert_plugin_info = new core\plugininfo\totara();
        $cert_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;
        $cert_plugin_info->type = 'totara';
        $cert_plugin_info->name = 'certification';

        $coht_plugin_info = new core\plugininfo\totara();
        $coht_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_EXTENSION;
        $coht_plugin_info->type = 'totara';
        $coht_plugin_info->name = 'cohort';

        $installed_plugins[] = [$cert_plugin_info, $coht_plugin_info];
        $mock_plugin_manager->method('get_plugins')->willReturn($installed_plugins);

        /** @var string[] $usage_data_class_namespaces */
        $usage_data_class_namespaces = $this->callInternalMethod($exporter, 'get_export_class_namespaces', [$mock_plugin_manager]);

        $usage_data_class_namespaces = array_filter($usage_data_class_namespaces, function ($usage_data_class_namespace) {
            // Filter out core plugins to simplify testing
            if (str_contains($usage_data_class_namespace, 'core')) {
                return false;
            }

            return true;
        });

        // only totara_certification is a standard plugin, cohort was mocked as an extension
        $this->assertContains('totara_certification\usagedata\assignment', $usage_data_class_namespaces);
        $this->assertContains('totara_certification\usagedata\certification', $usage_data_class_namespaces);
        $this->assertContains('totara_certification\usagedata\courseset', $usage_data_class_namespaces);
        $this->assertContains('totara_certification\usagedata\recertification', $usage_data_class_namespaces);

        $this->assertNotContains('totara_cohort\usagedata\count_of_rules', $usage_data_class_namespaces);

        // Now, let's run the test again but this time both plugins (totara_certification and cohort) are standard
        $coht_plugin_info = new core\plugininfo\totara();
        $coht_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;
        $coht_plugin_info->type = 'totara';
        $coht_plugin_info->name = 'cohort';

        $installed_plugins[] = [$cert_plugin_info, $coht_plugin_info];
        $mock_plugin_manager = $this->createMock(core_plugin_manager::class);
        $mock_plugin_manager->method('get_plugins')->willReturn($installed_plugins);

        /** @var string[] $usage_data_class_namespaces */
        $usage_data_class_namespaces = $this->callInternalMethod($exporter, 'get_export_class_namespaces', [$mock_plugin_manager]);

        $usage_data_class_namespaces = array_filter($usage_data_class_namespaces, function ($usage_data_class_namespace) {
            // Filter out core plugins to simplify testing
            if (str_contains($usage_data_class_namespace, 'core')) {
                return false;
            }

            return true;
        });

        $this->assertContains('totara_cohort\usagedata\count_of_rules', $usage_data_class_namespaces);
        $this->assertContains('totara_certification\usagedata\assignment', $usage_data_class_namespaces);
    }

    public function test_get_export_classes(): void {
        $exporter = new exporter();

        // add one for totara_certification
        $cert_plugin_info = new core\plugininfo\totara();
        $cert_plugin_info->source = core_plugin_manager::PLUGIN_SOURCE_STANDARD;
        $cert_plugin_info->type = 'totara';
        $cert_plugin_info->name = 'certification';

        $mock_plugin_manager = $this->createMock(core_plugin_manager::class);
        $installed_plugins[] = [$cert_plugin_info];
        $mock_plugin_manager->method('get_plugins')->willReturn($installed_plugins);

        $usage_data_classes = $this->callInternalMethod($exporter, 'get_export_classes', []);

        foreach ($usage_data_classes as $usage_data_class) {
            $this->assertInstanceOf(\tool_usagedata\local\data::class, $usage_data_class);
        }
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_initialise_data(): void {
        // Given we have a new exporter instance
        $exporter = new exporter();
        // And we check the data property before initialising
        $exporter_reflection = new ReflectionClass($exporter);
        $data_property = $exporter_reflection->getProperty('data');
        $data_property->setAccessible(true);
        // Then data is null
        $this->assertNull($data_property->getValue($exporter));

        // Given we initialise the data
        $this->callInternalMethod($exporter, 'initialise_data', []);
        // And we check the data property
        $data_property = $exporter_reflection->getProperty('data');
        $data = $data_property->getValue($exporter);
        // Then the data is initialised
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
    }
}
