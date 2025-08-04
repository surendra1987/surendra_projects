<?php
/**
 * This file is part of Totara Core
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package totara_playlist
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use engage_course\totara_engage\share\course_provider;
use totara_playlist\entity\playlist_resource;

class totara_playlist_resource_test extends testcase {
    /**
     * Assert that a resource can only be added to a playlist once.
     *
     * @return void
     */
    public function test_add_resource_counts(): void {
        global $DB, $CFG;

        // Create a playlist and a resource
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $this->setUser($user);

        /** @var \totara_playlist\testing\generator $playlistgen */
        $playlistgen = $gen->get_plugin_generator('totara_playlist');
        $playlist = $playlistgen->create_playlist();

        $course = $gen->create_course();
        $provider = new course_provider();
        $resource_item = $provider->get_item_instance_from_instance_id($course->id);

        $count = $DB->count_records(playlist_resource::TABLE, ['playlistid' => $playlist->get_id(), 'resourceid' => $resource_item->get_id()]);
        $this->assertEquals(0, $count);

        $playlist->add_resource($resource_item);

        $count = $DB->count_records(playlist_resource::TABLE, ['playlistid' => $playlist->get_id(), 'resourceid' => $resource_item->get_id()]);
        $this->assertEquals(1, $count);

        $playlist->add_resource($resource_item);
        $playlist->add_resource($resource_item);

        $count = $DB->count_records(playlist_resource::TABLE, ['playlistid' => $playlist->get_id(), 'resourceid' => $resource_item->get_id()]);
        $this->assertEquals(1, $count);
    }
}