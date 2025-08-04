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
use tool_diagnostic\config_json_provider;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_config_json_provider_test extends testcase {

    /**
     * @var false|string
     */
    private $custom_config_filename;

    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->custom_config_filename = $CFG->tempdir.'/phpunit_diagnostic_config.json';
        if (file_exists($this->custom_config_filename)) {
            unlink($this->custom_config_filename);
        }
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        if (file_exists($this->custom_config_filename)) {
            unlink($this->custom_config_filename);
        }
        parent::tearDown();
    }

    public function test_default_config_file() {
        $provider = new config_json_provider();
        $provider->set_custom_config_filename($this->custom_config_filename);
        $config = $provider->get_config();

        $expected_json = json_encode($config, JSON_PRETTY_PRINT);
        $this->assertJsonStringEqualsJsonFile($this->get_default_config_filename(), $expected_json);
    }
    
    public function test_custom_config_file() {
        $custom_config_file_content =
<<<'EOF'
{
  "summary": {
    "enabled": true
  }
}
EOF;
        $this->create_custom_config_file($custom_config_file_content);

        $provider = new config_json_provider();
        $provider->set_custom_config_filename($this->custom_config_filename);
        $config = $provider->get_config();
        
        $this->assertEqualsCanonicalizing(
            ['summary' => ['enabled' => true]],
            $config
        );
    }

    public function test_invalid_custom_config_file() {
        $custom_config_file_content =
            <<<'EOF'
{
  "summary": {
}
EOF;
        $this->create_custom_config_file($custom_config_file_content);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('JSON could not be decoded. Please check the config file: '.$this->custom_config_filename);

        $provider = new config_json_provider();
        $provider->set_custom_config_filename($this->custom_config_filename);
        $provider->get_config();

    }

    /**
     * Returns the config.json.dist filename path
     *
     * @return string
     */
    private function get_default_config_filename(): string {
        global $CFG;

        return $CFG->dirroot.'/admin/tool/diagnostic/config/config.json.dist';
    }

    /**
     * Creates a custom config file with the contents given.
     * Any existing config file is backed up and restored after
     * each test.
     *
     * @param string $content
     * @return void
     */
    private function create_custom_config_file(string $content): void {
        file_put_contents(
            $this->custom_config_filename,
            $content
        );
    }

}
