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

use core_phpunit\testcase;
use engage_course\totara_engage\resource\course;
use totara_playlist\playlist;
use totara_webapi\phpunit\webapi_phpunit_helper;

class engage_course_webapi_ids_added_to_playlist_test extends testcase {
    use webapi_phpunit_helper;

    public function test_query_ids_added_to_playlist() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);
        $course = $this->create_course(['userid' => $user->id]);
        $course2 = $this->create_course(['userid' => $user->id]);
        $playlist->add_resource($course);
        $playlist->add_resource($course2);

        $result = $this->execute_query(['playlist_id' => $playlist->get_id()]);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);

        $ids = array_values($result);

        $this->assertContainsEquals($course->get_instanceid(), $ids);
        $this->assertContainsEquals($course2->get_instanceid(), $ids);
    }

    private function setup_user() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        return $user;
    }

    private function create_playlist($parameters = []): playlist {
        /** @var \totara_playlist\testing\generator $playlistgen */
        $playlistgen = $this->getDataGenerator()->get_plugin_generator('totara_playlist');
        return $playlistgen->create_playlist($parameters);
    }

    private function execute_query(array $args) {
        return $this->resolve_graphql_query('engage_course_ids_added_to_playlist', $args);
    }

    private function create_course($params = []): course {
        /** @var \engage_course\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('engage_course');
        return $generator->create_course_resource($params);
    }
}
