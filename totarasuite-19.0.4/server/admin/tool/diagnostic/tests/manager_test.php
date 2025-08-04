<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\config_provider;
use tool_diagnostic\manager;
use tool_diagnostic\provider\provider;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_manager_test extends testcase {

    private function get_example_config(): array {
        return [
            "performance_database" => [
                "enabled" => false,
            ],
            "platform_database" => [
                "enabled" => true,
                'dbconfig_whitelist' => []
            ],
            "does_not_exist" => [
                "enabled" => true,
            ],
            "summary" => [
                "enabled" => true,
            ],
            "platform_phpini" => [
                "enabled" => true,
                "whitelist" => [
                    "memory_limit",
                    "opcache.enable",
                ]
            ]
        ];
    }

    public function test_get_enabled_providers(): void {
        $config_stub = $this->createStub(config_provider::class);

        $config_stub->method('get_config')
            ->willReturn($this->get_example_config());

        $manager = new manager($config_stub);

        $this->assertEqualsCanonicalizing(
            ['platform_database', 'summary', 'platform_phpini'],
            array_map(function (provider $provider) {
                return $provider::get_id();
            }, $manager->get_providers())
        );
    }

    public function test_get_all_providers(): void {
        $config_stub = $this->createStub(config_provider::class);

        $config_stub->method('get_config')
            ->willReturn($this->get_example_config());

        $manager = new manager($config_stub, true);

        $this->assertEqualsCanonicalizing(
            ['platform_database', 'summary', 'performance_database', 'platform_phpini'],
            array_map(function (provider $provider) {
                return $provider::get_id();
            }, $manager->get_providers())
        );
    }

    public function test_get_all_providers_with_excluded_ones(): void {
        $config_stub = $this->createStub(config_provider::class);

        $config_stub->method('get_config')
            ->willReturn($this->get_example_config());

        $exclude_providers = [
            'platform_database',
            'summary'
        ];

        $manager = new manager($config_stub, true, $exclude_providers);

        $this->assertEqualsCanonicalizing(
            ['performance_database', 'platform_phpini'],
            array_map(function (provider $provider) {
                return $provider::get_id();
            }, $manager->get_providers())
        );
    }

    public function test_run_diagnostic(): void {
        $config_stub = $this->createStub(config_provider::class);
        $config_stub->method('get_config')
            ->willReturn($this->get_example_config());

        $manager = new manager($config_stub);

        $result = $manager->run_diagnostics();

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('download_url', $result);
        $this->assertArrayHasKey('download_filesize', $result);

        $pattern = "/https\:\/\/www\.example\.com\/moodle\/pluginfile\.php\/" . SYSCONTEXTID . "\/tool_diagnostic\/export\/[0-9]+\/diagnostic\.zip/";
        $this->assertMatchesRegularExpression($pattern, $result['download_url']);

        $this->assertGreaterThan(0, $result['download_filesize']);

        $file_id_found = preg_match('/\/tool_diagnostic\/export\/([\d]+)\/diagnostic\.zip/', $result['download_url'], $matches);
        $this->assertNotFalse($file_id_found);
        $file_id = $matches[1];

        $file_record = manager::get_result_file_record($file_id);
        $this->assertNotEmpty($file_record);

        $file_record = manager::get_result_file_record(time() + 1);
        $this->assertEmpty($file_record);
    }

    public function test_provider_with_invalid_whitelist(): void {
        $config_stub = $this->createStub(config_provider::class);

        $config = [
            "totara_config" => [
                "enabled" => false,
                "whitelist" => [
                    "wwwroot",
                    "\<somethinginvalid\>"
                ]
            ],
            "summary" => [
                "enabled" => true,
            ],
        ];

        $config_stub->method('get_config')
            ->willReturn($config);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid whitelist entry detected');

        new manager($config_stub);
    }
}
