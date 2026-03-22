<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

defined('MOODLE_INTERNAL') || die();

use core\orm\query\exceptions\record_not_found_exception;
use core_phpunit\testcase;
use engage_course\totara_engage\resource\course;
use totara_webapi\phpunit\webapi_phpunit_helper;

class engage_course_webapi_share_with_recipient_by_ids_test extends testcase {
    use webapi_phpunit_helper;

    public function test_mutation_share_with_recipient_by_ids() {
        $user = $this->setup_user();
        $workspace = $this->create_workspace('test workspace', $user->id);
        $course = $this->create_course(['userid' => $user->id]);

        $result = $this->execute_mutation(['recipient' => ['instanceid' =>$workspace->get_id(),
              'component' => 'container_workspace', 'area' => 'library'], 'ids' => [$course->get_instanceid()]]);
        $this->assertIsBool($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($result);
    }

    public function test_share_with_recipient_by_ids_with_invalid_id() {
        $user = $this->setup_user();
        $workspace = $this->create_workspace('test workspace', $user->id);

        $this->expectException(record_not_found_exception::class);
        $this->expectExceptionMessage('Can not find data record in database');
        $this->execute_mutation(['recipient' => ['instanceid' =>$workspace->get_id(),
            'component' => 'container_workspace', 'area' => 'library'], 'ids' => [42]]);
    }

    private function setup_user() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        return $user;
    }

    private function create_workspace($name, $userid, $summary = null, $private = false, $hidden = false): \container_workspace\workspace {
        /** @var \container_workspace\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('container_workspace');
        return $generator->create_workspace($name, $summary ?? "{$name} summary", FORMAT_PLAIN, $userid, $private, $hidden);
    }

    private function execute_mutation(array $args) {
        return $this->resolve_graphql_mutation('engage_course_share_with_recipient_by_ids', $args);
    }

    private function create_course($params = []): course {
        /** @var \engage_course\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('engage_course');
        return $generator->create_course_resource($params);
    }
}
