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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

use core_mfa\entity\instance as instance_entity;
use core_mfa\model\instance as instance_model;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core_mfa\model\instance
 * @group core_mfa
 */
class core_mfa_model_instance_test extends testcase {
    /**
     * @return void
     */
    public function test_create_instance(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $model = instance_model::create($user->id, 'button', 'Coffee Table', ['secret' => 'abcdef']);

        $entity = new instance_entity($model->id);

        $this->assertSame('button', $entity->type);
        $this->assertSame('Coffee Table', $entity->label);
        $this->assertSame($user->id, $entity->user_id);
        $this->assertEquals(['secret' => 'abcdef'], json_decode($entity->config, true));
    }

    /**
     * @return void
     */
    public function test_format_for_listing(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $model = instance_model::create($user->id, 'button', 'Samsung Refrigerator', ['secret' => 'hello']);
        $formatted = $model->format_for_listing();

        $this->assertSame($model->id, $formatted['id']);
        $this->assertSame('Samsung Refrigerator', $formatted['label']);
        $this->assertSame('button', $formatted['factor']);
        $this->assertSame('Button', $formatted['factor_name']);
    }

    /**
     * @return void
     */
    public function test_delete_instance(): void {
        global $DB;

        require_once __DIR__ . '/fixtures/mock_mfa_fixture.php';

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);

        $model = instance_model::create($user->id, 'unittest', 'Label', []);
        $factor_id = $model->id;

        $sink = $this->redirectEvents();
        $model->delete();
        $events = $sink->get_events();
        $sink->close();
        $event = array_pop($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\mfa_instance_deleted', $event);
        $this->assertEquals($factor_id, $event->objectid);
        $this->assertEquals($user->id, $event->userid);

        $exists = $DB->record_exists(core_mfa\entity\instance::TABLE, ['id' => $model->id]);
        $this->assertFalse($exists);
    }
}
