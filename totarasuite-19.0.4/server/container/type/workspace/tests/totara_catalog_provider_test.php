<?php
/*
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */

use container_workspace\workspace;
use container_workspace\testing\generator as workspace_generator;
use container_workspace\totara_catalog\workspace as workspace_provider;
use core\task\manager as task_manager;
use core_phpunit\testcase;
use totara_catalog\local\config;
use totara_catalog\provider;
use totara_catalog\dataformatter\formatter;

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_catalog
 */
class container_workspace_totara_catalog_provider_test extends testcase {

    private ?workspace_provider $provider = null;

    private ?workspace $workspace = null;

    protected function setUp(): void {
        parent::setup();
        $this->setAdminUser();
        $this->provider = new workspace_provider();
        $this->workspace = workspace_generator::instance()->create_workspace();
    }

    protected function tearDown(): void {
        $this->provider = null;
        $this->workspace = null;
        parent::tearDown();
    }

    public function test_get_name() {
        self::assertSame(get_string('space', 'container_workspace'), $this->provider->get_name());
    }

    public function test_get_object_type() {
        self::assertSame('workspace', $this->provider->get_object_type());
    }

    public function test_get_manage_link() {
        self::assertNull($this->provider->get_manage_link($this->workspace->id));
    }

    public function test_get_details_link() {
        $link = $this->provider->get_details_link($this->workspace->id);
        self::assertArrayHasKey('description', (array)$link);
        self::assertArrayHasKey('button', (array)$link);

        self::assertStringContainsString('&from=catalog', $link->button->url);
    }

    public function test_get_object_table() {
        self::assertSame('{course}', $this->provider->get_object_table());
    }

    public function test_get_objectid_field() {
        self::assertSame('id', $this->provider->get_objectid_field());
    }

    public function test_get_data_holder_config() {

        self::assertNotEmpty($this->provider->get_data_holder_config('sort'));
        self::assertNotEmpty($this->provider->get_data_holder_config('fts'));
        self::assertNotEmpty($this->provider->get_data_holder_config('image'));

        $ftsconfig = $this->provider->get_data_holder_config('fts');
        self::assertArrayHasKey('high', $ftsconfig);
        self::assertArrayHasKey('medium', $ftsconfig);
        self::assertArrayHasKey('low', $ftsconfig);
    }

    public function test_get_config() {
        $config = config::instance();
        $config->update(
            [
                'rich_text'               => ['workspace' => 'test_placeholder'],
                'details_additional_text' => ['workspace' => []],
            ]
        );
        self::assertSame('test_placeholder', $this->provider->get_config('rich_text'));
        self::assertEmpty($this->provider->get_config('details_additional_text'));
    }

    public function test_can_see() {
        $result = $this->provider->can_see([(object)['objectid' => $this->workspace->id]]);
        self::assertTrue($result[$this->workspace->id]);
    }

    public function test_get_all_objects_sql() {
        $result = $this->provider->get_all_objects_sql();
        self::assertNotEmpty($result[0]);
        self::assertArrayHasKey('level', $result[1]);
        self::assertEquals(CONTEXT_COURSE, $result[1]['level']);
    }

    public function test_is_plugin_enabled() {
        self::assertTrue($this->provider->is_plugin_enabled());
    }

    public function test_get_buttons() {
        set_config('enablecourserequests', 1);
        self::assertIsArray($this->provider->get_buttons());
    }

    public function test_change_status() {
        global $DB;

        $DB->delete_records('task_adhoc');

        // check inactive status
        workspace_provider::change_status(provider::PROVIDER_STATUS_INACTIVE);
        $count = $DB->count_records('catalog', ['objecttype' => workspace_provider::get_object_type()]);
        self::assertEmpty($count);

        // check active status
        workspace_provider::change_status(provider::PROVIDER_STATUS_ACTIVE);
        self::assertEquals(1, $DB->count_records('task_adhoc'));
        $task = task_manager::get_next_adhoc_task(time());
        $task->execute();
        task_manager::adhoc_task_complete($task);

        $count = $DB->count_records('catalog', ['objecttype' => workspace_provider::get_object_type()]);
        self::assertSame(1, $count);
    }

    public function test_get_filters() {
        foreach ($this->provider->get_filters() as $filter) {
            self::assertInstanceOf('totara_catalog\\filter', $filter);
        }
    }

    public function test_get_features() {
        foreach ($this->provider->get_features() as $features) {
            self::assertInstanceOf('totara_catalog\\feature', $features);
        }
    }

    public function test_get_dataholders() {
        // check fts data holders
        foreach ($this->provider->get_dataholders(formatter::TYPE_FTS) as $dataholder) {
            self::assertInstanceOf('totara_catalog\\dataholder', $dataholder);
        }

        // check title data holders
        foreach ($this->provider->get_dataholders(formatter::TYPE_PLACEHOLDER_TITLE) as $dataholder) {
            self::assertInstanceOf('totara_catalog\\dataholder', $dataholder);
        }
    }

}
